<?php
/**
 * User Dashboard
 * Clinic Reservation System
 */
require '../config/db.php';

// Redirect if not logged in
if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$first_name = $_SESSION['first_name'] ?? 'User';

// Get user's appointments
if ($role === 'admin') {
    // Admin sees all appointments
    $appointments_query = "
        SELECT a.*, s.name as service_name, s.price, u.first_name, u.last_name 
        FROM appointments a 
        LEFT JOIN services s ON a.service_id = s.id 
        LEFT JOIN users u ON a.user_id = u.id 
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ";
    $appointments_result = $conn->query($appointments_query);
} else {
    // Regular users see only their appointments
    $appointments_stmt = $conn->prepare("
        SELECT a.*, s.name as service_name, s.price, s.duration_minutes 
        FROM appointments a 
        LEFT JOIN services s ON a.service_id = s.id 
        WHERE a.user_id = ? 
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
    ");
    $appointments_stmt->bind_param("i", $user_id);
    $appointments_stmt->execute();
    $appointments_result = $appointments_stmt->get_result();
}

// Get upcoming appointments count
$upcoming_count = 0;
if ($role !== 'admin') {
    $count_stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointments 
        WHERE user_id = ? 
        AND appointment_date >= CURDATE() 
        AND status NOT IN ('cancelled', 'completed')
    ");
    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();
    $upcoming_count = $count_stmt->get_result()->fetch_assoc()['count'];
}

// Get recent activity
$recent_activity = [];
if ($role === 'admin') {
    $activity_result = $conn->query("
        SELECT 'appointment' as type, a.created_at, a.full_name, s.name as service_name 
        FROM appointments a 
        LEFT JOIN services s ON a.service_id = s.id 
        ORDER BY a.created_at DESC 
        LIMIT 5
    ");
    $recent_activity = $activity_result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Clinic Reservation System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>🏥 Clinic Reservation System</h1>
    <h2    <p>Welcome back, <?php echo htmlspecialchars($first_name); ?>!</p> </h>
    </div>

    <div class="dashboard-container">
        <?php echo display_message(); ?>

        <?php if ($role === 'admin'): ?>
            <!-- Admin Dashboard -->
            <div class="grid grid-3">
                <!-- Statistics Cards -->
                <div class="card">
                    <h3>📊 Quick Stats</h3>
                    <?php
                    $stats = $conn->query("
                        SELECT 
                            (SELECT COUNT(*) FROM appointments WHERE status = 'pending') as pending,
                            (SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()) as today,
                            (SELECT COUNT(*) FROM users WHERE role = 'patient') as patients
                    ")->fetch_assoc();
                    ?>
                    <p><strong>Pending Appointments:</strong> <?php echo $stats['pending']; ?></p>
                    <p><strong>Today's Appointments:</strong> <?php echo $stats['today']; ?></p>
                    <p><strong>Total Patients:</strong> <?php echo $stats['patients']; ?></p>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <h3>⚡ Quick Actions</h3>
                    <div class="nav-links">
                        <a href="index.php" class="btn btn-primary">📅 New Appointment</a>
                        <a href="admin_users.php" class="btn btn-secondary">👥 Manage Users</a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <h3>🕒 Recent Activity</h3>
                    <?php if (!empty($recent_activity)): ?>
                        <?php foreach ($recent_activity as $activity): ?>
                            <p style="font-size: var(--font-size-sm); margin-bottom: 0.5rem;">
                                <strong><?php echo htmlspecialchars($activity['full_name']); ?></strong> 
                                booked <?php echo htmlspecialchars($activity['service_name']); ?>
                                <br>
                                <small style="color: var(--gray-500);">
                                    <?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?>
                                </small>
                            </p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No recent activity</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- All Appointments Table -->
            <div class="card">
                <h3>📋 All Appointments</h3>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Service</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Contact</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($appointments_result->num_rows > 0): ?>
                                <?php while ($appointment = $appointments_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($appointment['full_name']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($appointment['service_name'] ?? 'N/A'); ?>
                                            <?php if ($appointment['price']): ?>
                                                <br><small>$<?php echo number_format($appointment['price'], 2); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?><br>
                                            <small><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                                <?php echo ucfirst($appointment['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($appointment['email']); ?><br>
                                            <small><?php echo htmlspecialchars($appointment['phone']); ?></small>
                                        </td>
                                        <td>
                                            <a href="view_appointment.php?id=<?php echo $appointment['id']; ?>" 
                                               class="btn btn-primary" style="font-size: var(--font-size-xs); padding: 0.25rem 0.5rem;">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No appointments found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <!-- Patient Dashboard -->
            <div class="grid grid-2">
                <!-- Welcome Card -->
                <div class="card">
                    <h3>👋 Welcome, <?php echo htmlspecialchars($first_name); ?>!</h3>
                    <p>Manage your appointments and health records from here.</p>
                    <div class="nav-links">
                        <a href="index.php" class="btn btn-primary">📅 Book New Appointment</a>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card">
                    <h3>📊 Your Statistics</h3>
                    <p><strong>Upcoming Appointments:</strong> <?php echo $upcoming_count; ?></p>
                    <p><strong>Total Appointments:</strong> <?php echo $appointments_result->num_rows; ?></p>
                    <p><strong>Member Since:</strong> 
                        <?php 
                        $member_since = $conn->prepare("SELECT created_at FROM users WHERE id = ?");
                        $member_since->bind_param("i", $user_id);
                        $member_since->execute();
                        $member_date = $member_since->get_result()->fetch_assoc()['created_at'];
                        echo date('M Y', strtotime($member_date));
                        ?>
                    </p>
                </div>
            </div>

            <!-- Appointments -->
            <div class="card">
                <h3>📋 Your Appointments</h3>
                
                <?php if ($appointments_result->num_rows > 0): ?>
                    <div class="grid grid-2">
                        <?php while ($appointment = $appointments_result->fetch_assoc()): ?>
                            <div class="appointment-card">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                    <h4 style="margin: 0; color: var(--primary-color);">
                                        <?php echo htmlspecialchars($appointment['service_name'] ?? 'General Consultation'); ?>
                                    </h4>
                                    <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </div>
                                
                                <p style="margin: 0.5rem 0;">
                                    <strong>📅 Date:</strong> 
                                    <?php echo date('l, F j, Y', strtotime($appointment['appointment_date'])); ?>
                                </p>
                                
                                <p style="margin: 0.5rem 0;">
                                    <strong>🕐 Time:</strong> 
                                    <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                    <?php if ($appointment['duration_minutes']): ?>
                                        (<?php echo $appointment['duration_minutes']; ?> minutes)
                                    <?php endif; ?>
                                </p>
                                
                                <?php if ($appointment['price']): ?>
                                    <p style="margin: 0.5rem 0;">
                                        <strong>💰 Cost:</strong> $<?php echo number_format($appointment['price'], 2); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($appointment['symptoms']): ?>
                                    <p style="margin: 0.5rem 0;">
                                        <strong>📝 Symptoms:</strong> 
                                        <?php echo htmlspecialchars(substr($appointment['symptoms'], 0, 100)); ?>
                                        <?php if (strlen($appointment['symptoms']) > 100) echo '...'; ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div style="margin-top: 1rem;">
                                    <small style="color: var(--gray-500);">
                                        Booked on <?php echo date('M j, Y', strtotime($appointment['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center" style="padding: 3rem;">
                        <h4>📅 No Appointments Yet</h4>
                        <p>You haven't booked any appointments yet. Get started by booking your first appointment!</p>
                        <a href="index.php" class="btn btn-primary">Book Your First Appointment</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Navigation -->
        <div class="nav-links" style="margin-top: 2rem;">
            <?php if ($role === 'admin'): ?>
                <a href="admin_dashboard.php" class="btn btn-secondary">🔧 Admin Panel</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
        </div>
    </div>

    <script>
        // Auto-refresh page every 5 minutes for admin
        <?php if ($role === 'admin'): ?>
        setTimeout(function() {
            location.reload();
        }, 300000); // 5 minutes
        <?php endif; ?>

        // Show success message animation
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>
