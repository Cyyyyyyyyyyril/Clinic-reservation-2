<?php
/**
 * User Login Page
 * Clinic Reservation System
 */
require '../config/db.php';

$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
$password = $_POST['password'];


    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        try {
            // Prevent SQL injection
            $stmt = $conn->prepare("SELECT id, username, password, role, first_name, status FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();

            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            // Verify password and check account status
            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'inactive' || $user['status'] === 'suspended') {
                    $error = "Your account has been " . $user['status'] . ". Please contact administration.";
                } else {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['first_name'] = $user['first_name'];

                    // Redirect based on role
                    switch ($user['role']) {
                        case 'admin':
                            header("Location: admin_dashboard.php");
                            break;
                        case 'doctor':
                            header("Location: doctor_dashboard.php");
                            break;
                        default:
                            header("Location: dashboard.php");
                    }
                    exit();
                }
            } else {
                $error = "Invalid username/email or password.";
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "An error occurred during login. Please try again.";
        }
    }
}

// Display registration success message
$registered_message = '';
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $registered_message = "Registration successful! Please log in with your credentials.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Clinic Reservation System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="logo">🏥</div>
        <p style="text-align: center; color: var(--gray-600); margin-bottom: 2rem;">
            Please sign in to your account
        </p>

        <?php if (!empty($registered_message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($registered_message); ?>
            </div>
        <?php endif; ?>

        <?php echo display_message(); ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label class="form-label" for="username">Username or Email</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       class="form-input" 
                       placeholder="Enter your username or email"
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-input" 
                       placeholder="Enter your password"
                       required>
            </div>

            <button type="submit" name="login" class="btn btn-primary">
                <span class="" id="loginSpinner"></span>
                Sign In
            </button>
        </form>

        <div class="text-center" style="margin-top: 1.5rem;">
            <p style="color: var(--gray-600);">
                Don't have an account? 
                <a href="register.php" style="color: var(--primary-color); font-weight: 500;">
                    Create one here
                </a>
            </p>
        </div>

    <script>
        // Form enhancement
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const spinner = document.getElementById('loginSpinner');
            
            // Show loading state
            submitBtn.classList.add('loading');
            spinner.classList.remove('hidden');
            submitBtn.innerHTML = '<span class="spinner"></span> Signing In...';
        });

        // Auto-focus username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>
