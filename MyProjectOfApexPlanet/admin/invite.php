<?php
require_once '../includes/auth_helper.php';
// 🔒 SECURITY CHECK: Protect path access from non-admin accounts
enforce_permission(['admin']);

require '../db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Server-Side Input Field Form Validation Loops
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Validation Error: All input fields must be populated!";
    } elseif ($password !== $confirm_password) {
        $error = "Mismatch Error: Passwords do not match!";
    } elseif (strlen($password) < 8) { 
        $error = "Security Policy: Administrative passwords must be at least 8 characters long.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Conflict: This username or email address is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Explicitly force registration role attribute value to 'admin'
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, 'admin', 'active')");
            $stmt->execute([$username, $email, $hashedPassword]);
            
            $success = "Success! New admin account for '$username' generated perfectly.";
        }
    }
}

require '../includes/header.php';
require '../includes/navbar.php';
?>

<!-- 🌟 Flexbox Centering Wrapper -->
<div style="display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 140px); padding: 40px 16px; box-sizing: border-box;">
    
    <!-- 🚀 FIX: Removed all hardcoded hex background/text colors to allow full var() variable theme inheritance -->
    <div class="card" style="width: 100%; max-width: 600px; padding: 35px; border: 2px solid #d97706; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); background: var(--surface); margin: 0;">
        <h2 style="color: var(--text); margin-bottom: 5px; font-weight: 800;">🔐 Provision Admin Authority</h2>
        <p style="color: var(--text2); font-size: 13px; margin-bottom: 25px;">Constructing an isolated node token profile with global site modification clearance capabilities.</p>
        
        <?php if($error): ?>
            <div class="alert alert-error" style="background: #fee2e2; color: #991b1b; border-left: 4px solid #dc2626; padding: 12px; margin-bottom: 20px; border-radius: 8px; font-weight: 600;">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="alert alert-success" style="background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; padding: 12px; margin-bottom: 20px; border-radius: 8px; font-weight: 600;">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" style="margin-top: 10px;">
            <div class="form-group">
                <label style="color: var(--text); font-weight: 700;">Admin Identifier Key (Username)</label>
                <input type="text" name="username" placeholder="Create administrative username" required style="border: 1.5px solid var(--border); background: var(--bg); color: var(--text); padding: 10px; border-radius: 8px; width: 100%;">
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label style="color: var(--text); font-weight: 700;">Corporate Communications Node (Email)</label>
                <input type="email" name="email" placeholder="username@corporate-domain.com" required style="border: 1.5px solid var(--border); background: var(--bg); color: var(--text); padding: 10px; border-radius: 8px; width: 100%;">
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 15px; margin-top: 15px; margin-bottom: 15px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="color: var(--text); font-weight: 700;">Secure Pass Code</label>
                    <input type="password" name="password" placeholder="Min 8 indicators" required style="border: 1.5px solid var(--border); background: var(--bg); color: var(--text); padding: 10px; border-radius: 8px; width: 100%;">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="color: var(--text); font-weight: 700;">Confirm Pass Code</label>
                    <input type="password" name="confirm_password" placeholder="Repeat phrase keys" required style="border: 1.5px solid var(--border); background: var(--bg); color: var(--text); padding: 10px; border-radius: 8px; width: 100%;">
                </div>
            </div>
            
            <div style="display: flex; gap: 15px; margin-top: 30px;">
                <button type="submit" class="btn" style="flex: 1; background: linear-gradient(135deg, #1e293b, #0f172a) !important; color: white;">Generate Authority Token</button>
                <a href="dashboard.php" class="btn btn-outline" style="text-decoration: none; text-align: center; line-height: 20px; color: var(--text);">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require '../includes/footer.php'; ?>