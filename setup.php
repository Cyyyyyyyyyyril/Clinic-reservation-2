<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Clinic Reservation System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .logo {
            text-align: center;
            font-size: 4rem;
            margin-bottom: 20px;
        }
        h1 {
            text-align: center;
            color: #2c5282;
            margin-bottom: 30px;
        }
        .setup-step {
            background: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            border-left: 5px solid #2c5282;
        }
        .step-number {
            background: #2c5282;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #2c5282;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: #3182ce;
            transform: translateY(-2px);
        }
        .btn-success {
            background: #38a169;
        }
        .btn-success:hover {
            background: #2f855a;
        }
        .demo-accounts {
            background: #e6fffa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border: 2px solid #38a169;
        }
        .demo-account {
            margin: 10px 0;
            padding: 10px;
            background: white;
            border-radius: 5px;
        }
        .status {
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            text-align: center;
        }
        .status-error {
            background: #fed7d7;
            color: #742a2a;
            border: 2px solid #fc8181;
        }
        .status-success {
            background: #c6f6d5;
            color: #22543d;
            border: 2px solid #68d391;
        }
        .code-block {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🏥</div>
        <h1>Clinic Reservation System Setup</h1>
        
        <?php
        // Check database connection
        $db_status = false;
        $error_message = '';
        
        try {
            $conn = new mysqli('localhost', 'root', '', 'clinic_reservation_system');
            if ($conn->connect_error) {
                throw new Exception('Connection failed: ' . $conn->connect_error);
            }
            
            // Check if tables exist
            $tables = ['users', 'appointments', 'services'];
            $tables_exist = true;
            
            foreach ($tables as $table) {
                $result = $conn->query("SHOW TABLES LIKE '$table'");
                if ($result->num_rows == 0) {
                    $tables_exist = false;
                    break;
                }
            }
            
            if ($tables_exist) {
                $db_status = true;
            } else {
                $error_message = 'Database tables not found. Please import the SQL file.';
            }
            
            $conn->close();
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        ?>

        <?php if ($db_status): ?>
            <div class="status status-success">
                <h2>✅ Setup Complete!</h2>
                <p>Your Clinic Reservation System is ready to use.</p>
                <a href="public/login.php" class="btn btn-success">🚀 Launch Application</a>
            </div>
        <?php else: ?>
            <div class="status status-error">
                <h2>⚠️ Setup Required</h2>
                <p><strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php endif; ?>

        <div class="setup-step">
            <h3><span class="step-number">1</span>Database Setup</h3>
            <p>Import the database schema to get started:</p>
            <ol>
                <li>Open phpMyAdmin: <a href="http://localhost/phpmyadmin" target="_blank">http://localhost/phpmyadmin</a></li>
                <li>Create new database: <code>clinic_reservation_system</code></li>
                <li>Import file: <code>database/clinic_reservation_system.sql</code></li>
            </ol>
            
            <div class="code-block">
                Database: clinic_reservation_system<br>
                Tables: users, appointments, services<br>
                Sample Data: ✓ Included
            </div>
        </div>

        <div class="setup-step">
            <h3><span class="step-number">2</span>File Permissions</h3>
            <p>Ensure your web server has proper permissions:</p>
            <ul>
                <li>Read access to all PHP files</li>
                <li>Write access to session directory</li>
                <li>Execute permissions for PHP scripts</li>
            </ul>
        </div>

        <div class="setup-step">
            <h3><span class="step-number">3</span>Configuration</h3>
            <p>Database settings are configured in: <code>config/db.php</code></p>
            <div class="code-block">
                Host: localhost<br>
                Username: root<br>
                Password: (empty)<br>
                Database: clinic_reservation_system
            </div>
        </div>

        <div class="demo-accounts">
            <h3>🔑 Demo Accounts</h3>
            <p>Use these accounts to test the system:</p>
            
            <div class="demo-account">
                <strong>Administrator</strong><br>
                Username: <code>admin</code> | Password: <code>admin123</code><br>
                <small>Full system access, manage users and appointments</small>
            </div>
            
            <div class="demo-account">
                <strong>Doctor</strong><br>
                Username: <code>dr.johnson</code> | Password: <code>admin123</code><br>
                <small>Medical professional access, patient management</small>
            </div>
            
            <div class="demo-account">
                <strong>Patient</strong><br>
                Username: <code>johndoe</code> | Password: <code>admin123</code><br>
                <small>Book appointments, view medical history</small>
            </div>
        </div>

        <div class="setup-step">
            <h3><span class="step-number">4</span>Next Steps</h3>
            <p>After setup is complete:</p>
            <ul>
                <li>Test login with demo accounts</li>
                <li>Book a sample appointment</li>
                <li>Explore admin features</li>
                <li>Customize services and settings</li>
                <li>Change default passwords</li>
            </ul>
        </div>

        <div style="text-align: center; margin-top: 40px;">
            <a href="public/" class="btn">📋 View Application</a>
            <a href="README.md" class="btn">📖 Read Documentation</a>
            <?php if ($db_status): ?>
                <a href="public/login.php" class="btn btn-success">🚀 Get Started</a>
            <?php endif; ?>
        </div>

        <hr style="margin: 40px 0;">
        
        <div style="text-align: center; color: #666;">
            <p><strong>Clinic Reservation System v2.0</strong></p>
            <p>Modern Medical Appointment Management</p>
            <p>Built with PHP, MySQL & Modern CSS</p>
        </div>
    </div>
</body>
</html>
