<?php
require_once '../includes/auth_helper.php';
enforce_permission(['admin']); // 🔒 Route Access Guard

require '../db.php';

// 🚀 NEW: Scalar Aggregate Query to fetch the total number of active administrators
$adminCountQuery = "SELECT COUNT(*) FROM users WHERE role = 'admin'";
$adminCountStmt = $pdo->query($adminCountQuery);
$totalAdmins = $adminCountStmt->fetchColumn();

// Evaluate active view filters via simple URL parameters
$view_mode = isset($_GET['view']) && $_GET['view'] === 'admins' ? 'admins' : 'users';

if ($view_mode === 'admins') {
    $querySql = "SELECT id, username, email, role, status, 0 as total_tasks, 0 as completed_tasks FROM users WHERE role = 'admin' ORDER BY id DESC";
} else {
    $querySql = "
        SELECT u.id, u.username, u.email, u.role, u.status,
               COUNT(t.id) as total_tasks,
               SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks
        FROM users u
        LEFT JOIN todos t ON u.id = t.user_id
        WHERE u.role = 'user'
        GROUP BY u.id
        ORDER BY u.id DESC
    ";
}

$stmt = $pdo->prepare($querySql);
$stmt->execute();
$dataset = $stmt->fetchAll();

require '../includes/header.php';
require '../includes/navbar.php';
?>

<div class="hero" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-bottom: 3px solid #d97706; padding: 40px 20px;">
    <h1 style="font-size: 32px; letter-spacing: -1px; margin-bottom: 8px;">💎 TaskFlow <span style="background: linear-gradient(90deg, #fbbf24, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Executive Workspace</span></h1>
    
    <div style="margin-bottom: 12px;">
        <span style="background: rgba(217, 119, 6, 0.15); border: 1.5px solid #d97706; padding: 6px 14px; border-radius: 20px; font-size: 12px; color: #fbbf24; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(217,119,6,0.15);">
            🛡️ Administrative Management Cleared — Active Admins: <?php echo $totalAdmins; ?>
        </span>
    </div>

    <p class="hero-quote" style="color: #94a3b8 !important; max-width: 700px; margin: 5px auto 0 auto;">Real-time enterprise intelligence and cryptographic role isolation monitors.</p>
    
    <div class="hero-btns" style="margin-top: 20px;">
        <a href="invite.php" class="btn" style="background: linear-gradient(135deg, #d97706, #b45309) !important; color: white; box-shadow: 0 4px 14px rgba(217,119,6,0.3); font-weight: 700; padding: 12px 24px;">⚡ Create Admin Account</a>
    </div>
</div>

