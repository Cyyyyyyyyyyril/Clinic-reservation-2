<?php
/**
 * Admin Dashboard
 * Clinic Reservation System
 */
require '../config/db.php';

if (!is_logged_in() || !is_admin()) {
    redirect_with_message("login.php", "Access denied. Admin privileges required.");
}

// Get statistics
$stats = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM appointments) as total_appointments,
        (SELECT COUNT(*) FROM appointments WHERE status = 'pending') as pending_appointments,
        (SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()) as today_appointments,
        (SELECT COUNT(*) FROM users WHERE role = 'patient') as total_patients,
        (SELECT COUNT(*) FROM users WHERE role = 'doctor') as total_doctors,
        (SELECT COUNT(*) FROM services WHERE available = 1) as active_services
")->fetch_assoc();

// Get appointments
$all_appointments = $conn->query("
    SELECT a.*, s.name as service_name, s.price, u.first_name, u.last_name 
    FROM appointments a 
    LEFT JOIN services s ON a.service_id = s.id 
    LEFT JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC
");

// Get users
$all_users = $conn->query("SELECT id, first_name, last_name, email, role, created_at FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Clinic Reservation System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
        }

        .topbar {
            background-color: var(--primary);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .topbar-title {
            font-size: 1.8rem;
            font-weight: bold;
        }

        .topbar-subtitle {
            font-size: 1rem;
            color: #e0e0e0;
        }

        .dashboard-container {
            padding: 2rem;
        }

        .grid {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .grid-3 > .card {
            flex: 1 1 30%;
        }

        .card {
            background-color: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn {
            padding: 0.6rem 1rem;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--secondary);
            color: white;
        }

        .btn-secondary {
            background-color: #7f8c8d;
            color: white;
        }

        .btn-outline {
            border: 1px solid var(--primary);
            background-color: transparent;
            color: var(--primary);
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }

        .table th, .table td {
            padding: 0.75rem;
            border: 1px solid #ddd;
            text-align: left;
        }

        .status-badge {
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            color: white;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .status-pending { background-color: orange; }
        .status-approved { background-color: green; }
        .status-cancelled { background-color: red; }
        .status-completed { background-color: gray; }

        .btn-small {
            font-size: 0.85rem;
            padding: 0.4rem 0.8rem;
            margin-right: 0.4rem;
        }
    </style>
</head>
<body>
    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-title">Admin Dashboard</div>
        <div class="topbar-subtitle">Clinic Reservation System</div>
    </div>

    <div class="dashboard-container">
        <?php echo display_message(); ?>

        <!-- Statistics -->
        <div class="grid grid-3">
            <div class="card">
                <h3>Appointments</h3>
                <p style="font-size: 2rem; font-weight: bold; color: var(--primary); margin: 1rem 0;">
                    <?= $stats['total_appointments']; ?>
                </p>
                <p><strong>Pending:</strong> <?= $stats['pending_appointments']; ?></p>
                <p><strong>Today:</strong> <?= $stats['today_appointments']; ?></p>
            </div>

            <div class="card">
                <h3>Users</h3>
                <p style="font-size: 2rem; font-weight: bold; color: var(--accent-color); margin: 1rem 0;">
                    <?= $stats['total_patients'] + $stats['total_doctors']; ?>
                </p>
                <p><strong>Patients:</strong> <?= $stats['total_patients']; ?></p>
                <p><strong>Doctors:</strong> <?= $stats['total_doctors']; ?></p>
            </div>

            <div class="card">
                <h3>Services</h3>
                <p style="font-size: 2rem; font-weight: bold; color: var(--warning-color); margin: 1rem 0;">
                    <?= $stats['active_services']; ?>
                </p>
                <p>Active Services</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <h3>Quick Actions</h3>
            <div class="nav-links">
                <a href="index.php" class="btn btn-primary">Create Appointment</a>
                <a href="#appointments-section" class="btn btn-secondary">Manage Appointments</a>
                <a href="#users-section" class="btn btn-secondary">Manage Users</a>
                <a href="manage_services.php" class="btn btn-secondary">Manage Services</a>
                <a href="reports.php" class="btn btn-outline">Doctors</a>
            </div>
        </div>

        <!-- Appointments Section -->
        <div class="card" id="appointments-section" style="display: none;">
            <h3>All Appointments</h3>
            <?php if ($all_appointments->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Service</th>
                            <th>Price</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $all_appointments->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                <td><?= htmlspecialchars($row['service_name']) ?></td>
                                <td>₱<?= number_format($row['price'], 2) ?></td>
                                <td><?= $row['appointment_date'] ?></td>
                                <td><?= $row['appointment_time'] ?></td>
                                <td><span class="status-badge status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                                <td>
                                    <a href="edit_appointment.php?id=<?= $row['id'] ?>" class="btn btn-small btn-primary">Edit</a>
                                    <a href="delete_appointment.php?id=<?= $row['id'] ?>" class="btn btn-small btn-danger" onclick="return confirm('Delete this appointment?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No appointments found.</p>
            <?php endif; ?>
        </div>

        <!-- Users Section -->
        <div class="card" id="users-section" style="display: none;">
            <h3>All Users</h3>
            <?php if ($all_users->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $all_users->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= ucfirst($user['role']) ?></td>
                                <td><?= date("F j, Y", strtotime($user['created_at'])) ?></td>
                                <td>
                                    <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-small btn-primary">Edit</a>
                                    <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-small btn-danger" onclick="return confirm('Delete this user?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>
        </div>

        <!-- Navigation -->
        <div class="nav-links" style="margin-top: 2rem;">
            <a href="dashboard.php" class="btn btn-secondary">User Dashboard</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        document.querySelector('a[href="#appointments-section"]').addEventListener('click', function(e) {
            e.preventDefault();
            const section = document.getElementById('appointments-section');
            section.style.display = 'block';
            section.scrollIntoView({ behavior: 'smooth' });
        });

        document.querySelector('a[href="#users-section"]').addEventListener('click', function(e) {
            e.preventDefault();
            const section = document.getElementById('users-section');
            section.style.display = 'block';
            section.scrollIntoView({ behavior: 'smooth' });
        });

        setTimeout(() => location.reload(), 300000);

        function updateClock() {
            const now = new Date();
            document.title = `Admin Dashboard (${now.toLocaleString()}) - Clinic Reservation System`;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>
