<?php
require_once '../includes/auth_helper.php';
confirm_authenticated();

require '../db.php';

// Handle cancel link BEFORE any output
if (isset($_GET['cancel'])) {
    unset($_SESSION['pwd_verified'], $_SESSION['pwd_verified_at']);
    header("Location: change_password.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ============================================
   Load user record (we need stored email/phone)
============================================ */
$stmt = $pdo->prepare("SELECT email, phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: /myProjectOfApexPlanet/login.php");
    exit();
}

$error   = "";
$success = "";
$step    = 1; // 1 = verify identity, 2 = enter new password

/*
|--------------------------------------------------------------------------
| CSRF token
|--------------------------------------------------------------------------
*/
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_ok($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token ?? '');
}

/*
|--------------------------------------------------------------------------
| Handle POST
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_ok($_POST['csrf'] ?? '')) {
        $error = "Security check failed. Please reload and try again.";
    } else {

        $action = $_POST['action'] ?? '';

        if ($action === 'verify') {
            // Step 1 — verify email + phone against stored values
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');

            if ($email === '' || $phone === '') {
                $error = "Both email and phone are required.";
            } elseif (
                strcasecmp($email, $user['email'] ?? '') === 0 &&
                preg_replace('/\D+/', '', $phone) ===
                preg_replace('/\D+/', '', $user['phone'] ?? '')
            ) {
                $_SESSION['pwd_verified']  = true;
                $_SESSION['pwd_verified_at'] = time();
                $step = 2;
            } else {
                $error = "Email or phone does not match our records.";
            }

        } elseif ($action === 'reset') {

            // Step 2 — set the new password
            if (empty($_SESSION['pwd_verified']) ||
                (time() - ($_SESSION['pwd_verified_at'] ?? 0)) > 600) {
                // Verification expired or missing
                unset($_SESSION['pwd_verified'], $_SESSION['pwd_verified_at']);
                $error = "Verification expired. Please verify your identity again.";
                $step = 1;
            } else {
                $new     = $_POST['new_password'] ?? '';
                $confirm = $_POST['confirm_password'] ?? '';

                if ($new === '' || $confirm === '') {
                    $error = "Please fill in both password fields.";
                    $step = 2;
                } elseif (strlen($new) < 8) {
                    $error = "New password must be at least 8 characters.";
                    $step = 2;
                } elseif ($new !== $confirm) {
                    $error = "Passwords do not match.";
                    $step = 2;
                } else {
                    $hash = password_hash($new, PASSWORD_DEFAULT);
                    $upd  = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $upd->execute([$hash, $user_id]);

                    unset($_SESSION['pwd_verified'], $_SESSION['pwd_verified_at']);
                    $_SESSION['success'] = "Password updated successfully.";
                    header("Location: index.php");
                    exit();
                }
            }
        }
    }
}

// If step is still 1 by default and user has a verified flag, jump to step 2
if ($step === 1 && !empty($_SESSION['pwd_verified']) &&
    (time() - ($_SESSION['pwd_verified_at'] ?? 0)) <= 600) {
    $step = 2;
}

require '../includes/header.php';
require '../includes/navbar.php';
?>

<div class="container" style="max-width: 520px; margin: 40px auto; padding: 0 16px;">

<div class="card" style="padding: 30px 24px;">

<h2 style="margin-bottom: 8px;">🔒 Change Password</h2>
<p style="color: var(--text2); font-size: 14px; margin-bottom: 24px;">
    <?php if ($step === 1): ?>
        For your security, please confirm your email and phone number first.
    <?php else: ?>
        Identity verified. Enter your new password below.
    <?php endif; ?>
</p>

<?php if ($error): ?>
    <div class="alert alert-error"
         style="background:#fee2e2;color:#991b1b;border-left:4px solid #dc2626;
                padding:12px;border-radius:8px;margin-bottom:18px;font-weight:600;">
        ⚠️ <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"
         style="background:#d1fae5;color:#065f46;border-left:4px solid #10b981;
                padding:12px;border-radius:8px;margin-bottom:18px;font-weight:600;">
        ✅ <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<!-- ============================================================
     STEP 1 — verify email + phone
============================================================ -->
<?php if ($step === 1): ?>
<form method="POST" novalidate>
    <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="action" value="verify">

    <div class="form-group" style="margin-bottom: 16px;">
        <label style="font-weight:600;">📧 Registered Email</label>
        <input type="email" name="email" required
               placeholder="you@example.com"
               style="width:100%;padding:12px;border:1.5px solid var(--border);
                      background:var(--bg);color:var(--text);
                      border-radius:8px;font-size:15px;">
    </div>

    <div class="form-group" style="margin-bottom: 22px;">
        <label style="font-weight:600;">📱 Registered Phone</label>
        <input type="tel" name="phone" required
               placeholder="e.g. +91 9876543210"
               style="width:100%;padding:12px;border:1.5px solid var(--border);
                      background:var(--bg);color:var(--text);
                      border-radius:8px;font-size:15px;">
    </div>

    <button type="submit" class="btn btn-primary"
            style="width:100%;justify-content:center;padding:12px;">
        Verify Identity →
    </button>
</form>

<!-- ============================================================
     STEP 2 — set new password
============================================================ -->
<?php else: ?>
<form method="POST" novalidate>
    <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="action" value="reset">

    <div class="form-group" style="margin-bottom: 16px;">
        <label style="font-weight:600;">🔑 New Password</label>
        <input type="password" name="new_password" required minlength="8"
               placeholder="At least 8 characters"
               style="width:100%;padding:12px;border:1.5px solid var(--border);
                      background:var(--bg);color:var(--text);
                      border-radius:8px;font-size:15px;">
    </div>

    <div class="form-group" style="margin-bottom: 22px;">
        <label style="font-weight:600;">🔑 Confirm New Password</label>
        <input type="password" name="confirm_password" required minlength="8"
               placeholder="Repeat password"
               style="width:100%;padding:12px;border:1.5px solid var(--border);
                      background:var(--bg);color:var(--text);
                      border-radius:8px;font-size:15px;">
    </div>

    <button type="submit" class="btn btn-primary"
            style="width:100%;justify-content:center;padding:12px;">
        💾 Save New Password
    </button>

    <p style="text-align:center;margin-top:18px;font-size:13px;color:var(--text2);">
        <a href="change_password.php?cancel=1"
           style="color:var(--primary);text-decoration:none;font-weight:600;">
            ← Cancel and start over
        </a>
    </p>
</form>
<?php endif; ?>

<p style="text-align:center;margin-top:24px;font-size:13px;color:var(--text2);">
    <a href="/myProjectOfApexPlanet/profile/index.php"
       style="color:var(--primary);text-decoration:none;font-weight:600;">
        ← Back to Profile
    </a>
</p>

</div>

</div>

<?php require '../includes/footer.php'; ?>