<div class="container" style="margin-top: 30px; padding: 0 16px; box-sizing: border-box;">
    
    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success" style="background: #10b981; color: white; border-radius: 8px; font-weight: 600; padding: 12px; margin-bottom: 20px;"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error" style="background: #ef4444; color: white; border-radius: 8px; font-weight: 600; padding: 12px; margin-bottom: 20px;"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <div class="admin-tab-row" style="display: flex; gap: 12px; margin-bottom: 30px; flex-wrap: wrap;">
        <a href="dashboard.php?view=users" class="btn <?php echo $view_mode === 'users' ? 'btn-primary' : 'btn-outline'; ?>" style="flex: 1; min-width: 260px; justify-content: center; padding: 12px; text-align: center;">📊 Task Stream Feed (Users Only)</a>
        <a href="dashboard.php?view=admins" class="btn <?php echo $view_mode === 'admins' ? 'btn-primary' : 'btn-outline'; ?>" style="flex: 1; min-width: 260px; justify-content: center; padding: 12px; text-align: center;">🛡️ View Admins Compartment</a>
    </div>

    <div class="section-header" style="border-bottom: 2px solid var(--border); padding-bottom: 12px; margin-bottom: 25px;">
        <h2 style="font-size: 20px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text); font-weight: 800; margin: 0;">
            <?php echo $view_mode === 'admins' ? '🔒 SECURE ADMIN COMPARTMENT REGISTRY' : '📈 SYSTEM USER PRODUCTIVITY REGISTRY'; ?>
        </h2>
    </div>

    <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 80px;">
        <?php foreach ($dataset as $row): ?>
            <?php 
                $isSuspended = ($row['status'] === 'suspended');
                $tCount = (int)$row['total_tasks'];
                $cCount = (int)$row['completed_tasks'];
                $pCount = $tCount - $cCount;
                $rate = $tCount > 0 ? round(($cCount / $tCount) * 100) : 0;
            ?>
            <div class="todo-item" style="border: 2px solid var(--border); border-radius: 12px; padding: 20px; background: var(--surface); display: flex; flex-direction: column; gap: 15px; opacity: <?php echo $isSuspended ? '0.6' : '1'; ?>; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
                
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; width: 100%;">
                    
                    <div style="flex: 1; min-width: 240px;">
                        <h3 style="color: var(--text); font-size: 19px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; margin: 0; flex-wrap: wrap;">
                            <span><?php echo $row['role'] === 'admin' ? '🛡️' : '👤'; ?> <?php echo htmlspecialchars($row['username']); ?></span>
                            <span style="display: inline-flex; gap: 4px;">
                                <span class="badge" style="background: var(--surface); border: 1px solid var(--border); color: var(--text2); font-size: 10px; padding: 2px 8px; border-radius: 4px; font-weight: 700;">
                                    <?php echo strtoupper($row['role']); ?>
                                </span>
                                <span class="badge" style="background: <?php echo $isSuspended ? '#ef4444' : '#10b981'; ?>; color: white; font-size: 10px; padding: 2px 8px; border-radius: 4px; font-weight: 700;">
                                    <?php echo strtoupper($row['status']); ?>
                                </span>
                            </span>
                        </h3>
                        <p style="color: var(--text2); font-size: 13px; margin-top: 6px; word-break: break-all;">✉️ Identity Node: <strong><?php echo htmlspecialchars($row['email']); ?></strong></p>
                    </div>

                    <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                        <?php if ($view_mode === 'users'): ?>
                            <div style="display: flex; gap: 8px;">
                                <div style="text-align: center; background: var(--bg); padding: 6px 12px; border-radius: 8px; font-weight: 700; color: var(--text); min-width: 60px;">
                                    <?php echo $tCount; ?><div style="font-size: 9px; font-weight: 500; color: var(--text2);">Total</div>
                                </div>
                                <div style="text-align: center; background: #d1fae5; padding: 6px 12px; border-radius: 8px; font-weight: 700; color: #065f46; min-width: 60px;">
                                    <?php echo $cCount; ?><div style="font-size: 9px; font-weight: 500; color: #0f766e;">Done</div>
                                </div>
                                <div style="text-align: center; background: #fee2e2; padding: 6px 12px; border-radius: 8px; font-weight: 700; color: #991b1b; min-width: 60px;">
                                    <?php echo $pCount; ?><div style="font-size: 9px; font-weight: 500; color: #b91c1c;">Pending</div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div style="display: inline-block;">
                            <?php if ($row['id'] !== (int)$_SESSION['user_id']): ?>
                                <form method="POST" action="toggle_status.php" style="margin: 0;">
                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="current_status" value="<?php echo $row['status']; ?>">
                                    <button type="submit" class="btn <?php echo $isSuspended ? 'btn-green' : 'btn-red'; ?> btn-sm" style="border-radius: 6px; font-weight: 700; padding: 6px 12px; font-size: 11px;">
                                        <?php echo $isSuspended ? '🔓 Reactivate' : '🚫 Suspend Access'; ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span style="font-size: 11px; color: var(--text3); font-weight: 700; background: var(--bg); padding: 6px 12px; border-radius: 6px; border: 1px dashed var(--border);">🔒 Active Master Token</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if ($view_mode === 'users' && !$isSuspended): ?>
                <div class="progress-wrap" style="padding: 0; background: transparent; border: none; margin: 5px 0 0 0; width: 100%;">
                    <div class="progress-top" style="margin-bottom: 6px; display: flex; justify-content: space-between; font-size: 12px;">
                        <span style="color: var(--text2); font-weight: 600;">Node Resolution Velocity</span>
                        <strong style="color: var(--teal);"><?php echo $rate; ?>% Execution Stability</strong>
                    </div>
                    <div class="progress-bar" style="background: var(--border); height: 8px; border-radius: 4px; overflow: hidden; width: 100%;">
                        <div class="progress-fill" style="width: <?php echo $rate; ?>%; background: linear-gradient(90deg, #d97706, #0d9488); height: 100%; border-radius: 4px;"></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require '../includes/footer.php'; ?>

