<?php

session_start();

require '../db.php';
require '../includes/date_helper.php';
require_once '../helpers/logger.php';


if(!isset($_SESSION['user_id'])){
    exit();
}

$user_id=$_SESSION['user_id'];

$yesterday = getYesterday();

/*
---------------------------------------------------------
Lock yesterday in daily_progress so the profile weekly
chart correctly remembers yesterday as a closed day.
---------------------------------------------------------
*/

$stmt = $pdo->prepare("
    UPDATE daily_progress
    SET
        day_closed = 1,
        carry_forward_done = 1
    WHERE
        user_id = ?
        AND progress_date = ?
");

$stmt->execute([
    $user_id,
    $yesterday
]);

/*
---------------------------------------------------------
Also lock yesterday's todos so the next login doesn't
re-prompt "continue yesterday?". We mark ALL of yesterday's
rows (completed or pending) as day_closed=1 — we are NOT
deleting anything, the history stays intact for the weekly
progress chart and the activity log.
---------------------------------------------------------
*/

$stmt = $pdo->prepare("
    UPDATE todos
    SET day_closed = 1
    WHERE user_id = ?
      AND DATE(created_at) = ?
");

$stmt->execute([
    $user_id,
    $yesterday
]);

logActivity($pdo, $user_id, 'Day Started Fresh', 'Discarded carry-over from ' . $yesterday);

header("Location:/myProjectOfApexPlanet/todos/view.php");

exit();