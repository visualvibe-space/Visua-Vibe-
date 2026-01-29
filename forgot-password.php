<?php
// You can create a simple forgot password system with email verification
// For now, here's a basic structure
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Admin Panel</title>
    <style>
        /* Similar styling as login page */
        body {
            background: var(--dark);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 1rem;
        }
        
        .reset-container {
            max-width: 400px;
            width: 100%;
            background: var(--dark-secondary);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow-hover);
            border: var(--border);
        }
        
        .reset-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .reset-header h1 {
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        
        .reset-header p {
            color: var(--text-secondary);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.9rem;
            background: rgba(255,255,255,0.05);
            border: var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 1rem;
        }
        
        .btn {
            width: 100%;
            padding: 0.9rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--primary);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <h1>Reset Password</h1>
            <p>Enter your email to receive reset instructions</p>
        </div>
        
        <form method="POST" action="send-reset.php">
            <div class="form-group">
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            
            <button type="submit" class="btn">Send Reset Link</button>
        </form>
        
        <a href="login.php" class="back-link">← Back to Login</a>
    </div>
</body>
</html>