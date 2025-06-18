<?php
/**
 * User Registration Page
 * Clinic Reservation System
 */
require '../config/db.php';

$error = '';
$success = '';

if (isset($_POST['register'])) {
    // Retrieve and sanitize input
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $birthday = $_POST['birthday'];
    $sex = $_POST['sex'];
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    $errors = [];

    if (empty($first_name)) $errors[] = "First name is required.";
    if (empty($last_name)) $errors[] = "Last name is required.";
    if (empty($birthday)) $errors[] = "Birthday is required.";
    if (empty($sex)) $errors[] = "Sex is required.";
    if (!validate_email($email)) $errors[] = "Valid email address is required.";
    if (empty($phone)) $errors[] = "Phone number is required.";
    if (empty($username)) $errors[] = "Username is required.";
    if (strlen($username) < 3) $errors[] = "Username must be at least 3 characters long.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters long.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

    // Check age (must be at least 13 years old)
    $birth_date = new DateTime($birthday);
    $today = new DateTime();
    $age = $today->diff($birth_date)->y;
    if ($age < 13) $errors[] = "You must be at least 13 years old to register.";

    if (empty($errors)) {
        try {
            // Check for existing username or email
            $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $check->bind_param("ss", $username, $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "Username or email already exists. Please choose different credentials.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user
                $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, birthday, sex, email, phone, username, password, role, status) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'patient', 'active')");
                $stmt->bind_param("ssssssss", $first_name, $last_name, $birthday, $sex, $email, $phone, $username, $hashed_password);

                if ($stmt->execute()) {
                    redirect_with_message("login.php", "Registration successful! Please log in with your credentials.", "success");
                } else {
                    throw new Exception("Registration failed: " . $conn->error);
                }
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $error = "An error occurred during registration. Please try again.";
        }
    } else {
        $error = implode(" ", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Clinic Reservation System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="logo">🏥</div>
        <h2>Create Your Account</h2>
        <p style="text-align: center; color: var(--gray-600); margin-bottom: 2rem;">
            Join us to manage your healthcare appointments
        </p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="registerForm">
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="first_name">First Name *</label>
                    <input type="text" 
                           id="first_name" 
                           name="first_name" 
                           class="form-input" 
                           placeholder=""
                           value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="last_name">Last Name *</label>
                    <input type="text" 
                           id="last_name" 
                           name="last_name" 
                           class="form-input" 
                           placeholder=""
                           value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
                           required>
                </div>
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="birthday">Date of Birth *</label>
                    <input type="date" 
                           id="birthday" 
                           name="birthday" 
                           class="form-input"
                           max="<?php echo date('Y-m-d', strtotime('-13 years')); ?>"
                           value="<?php echo isset($_POST['birthday']) ? htmlspecialchars($_POST['birthday']) : ''; ?>"
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="sex">Sex *</label>
                    <select id="sex" name="sex" class="form-select" required>
                        <option value="" disabled <?php echo !isset($_POST['sex']) ? 'selected' : ''; ?>>Select Sex</option>
                        <option value="Male" <?php echo (isset($_POST['sex']) && $_POST['sex'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo (isset($_POST['sex']) && $_POST['sex'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address *</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-input" 
                       placeholder=""
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone Number *</label>
                <input type="tel" 
                       id="phone" 
                       name="phone" 
                       class="form-input" 
                       placeholder=""
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label" for="username">Username *</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       class="form-input" 
                       placeholder=""
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       minlength="3"
                       required>
                <small style="color: var(--gray-500); font-size: var(--font-size-sm);">
                    At least 3 characters, letters and numbers only
                </small>
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="password">Password *</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-input" 
                           placeholder="••••••••"
                           minlength="6"
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password *</label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           class="form-input" 
                           placeholder="••••••••"
                           minlength="6"
                           required>
                </div>
            </div>

            <div id="password-requirements" style="font-size: var(--font-size-sm); color: var(--gray-500); margin-bottom: 1rem;">
                Password must be at least 6 characters long
            </div>

            <button type="submit" name="register" class="btn btn-primary">
                <span class="" id="registerSpinner"></span>
                Create Account
            </button>
        </form>

        <div class="text-center" style="margin-top: 1.5rem;">
            <p style="color: var(--gray-600);">
                Already have an account? 
                <a href="login.php" style="color: var(--primary-color); font-weight: 500;">
                    Sign in here
                </a>
            </p>
        </div>
    </div>

    <script>
        // Form validation and enhancement
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const spinner = document.getElementById('registerSpinner');
            
            // Show loading state
            submitBtn.classList.add('loading');
            spinner.classList.remove('hidden');
            submitBtn.innerHTML = '<span class="spinner"></span> Creating Account...';
        });

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = 'var(--danger-color)';
            } else {
                this.style.borderColor = 'var(--gray-200)';
            }
        });

        // Username validation
        document.getElementById('username').addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
        });

        // Auto-focus first name field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('first_name').focus();
        });
    </script>
</body>
</html>
