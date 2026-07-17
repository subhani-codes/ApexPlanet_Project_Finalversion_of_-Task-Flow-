<?php
require 'db.php';
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

$error = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if(empty($username) || empty($password)){
        $error = "All fields are required!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if($user && password_verify($password, $user['password'])){
            if (isset($user['status']) && $user['status'] === 'suspended') {
                $error = "🚫 Access Denied: This account has been suspended.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // 🚀 FIX: Smart conditional routing based on user permissions
                if ($_SESSION['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: todos/view.php");
                }
                exit();
            }
        } else {
            $error = "Invalid username or password!";
        }
    }
}

require 'includes/header.php';
require 'includes/navbar.php';
?>

<div class="auth-container">
    <div class="auth-box" style="background: var(--surface); border: 1.5px solid #5c7270; border-radius: 16px; padding: 30px;">
        <div class="auth-logo">TF</div>
        <h2>Welcome back</h2>
        <p class="auth-sub">Sign in to manage your tasks</p>
        
        <?php if($error): ?>
            <div class="alert alert-error" style="background: #fee2e2; color: #991b1b; border-left: 4px solid #dc2626; padding: 12px; margin-bottom: 15px; border-radius: 8px;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter your username" required/>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required/>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Sign in</button>
        </form>
        <div class="auth-divider" style="height: 1px; background: var(--border); margin: 20px 0;"></div>
        <p style="text-align:center;font-size:13px;color:var(--text2)">
            Don't have an account? 
            <a href="register.php" style="color:var(--primary);font-weight:600;text-decoration:none">Register here</a>
        </p>
    </div>
</div>

<?php require 'includes/footer.php'; ?>