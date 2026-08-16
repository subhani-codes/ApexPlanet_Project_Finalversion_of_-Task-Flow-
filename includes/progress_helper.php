<?php

function updateDailyProgress($pdo, $user_id)
{
    $today = date('Y-m-d');

    // Count today's tasks
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM todos
        WHERE user_id = ?
        AND DATE(created_at)=?
    ");
    $stmt->execute([$user_id, $today]);
    $total = (int)$stmt->fetchColumn();

    // Count today's completed tasks
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM todos
        WHERE user_id = ?
        AND status='completed'
        AND DATE(created_at)=?
    ");
    $stmt->execute([$user_id, $today]);
    $completed = (int)$stmt->fetchColumn();

    $pending = $total - $completed;

    $percentage = 0;

    if ($total > 0) {
        $percentage = round(($completed / $total) * 100);
    }

    // Insert or Update today's progress
    $stmt = $pdo->prepare("
        INSERT INTO daily_progress
        (
            user_id,
            progress_date,
            total_tasks,
            completed_tasks,
            pending_tasks,
            percentage
        )
        VALUES (?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE

            total_tasks=VALUES(total_tasks),

            completed_tasks=VALUES(completed_tasks),

            pending_tasks=VALUES(pending_tasks),

            percentage=VALUES(percentage)
    ");

    $stmt->execute([
        $user_id,
        $today,
        $total,
        $completed,
        $pending,
        $percentage
    ]);

}

/*
|--------------------------------------------------------------------------
| Weekly Pruning
|--------------------------------------------------------------------------
| Removes daily_progress rows older than 7 days so the profile
| Mon-Sun chart only ever shows the current week. Safe to call
| on every login — it deletes nothing recent.
*/

function pruneWeeklyProgress($pdo, $user_id = null)
{
    $cutoff = date('Y-m-d', strtotime('-7 days'));

    if ($user_id === null) {
        $stmt = $pdo->prepare("
            DELETE FROM daily_progress
            WHERE progress_date < ?
        ");
        $stmt->execute([$cutoff]);
        return;
    }

    $stmt = $pdo->prepare("
        DELETE FROM daily_progress
        WHERE user_id = ?
          AND progress_date < ?
    ");
    $stmt->execute([$user_id, $cutoff]);
}

/*
|--------------------------------------------------------------------------
| Archive Yesterday's Progress
|--------------------------------------------------------------------------
| Snapshots yesterday's todo totals into daily_progress. Called
| on login so the profile chart still has data for the just-ended
| day even after the todo rows themselves are deleted. Idempotent
| (uses ON DUPLICATE KEY UPDATE).
*/

function archiveYesterdayProgress($pdo, $user_id)
{
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    // Make sure today's row is fresh first so archiveYesterdayProgress
    // doesn't accidentally snapshot today's incomplete work.
    updateDailyProgress($pdo, $user_id);

    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed
        FROM todos
        WHERE user_id = ?
          AND DATE(created_at) = ?
    ");
    $stmt->execute([$user_id, $yesterday]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $total     = (int)($row['total']     ?? 0);
    $completed = (int)($row['completed'] ?? 0);
    $pending   = max(0, $total - $completed);
    $percentage = $total > 0 ? (int)round(($completed / $total) * 100) : 0;

    $stmt = $pdo->prepare("
        INSERT INTO daily_progress
            (user_id, progress_date, total_tasks, completed_tasks, pending_tasks, percentage, day_closed, carry_forward_done)
        VALUES (?,?,?,?,?,?, 1, 0)
        ON DUPLICATE KEY UPDATE
            total_tasks     = VALUES(total_tasks),
            completed_tasks = VALUES(completed_tasks),
            pending_tasks   = VALUES(pending_tasks),
            percentage      = VALUES(percentage),
            day_closed      = 1,
            carry_forward_done = 0
    ");
    $stmt->execute([
        $user_id,
        $yesterday,
        $total,
        $completed,
        $pending,
        $percentage
    ]);

    return [
        'yesterday'  => $yesterday,
        'total'      => $total,
        'completed'  => $completed,
        'pending'    => $pending,
        'percentage' => $percentage,
    ];
}

/*
|--------------------------------------------------------------------------
| Delete Yesterday's Todos
|--------------------------------------------------------------------------
| Hard-deletes yesterday's todo rows for the user. Called after
| archiveYesterdayProgress() so the snapshot is already saved.
*/

function deleteYesterdayTodos($pdo, $user_id)
{
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    $stmt = $pdo->prepare("
        DELETE FROM todos
        WHERE user_id = ?
          AND DATE(created_at) = ?
    ");
    $stmt->execute([$user_id, $yesterday]);

    return $stmt->rowCount();
}
