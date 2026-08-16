<?php
require_once '../includes/auth_helper.php';
confirm_authenticated();

require '../db.php';

$user_id = $_SESSION['user_id'];

// Load logged-in user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Task statistics
$stmt = $pdo->prepare("SELECT COUNT(*) FROM todos WHERE user_id = ?");
$stmt->execute([$user_id]);
$totalTasks = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM todos WHERE user_id = ? AND status='completed'");
$stmt->execute([$user_id]);
$completedTasks = $stmt->fetchColumn();

$completionRate = ($totalTasks > 0)
    ? round(($completedTasks / $totalTasks) * 100)
    : 0;

require '../includes/header.php';
require '../includes/navbar.php';
?>
<div class="container">

<div class="profile-card">

<div class="profile-header">

<div class="profile-avatar">

<?php echo strtoupper(substr($user['username'],0,1)); ?>

</div>

<div class="profile-details">

<h2>

<?php echo htmlspecialchars($user['username']); ?>

</h2>

<p class="profile-role">

<?php echo ucfirst($user['role']); ?>

Member

</p>

<div class="online-status">

<span class="status-dot"></span>

Online

</div>

</div>

</div>

<div class="profile-grid">

<div class="profile-box">

<h4>📧 Email</h4>

<p>

<?php echo htmlspecialchars($user['email']); ?>

</p>

</div>

<div class="profile-box">

<h4>👑 Role</h4>

<p>

<?php echo ucfirst($user['role']); ?>

</p>

</div>

<div class="profile-box">

<h4>📅 Joined</h4>

<p>

<?php echo date("d M Y",strtotime($user['created_at'])); ?>

</p>

</div>

<div class="profile-box">

<h4>📈 Completion</h4>

<p>

<?php echo $completionRate; ?>%

</p>

</div>

</div>

<div class="profile-actions">

<a href="edit.php" class="btn btn-primary">

Edit Profile

</a>

<a href="change_password.php"

class="btn btn-outline">

Change Password

</a>

</div>

</div>

</div>
<?php require '../includes/footer.php'; ?>