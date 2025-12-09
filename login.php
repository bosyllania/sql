<?php
// login.php

// Include database configuration
require_once 'config.php';

// Clear any existing session data when accessing login page
if (isset($_GET['logout']) || !isset($_POST['email'])) {
    session_unset();
    session_destroy();
    session_start();
}

// If already logged in, redirect based on role
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admindb.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['email']); // This can now be email OR name
    $password_input = $_POST['password'];
    
    // Validate input
    if (empty($login_input) || empty($password_input)) {
        $error = "Please enter both email/name and password.";
    } else {
        // Check if user exists by email OR name
        $sql = "SELECT * FROM users WHERE Email = ? OR Name = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$login_input, $login_input]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Check if password column exists, otherwise use Passcode
            $stored_password = isset($user['password']) ? $user['password'] : $user['passcode'];
            
            // Verify password (works with both hashed and plain text)
            $password_match = false;
            if (password_get_info($stored_password)['algo'] !== null) {
                // Password is hashed
                $password_match = password_verify($password_input, $stored_password);
            } else {
                // Password is plain text (not recommended for production)
                $password_match = ($password_input === $stored_password);
            }
            
            if ($password_match) {
                // Password is correct, create session
                $user_id = isset($user['userID']) ? $user['userID'] : $user['id'];
                $user_name = isset($user['Name']) ? $user['Name'] : (isset($user['fullname']) ? $user['fullname'] : 'User');
                
                $_SESSION['user_id'] = $user_id;
                $_SESSION['fullname'] = $user_name;
                $_SESSION['email'] = $user['Email']; // Use the email from database
                
                // Check if email contains "admin" or if there's a role column
                if (isset($user['role'])) {
                    $_SESSION['role'] = $user['role'];
                    if ($user['role'] === 'admin') {
                        header("Location: admindb.php");
                    } else {
                        header("Location: dashboard.php");
                    }
                } else {
                    // Check if email matches admin pattern
                    if (strpos(strtolower($user['Email']), 'admin') !== false || $user['Email'] === 'gymrat@gmail.com') {
                        $_SESSION['role'] = 'admin';
                        header("Location: admindb.php");
                    } else {
                        $_SESSION['role'] = 'user';
                        header("Location: dashboard.php");
                    }
                }
                exit();
            } else {
                $error = "Invalid email/name or password.";
            }
        } else {
            $error = "Invalid email/name or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GymFlex Fusion Hub</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .logo {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo svg {
            width: 50px;
            height: 50px;
            stroke: #5B4FFF;
        }

        h2 {
            text-align: center;
            color: #1a1a1a;
            margin-bottom: 8px;
            font-size: 24px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .error-message {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
            font-weight: 500;
        }

        .password-wrapper {
            position: relative;
        }

        input[type="email"],
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="email"]:focus,
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #5B4FFF;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
        }

        .toggle-password:hover {
            color: #5B4FFF;
        }

        .toggle-password svg {
            width: 20px;
            height: 20px;
        }

        .btn-signin {
            width: 100%;
            padding: 12px;
            background-color: #5B4FFF;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-signin:hover {
            background-color: #4A3FE8;
        }

        .signup-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .signup-link a {
            color: #5B4FFF;
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        .demo-credentials {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .demo-credentials h4 {
            color: #0369a1;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .demo-credentials p {
            color: #0c4a6e;
            margin: 4px 0;
        }

        .demo-credentials strong {
            color: #075985;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
        </div>
        <h2>Welcome Gymbro</h2>
        <p class="subtitle">Sign in to your account</p>
        
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email or Name</label>
                <input type="text" id="email" name="email" required autocomplete="off">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required autocomplete="off">
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        <svg id="eye-icon-password" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="btn-signin">Sign In</button>
            
            <div class="signup-link">
                Don't have an account? <a href="register.php">Sign up</a>
            </div>
        </form>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById('eye-icon-' + inputId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }
    </script>
</body>
</html>