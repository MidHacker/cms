<?php
require_once '../config/db.php';

if (isLoggedIn()) {
    header('Location: /cms/dashboard/index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT id, username, email, password_hash, full_name, role, avatar_url FROM users WHERE username = ? AND is_active = 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['avatar_url'] = $user['avatar_url'];
            
            // Log login activity
            $log_sql = "INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'login', 'User logged in')";
            $log_stmt = mysqli_prepare($conn, $log_sql);
            mysqli_stmt_bind_param($log_stmt, "i", $user['id']);
            mysqli_stmt_execute($log_stmt);
            
            header('Location: /cms/dashboard/index.php');
            exit();
        } else {
            $error = "Invalid credentials";
        }
    } else {
        $error = "User not found or inactive";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Courier Management System</title>
    <link rel="stylesheet" href="/cms/assets/css/style.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
        }
        
        .login-card {
            background: var(--sg-body-bg-color);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            animation: fadeIn 0.5s ease;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-header h1 {
            color: var(--sg-dark);
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .login-header p {
            color: var(--sg-secondary);
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--sg-dark);
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--sg-light);
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: var(--sg-body-bg-color);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--sg-primary);
            box-shadow: 0 0 0 3px rgba(var(--sg-primary-rgb), 0.1);
        }
        
        .login-btn {
            width: 100%;
            padding: 16px;
            background: var(--sg-dark);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .login-btn:hover {
            background: var(--sg-primary);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(var(--sg-primary-rgb), 0.2);
        }
        
        .error-message {
            background: rgba(255, 32, 78, 0.1);
            color: var(--sg-primary);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid var(--sg-primary);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--sg-light);
            color: var(--sg-secondary);
            font-size: 14px;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Courier Management</h1>
                <p>Sign in to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-input" placeholder="Enter your username" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="login-btn">Sign In</button>
            </form>
            
            <div class="login-footer">
                <p>Demo Credentials:</p>
                <p>Admin: admin / password</p>
                <p>Client: client1 / password</p>
                <p>Livreur: livreur1 / password</p>
            </div>
        </div>
    </div>
    
    <script>
        // Add focus effects
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>
