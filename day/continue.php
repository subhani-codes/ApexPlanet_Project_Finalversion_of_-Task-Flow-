<?php
session_start();

require '../db.php';
require '../includes/date_helper.php';
require_once '../helpers/logger.php';

if(!isset($_SESSION['user_id'])){
    header("Location: /myProjectOfApexPlanet/login.php");
    exit();
}

$user_id=$_SESSION['user_id'];

$today = getToday();
$yesterday = getYesterday();

$pdo->beginTransaction();

try{

    /*
    ---------------------------------------------------------
    Step 1: Fetch only the uncompleted + still-open tasks
    from yesterday. day_closed=0 ensures we don't pick up
    rows the user has already explicitly closed or that
    another flow (e.g. fresh) already locked.
    ---------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT id, title, description, priority
        FROM todos
        WHERE user_id = ?
          AND status != 'completed'
          AND day_closed = 0
          AND DATE(created_at) = ?
    ");

    $stmt->execute([
        $user_id,
        $yesterday
    ]);

    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /*
    ---------------------------------------------------------
    Step 2: Copy each uncompleted task into today as a NEW row.
    - created_at = NOW() so it belongs to today for the
      weekly progress chart and updateDailyProgress().
    - day_closed = 0 so today's row can itself be closed
      at the end of today.
    - status = 'pending' (we never carry completed rows here).
    ---------------------------------------------------------
    */

    $insert = $pdo->prepare("
        INSERT INTO todos
        (
            user_id,
            title,
            description,
            priority,
            status,
            day_closed,
            created_at
        )
        VALUES
        (
            ?,?,?,?,'pending',0,NOW()
        )
    ");

    foreach($tasks as $task){

        $insert->execute([
            $user_id,
            $task['title'],
            $task['description'],
            $task['priority'] ?? 'medium'
        ]);

    }

    /*
    ---------------------------------------------------------
    Step 3: Lock yesterday. Mark EVERY row from yesterday
    (completed or not) as day_closed=1 so the next login
    doesn't re-prompt the user with the same carry-over.
    The history stays intact for the weekly progress chart.
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

    $pdo->commit();

    /*
    ---------------------------------------------------------
    Step 4: Refresh today's daily_progress row so the carried
    over tasks are immediately reflected in the percentage.
    ---------------------------------------------------------
    */
    require_once '../includes/progress_helper.php';
    updateDailyProgress($pdo, $user_id);

    logActivity($pdo, $user_id, 'Day Continued', 'Carried over ' . count($tasks) . ' task(s) from ' . $yesterday);

}
catch(Exception $e){

    $pdo->rollBack();

}

header("Location:/myProjectOfApexPlanet/todos/view.php");

exit();