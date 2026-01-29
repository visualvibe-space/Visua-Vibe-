<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Update last login
                $stmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                // Set session
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_name'] = $user['full_name'];
                
                // Redirect to admin panel
                header('Location: admin_pannel.php');
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}

// If already logged in, redirect
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_pannel.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #1a1d28 0%, #252a38 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-box {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 28px;
            color: white;
        }
        
        .logo h1 {
            color: white;
            font-size: 24px;
            font-weight: 600;
        }
        
        .logo p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            margin-top: 5px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: white;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
        }
        
        .input-with-icon input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: white;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .input-with-icon input:focus {
            outline: none;
            border-color: #4361ee;
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }
        
        .input-with-icon input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        .error-message {
            background: rgba(244, 67, 54, 0.1);
            border-left: 4px solid #f44336;
            color: #ff8a80;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            display: <?php echo empty($error) ? 'none' : 'flex'; ?>;
        }
        
        .error-message i {
            font-size: 16px;
        }
        
        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            font-size: 16px;
            padding: 5px;
        }
        
        .password-toggle:hover {
            color: white;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 25px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
        }
        
        @media (max-width: 480px) {
            .login-box {
                padding: 30px 20px;
            }
            
            .logo-icon {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }
            
            .logo h1 {
                font-size: 20px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-box">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h1>Admin Login</h1>
            <p>Enter your credentials to continue</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error-message" id="errorMessage">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-with-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           placeholder="Enter your username"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           required
                           autocomplete="username"
                           autofocus>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Enter your password"
                           required
                           autocomplete="current-password">
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i>
                Sign In
            </button>
        </form>
        
        <div class="footer-text">
            &copy; <?php echo date('Y'); ?> Admin Panel
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Auto focus username field
        document.getElementById('username').focus();
        
        // Hide error message after 5 seconds
        const errorMessage = document.getElementById('errorMessage');
        if (errorMessage) {
            setTimeout(() => {
                errorMessage.style.opacity = '0';
                setTimeout(() => {
                    errorMessage.style.display = 'none';
                }, 300);
            }, 5000);
        }
        
        // Form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const btn = this.querySelector('.login-btn');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
            btn.disabled = true;
            
            setTimeout(() => {
                if (!this.checkValidity()) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            }, 1000);
        });
    </script>
</body>
</html>