<?php
session_start();

require '../db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: /myProjectOfApexPlanet/login.php");
    exit();
}

$user_id=$_SESSION['user_id'];

/* Logged in user */

$stmt=$pdo->prepare("SELECT username FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user=$stmt->fetch(PDO::FETCH_ASSOC);

/* Yesterday */

require '../includes/date_helper.php';

$yesterday = getYesterday();

/*
---------------------------------------------------------
Get yesterday's task counts for this user.
Only rows that are NOT yet closed (day_closed = 0) count
toward the "carry-over" prompt.
---------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed
    FROM todos
    WHERE user_id = ?
      AND day_closed = 0
      AND DATE(created_at) = ?
");

$stmt->execute([$user_id, $yesterday]);

$result = $stmt->fetch(PDO::FETCH_ASSOC);

$total     = (int)($result['total']     ?? 0);
$completed = (int)($result['completed'] ?? 0);
$pending   = max(0, $total - $completed);

$dayName = date('D', strtotime($yesterday));

/*
---------------------------------------------------------
Edge case: if there are no rows at all for yesterday that
are still "open" (total = 0), redirect straight to the
dashboard. Nothing to carry over, nothing to display.
---------------------------------------------------------
*/

if ($total === 0) {
    header("Location: /myProjectOfApexPlanet/todos/view.php");
    exit();
}

require '../includes/header.php';
require '../includes/navbar.php';
?>

<div class="container">

<div class="day-start-card">

<div class="morning-icon">

☀️

</div>

<h2>

Good Morning,

<span>

<?php echo htmlspecialchars($user['username']); ?>

</span>

👋

</h2>

<?php if($pending > 0): ?>

<!--
=====================================================
PENDING BRANCH: show both Continue + Fresh buttons
=====================================================
-->

<div class="yesterday-box">

<h3>

Yesterday (<?php echo $dayName; ?>)

</h3>

<div class="summary-row">

<div class="summary-card complete">

<h4>

Completed

</h4>

<p>

<?php echo $completed; ?>

</p>

</div>

<div class="summary-card pending">

<h4>

Pending

</h4>

<p>

<?php echo $pending; ?>

</p>

</div>

</div>

<p class="message">

You still have

<strong>

<?php echo $pending; ?>

unfinished task<?php echo $pending>1?'s':''; ?>

</strong>

from yesterday.

</p>

</div>

<h3 class="question">

What would you like to do today?

</h3>

<div class="action-buttons">

<a href="continue.php"
class="btn btn-primary big-btn">

🔄 Continue Yesterday's Tasks

</a>

<a href="fresh.php"
class="btn btn-outline big-btn">

🌱 Start Fresh Today

</a>

</div>

<?php else: ?>

<!--
=====================================================
APPRECIATION BRANCH: 0 pending from yesterday.
Show encouragement + a single "Start Fresh" button.
=====================================================
-->

<div class="yesterday-box">

<h3>

Yesterday (<?php echo $dayName; ?>)

</h3>

<div class="summary-row">

<div class="summary-card complete">

<h4>

Completed

</h4>

<p>

<?php echo $completed; ?>

</p>

</div>

</div>

<p class="message">

🎉 You completed <strong>all</strong> of yesterday's tasks!
Keep the momentum going.

</p>

</div>

<h3 class="question">

Ready to start a fresh day?

</h3>

<div class="action-buttons">

<a href="fresh.php"
class="btn btn-primary big-btn">

🌱 Start Fresh Today

</a>

</div>

<?php endif; ?>

</div>

</div>

<?php require '../includes/footer.php'; ?>
