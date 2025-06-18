<?php
/**
 * Appointment Booking Page
 * Clinic Reservation System
 */
require '../config/db.php';

// Redirect if not logged in
if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get available services
$services_result = $conn->query("SELECT * FROM services WHERE available = 1 ORDER BY category, name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Clinic Reservation System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="logo">🏥</div>
        <h2>Book an Appointment</h2>
        
        <?php echo display_message(); ?>
        
        <form method="POST" action="submit_appointment.php" id="appointmentForm">
            <div class="form-group">
                <label class="form-label" for="full_name">Full Name *</label>
                <input type="text" 
                       id="full_name" 
                       name="full_name" 
                       class="form-input" 
                       value="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>" 
                       required>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address *</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-input" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" 
                       required>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone Number *</label>
                <input type="tel" 
                       id="phone" 
                       name="phone" 
                       class="form-input" 
                       placeholder="+1 (555) 123-4567" 
                       required>
            </div>

            <div class="form-group">
                <label class="form-label" for="service">Service *</label>
                <select id="service" name="service_id" class="form-select" required>
                    <option value="" disabled selected>Select a Service</option>
                    <?php 
                    $current_category = '';
                    while ($service = $services_result->fetch_assoc()): 
                        if ($current_category !== $service['category']):
                            if ($current_category !== '') echo '</optgroup>';
                            echo '<optgroup label="' . htmlspecialchars($service['category']) . '">';
                            $current_category = $service['category'];
                        endif;
                    ?>
                        <option value="<?php echo $service['id']; ?>" 
                                data-price="<?php echo $service['price']; ?>"
                                data-duration="<?php echo $service['duration_minutes']; ?>">
                            <?php echo htmlspecialchars($service['name']); ?> 
                            (<?php echo $service['duration_minutes']; ?> min - ₱<?php echo number_format($service['price'], 2); ?>)
                        </option>
                    <?php endwhile; ?>
                    <?php if ($current_category !== '') echo '</optgroup>'; ?>
                </select>
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="appointment_date">Preferred Date *</label>
                    <input type="date" 
                           id="appointment_date" 
                           name="appointment_date" 
                           class="form-input" 
                           min="<?php echo date('Y-m-d'); ?>" 
                           max="<?php echo date('Y-m-d', strtotime('+3 months')); ?>"
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="appointment_time">Preferred Time *</label>
                    <select id="appointment_time" name="appointment_time" class="form-select" required>
                        <option value="" disabled selected>Select Time</option>
                        <option value="09:00">09:00 AM</option>
                        <option value="09:30">09:30 AM</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="10:30">10:30 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="11:30">11:30 AM</option>
                        <option value="14:00">02:00 PM</option>
                        <option value="14:30">02:30 PM</option>
                        <option value="15:00">03:00 PM</option>
                        <option value="15:30">03:30 PM</option>
                        <option value="16:00">04:00 PM</option>
                        <option value="16:30">04:30 PM</option>
                        <option value="17:00">05:00 PM</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="symptoms">Symptoms / Reason for Visit</label>
                <textarea id="symptoms" 
                          name="symptoms" 
                          class="form-textarea" 
                          placeholder="Please describe your symptoms or reason for the appointment..."
                          rows="4"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="notes">Additional Notes</label>
                <textarea id="notes" 
                          name="notes" 
                          class="form-textarea" 
                          placeholder="Any additional information you'd like us to know..."
                          rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <span class="" id="submitSpinner"></span>
                Book Appointment
            </button>
        </form>

        <div class="nav-links">
            <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
        </div>
    </div>

    <script>
        // Form validation and enhancement
        document.getElementById('appointmentForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const spinner = document.getElementById('submitSpinner');
            
            // Show loading state
            submitBtn.classList.add('loading');
            spinner.classList.remove('hidden');
            submitBtn.textContent = ' Booking...';
        });

        // Auto-populate phone from user profile if available
        document.addEventListener('DOMContentLoaded', function() {
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('appointment_date').setAttribute('min', today);
            
            // Disable past times if today is selected
            document.getElementById('appointment_date').addEventListener('change', function() {
                const selectedDate = this.value;
                const today = new Date().toISOString().split('T')[0];
                const timeSelect = document.getElementById('appointment_time');
                
                if (selectedDate === today) {
                    const now = new Date();
                    const currentHour = now.getHours();
                    const currentMinute = now.getMinutes();
                    
                    Array.from(timeSelect.options).forEach(option => {
                        if (option.value) {
                            const [hour, minute] = option.value.split(':').map(Number);
                            const optionTime = hour * 60 + minute;
                            const currentTime = currentHour * 60 + currentMinute;
                            
                            option.disabled = optionTime <= currentTime + 60; // 1 hour buffer
                        }
                    });
                } else {
                    Array.from(timeSelect.options).forEach(option => {
                        option.disabled = false;
                    });
                }
            });
        });
    </script>
</body>
</html>
