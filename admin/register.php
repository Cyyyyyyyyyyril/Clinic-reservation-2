<?php
require '../config/db.php';

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $birthday = $_POST['birthday'];
    $sex = $_POST['sex'];
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

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

    $birth_date = new DateTime($birthday);
    $today = new DateTime();
    $age = $today->diff($birth_date)->y;
    if ($age < 13) $errors[] = "You must be at least 13 years old to register.";

    if (empty($errors)) {
        try {
            $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $check->bind_param("ss", $username, $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "Username or email already exists.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, birthday, sex, email, phone, username, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'patient', 'active')");
                $stmt->bind_param("ssssssss", $first_name, $last_name, $birthday, $sex, $email, $phone, $username, $hashed_password);
                if ($stmt->execute()) {
                    redirect_with_message("login.php", "Registration successful!", "success");
                } else {
                    throw new Exception("Registration failed: " . $conn->error);
                }
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $error = "An error occurred. Please try again.";
        }
    } else {
        $error = implode(" ", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register - Clinic Reservation</title>
  <style>
    :root {
        --primary-color: #0066cc;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-300: #dee2e6;
        --gray-500: #adb5bd;
        --gray-600: #6c757d;
        --danger-color: #dc3545;
        --success-color: #28a745;
        --font-size-sm: 0.875rem;
        --font-size-md: 1rem;
        --font-size-lg: 1.25rem;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #e0f7fa, #ffffff);
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
    }

    .container {
        background: #fff;
        padding: 2rem 3rem;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 700px;
    }

    .logo {
        font-size: 2.5rem;
        text-align: center;
        margin-bottom: 0.5rem;
    }

    h2 {
        text-align: center;
        margin-bottom: 0.5rem;
        color: var(--primary-color);
    }

    p {
        font-size: var(--font-size-md);
        color: var(--gray-600);
        text-align: center;
    }

    .grid {
        display: grid;
        gap: 1rem;
    }

    .grid-2 {
        grid-template-columns: 1fr 1fr;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 0.3rem;
    }

    .form-input,
    .form-select {
        padding: 0.7rem;
        border: 1px solid var(--gray-300);
        border-radius: 8px;
        font-size: var(--font-size-md);
    }

    .form-input:focus,
    .form-select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.15);
    }

    .alert {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        font-size: var(--font-size-sm);
    }

    .alert-error {
        background-color: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
    }

    .btn {
        background-color: var(--primary-color);
        color: #fff;
        border: none;
        padding: 0.8rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        font-size: var(--font-size-md);
        transition: background 0.3s;
    }

    .btn:hover {
        background-color: #0055aa;
    }

    .text-center {
        text-align: center;
    }

    @media (max-width: 768px) {
        .grid-2 {
            grid-template-columns: 1fr;
        }

        .container {
            padding: 1.5rem;
        }
    }
  </style>
</head>
<body>
<div class="container">
    <div class="logo">🏥</div>
    <h2>Create Your Account</h2>
    <p>Join us to manage your healthcare appointments</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="grid grid-2">
            <div class="form-group">
                <label class="form-label" for="first_name">First Name *</label>
                <input class="form-input" type="text" name="first_name" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="last_name">Last Name *</label>
                <input class="form-input" type="text" name="last_name" required>
            </div>
        </div>

        <div class="grid grid-2">
            <div class="form-group">
                <label class="form-label" for="birthday">Date of Birth *</label>
                <input class="form-input" type="date" name="birthday" max="<?php echo date('Y-m-d', strtotime('-13 years')); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="sex">Sex *</label>
                <select class="form-select" name="sex" required>
                    <option value="" disabled selected>Select Sex</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email Address *</label>
            <input class="form-input" type="email" name="email" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="phone">Phone Number *</label>
            <input class="form-input" type="tel" name="phone" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="username">Username *</label>
            <input class="form-input" type="text" name="username" required>
        </div>

        <div class="grid grid-2">
            <div class="form-group">
                <label class="form-label" for="password">Password *</label>
                <input class="form-input" type="password" name="password" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm Password *</label>
                <input class="form-input" type="password" name="confirm_password" required>
            </div>
        </div>

        <button type="submit" name="register" class="btn">Create Account</button>
    </form>

    <div class="text-center" style="margin-top: 1.5rem;">
        <p>Already have an account? <a href="login.php" style="color: var(--primary-color); font-weight: 500;">Sign in here</a></p>
    </div>
</div>
</body>
</html>
