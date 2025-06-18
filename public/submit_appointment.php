<?php
/**
 * Submit Appointment Handler
 * Clinic Reservation System
 */
require '../config/db.php';

// Ensure user is logged in
if (!is_logged_in()) {
    redirect_with_message("login.php", "Please log in to book an appointment.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Collect and sanitize form inputs
        $userId = $_SESSION['user_id'];
        $fullName = sanitize_input($_POST['full_name']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $serviceId = (int)$_POST['service_id'];
        $appointmentDate = $_POST['appointment_date'];
        $appointmentTime = $_POST['appointment_time'];
        $symptoms = sanitize_input($_POST['symptoms'] ?? '');
        $notes = sanitize_input($_POST['notes'] ?? '');

        // Validation
        $errors = [];

        if (empty($fullName)) {
            $errors[] = "Full name is required.";
        }

        if (!validate_email($email)) {
            $errors[] = "Valid email address is required.";
        }

        if (empty($phone)) {
            $errors[] = "Phone number is required.";
        }

        if (empty($serviceId)) {
            $errors[] = "Please select a service.";
        }

        if (empty($appointmentDate)) {
            $errors[] = "Appointment date is required.";
        } else {
            // Check if date is not in the past
            $selectedDate = new DateTime($appointmentDate);
            $today = new DateTime();
            if ($selectedDate < $today) {
                $errors[] = "Appointment date cannot be in the past.";
            }
        }

        if (empty($appointmentTime)) {
            $errors[] = "Appointment time is required.";
        }

        // Check if service exists
        if ($serviceId) {
            $serviceCheck = $conn->prepare("SELECT name FROM services WHERE id = ? AND available = 1");
            $serviceCheck->bind_param("i", $serviceId);
            $serviceCheck->execute();
            if ($serviceCheck->get_result()->num_rows === 0) {
                $errors[] = "Selected service is not available.";
            }
        }

        // Check for time conflicts (same date and time slot)
        if ($appointmentDate && $appointmentTime) {
            $conflictCheck = $conn->prepare("
                SELECT id FROM appointments 
                WHERE appointment_date = ? 
                AND appointment_time = ? 
                AND status NOT IN ('cancelled', 'no_show')
            ");
            $conflictCheck->bind_param("ss", $appointmentDate, $appointmentTime);
            $conflictCheck->execute();
            if ($conflictCheck->get_result()->num_rows > 0) {
                $errors[] = "This time slot is already booked. Please select a different time.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header("Location: index.php");
            exit();
        }

        // Insert appointment
        $sql = "INSERT INTO appointments 
                (user_id, service_id, full_name, email, phone, appointment_date, appointment_time, symptoms, notes, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssssss", $userId, $serviceId, $fullName, $email, $phone, $appointmentDate, $appointmentTime, $symptoms, $notes);

        if ($stmt->execute()) {
            $appointmentId = $conn->insert_id;
            
            // Get service details for confirmation
            $serviceQuery = $conn->prepare("SELECT name, price FROM services WHERE id = ?");
            $serviceQuery->bind_param("i", $serviceId);
            $serviceQuery->execute();
            $service = $serviceQuery->get_result()->fetch_assoc();

            $successMessage = "Your appointment has been successfully booked! " .
                            "Reference ID: #" . str_pad($appointmentId, 6, '0', STR_PAD_LEFT) . ". " .
                            "Service: " . $service['name'] . " on " . 
                            date('F j, Y', strtotime($appointmentDate)) . " at " . 
                            date('g:i A', strtotime($appointmentTime)) . ".";

            redirect_with_message("dashboard.php", $successMessage, "success");
        } else {
            throw new Exception("Failed to book appointment. Please try again.");
        }

    } catch (Exception $e) {
        error_log("Appointment booking error: " . $e->getMessage());
        redirect_with_message("index.php", "An error occurred while booking your appointment. Please try again.");
    }
} else {
    // Redirect if accessed directly without POST
    header("Location: index.php");
    exit();
}
?>
