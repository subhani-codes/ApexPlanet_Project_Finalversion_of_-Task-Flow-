<?php
// 🌟 NEW: Inject our dedicated security helper layer immediately
require_once '../includes/auth_helper.php';

// Enforce baseline authentication checks natively before executing queries
confirm_authenticated();

require '../db.php';

$user_id = $_SESSION['user_id'];
// Dynamic greeting based on server time
$currentHour = date('H');

if ($currentHour < 12) {
    $greeting = "Good Morning";
} elseif ($currentHour < 17) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}

// 🌟 NEW: Sanitize our search text query parameter against Cross-Site Scripting (XSS)
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Pagination Configuration Formulas
$limit = 5; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }

// Mathematical representation of our row offset targeting:
// $$\text{Offset} = (\text{Page} - 1) \times \text{Limit}$$
$offset = ($page - 1) * $limit;

// Extract metrics summaries across background rows safely
$statsStmt = $pdo->prepare("SELECT * FROM todos WHERE user_id = ?");
$statsStmt->execute([$user_id]);
$allUserTodos = $statsStmt->fetchAll();

$total = count($allUserTodos);
$completed = count(array_filter($allUserTodos, fn($t) => $t['status'] == 'completed'));
$pending = $total - $completed;
$percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
// Dynamic Progress Feedback
if ($percentage == 100) {
    $progressMessage = "Outstanding! Everything is completed. 🎉";
} elseif ($percentage >= 75) {
    $progressMessage = "Excellent work! Keep the momentum going 🚀";
} elseif ($percentage >= 50) {
    $progressMessage = "You're making great progress 💪";
} elseif ($percentage >= 25) {
    $progressMessage = "Nice start! Stay consistent ⭐";
} else {
    $progressMessage = "Let's complete your first tasks today 🔥";
}

$remainingGoal = max(0, $pending);

// Dynamic Safe Parameterized Query Execution
if ($search !== '') {
    $countSql = "SELECT COUNT(*) FROM todos WHERE user_id = ? AND (title LIKE ? OR description LIKE ?)";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$user_id, "%$search%", "%$search%"]);
    $totalRecords = $countStmt->fetchColumn();

    $querySql = "SELECT * FROM todos WHERE user_id = ? AND (title LIKE ? OR description LIKE ?) ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($querySql);
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(3, "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(4, $limit, PDO::PARAM_INT);
    $stmt->bindValue(5, $offset, PDO::PARAM_INT);
    $stmt->execute();
} else {
    $countSql = "SELECT COUNT(*) FROM todos WHERE user_id = ?";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$user_id]);
    $totalRecords = $countStmt->fetchColumn();

    $querySql = "SELECT * FROM todos WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($querySql);
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
}

$todos = $stmt->fetchAll();
$totalPages = ceil($totalRecords / $limit);

require '../includes/header.php';
require '../includes/navbar.php';
?>
<!-- Displays account rank dynamically based on session data -->
<?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <span class="badge badge-pending" style="background: #682279; color: white; margin-left: 0px;">------- >        Admin Mode</span>
<?php endif; ?>
<div class="hero">

    <div class="hero-content">

        <div class="hero-text">

            <h1>
                <?php echo $greeting; ?>,
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span> 👋
            </h1>

            <p id="motivationalQuote" class="hero-quote">
                Loading your daily inspiration...
            </p>

            <div class="hero-status-card">

                <div class="hero-progress">

                    <h3><?php echo $percentage; ?>%</h3>

                    <p>Tasks Completed</p>

                </div>

                <div class="hero-divider"></div>

                <div class="hero-pending">

                    <h3><?php echo $pending; ?></h3>

                    <p>Remaining Tasks</p>

                </div>

            </div>

            <div class="hero-btns">

                <a href="create.php" class="btn btn-primary">
                    ➕ Create Task
                </a>

                <a href="#tasks" class="btn btn-outline">
                    📋 View Tasks
                </a>

            </div>

        </div>

    </div>

</div>

<div class="container">
    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-layout">
        
        <!-- SIDEBAR CONTAINER (Stacks on top on mobile, sits right on desktop) -->
        <div class="sidebar-right">
            <div class="stats-sidebar-grid">

    <div class="stat-card total-card">

        <div class="stat-icon">
            📋
        </div>

        <div class="stat-num">
            <?php echo $total; ?>
        </div>

        <div class="stat-label">
            Total Tasks
        </div>

        <div class="stat-info">

            Manage your complete workflow

        </div>

    </div>

    

    <div class="stat-card pending-card">

        <div class="stat-icon">
            ⏳
        </div>

        <div class="stat-num">
            <?php echo $pending; ?>
        </div>

        <div class="stat-label">
            Pending
        </div>

        <div class="stat-info">

            Keep moving 🚀

        </div>

    </div>

    <div class="stat-card done-card">

        <div class="stat-icon">
            ✅
        </div>

        <div class="stat-num">
            <?php echo $completed; ?>
        </div>

        <div class="stat-label">
            Completed
        </div>

        <div class="stat-info">

            Excellent progress

        </div>

    </div>
</div>

           <div class="progress-wrap sidebar-progress-card">

    <h3 class="progress-title">
        📈 Progress Overview
    </h3>

    <div class="progress-top">

        <span><?php echo $completed; ?> of <?php echo $total; ?> completed</span>

        <strong><?php echo $percentage; ?>%</strong>

    </div>

    <div class="progress-bar">

        <div class="progress-fill"
             style="width:<?php echo $percentage; ?>%">
        </div>

    </div>

    <p class="progress-message">

        <?php echo $progressMessage; ?>

    </p>

    <?php if($pending>0): ?>

    <div class="goal-card">

        🎯 Goal:
        Complete
        <strong><?php echo $remainingGoal; ?></strong>
        more task<?php echo $remainingGoal>1?'s':''; ?>

    </div>

    <?php endif; ?>

</div>
        </div>

        <!-- TASKS COLUMN CONTAINER (Flows downstream on mobile, expanding left on desktop) -->
        <div class="main-content-left" id="tasks">
            <div class="section-header">
                <div class="section-title">My Tasks</div>
                <a href="create.php" class="btn btn-primary btn-sm">+ Add</a>
            </div>

            <?php if(count($todos) > 0): ?>
                <?php foreach($todos as $todo): ?>
                <div class="todo-item <?php echo $todo['status']=='completed' ? 'completed' : ''; ?>">
                    <div style="flex:1">
                        <div class="todo-title <?php echo $todo['status']=='completed' ? 'done' : ''; ?>">
                            <?php echo htmlspecialchars($todo['title']); ?>
                        </div>
                        <?php if($todo['description']): ?>
                        <div class="todo-meta">
                            <?php echo htmlspecialchars($todo['description']); ?>
                        </div>
                        <?php endif; ?>
                        <div class="todo-bottom">
                            <span class="badge badge-<?php echo $todo['status']; ?>">
                                <?php echo $todo['status'] == 'completed' ? 'Completed' : 'Pending'; ?>
                            </span>
                            <div style="display:flex;gap:4px">
                                <a href="edit.php?id=<?php echo $todo['id']; ?>" class="btn btn-amber btn-sm">Edit</a>
                                <a href="/myProjectOfApexPlanet/todos/delete.php?id=<?php echo $todo['id']; ?>" class="btn btn-red btn-sm" onclick="return confirmDelete()">Delete</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if($totalPages > 1): ?>
                <div class="pagination-container">
                    <?php for($i = 1; $i <= $totalPages; $i++): ?>
                        <?php 
                            $urlParams = ['page' => $i];
                            if($search !== '') { $urlParams['search'] = $search; }
                            $queryString = http_build_query($urlParams);
                        ?>
                        <a href="view.php?<?php echo $queryString; ?>" class="btn <?php echo $page === $i ? 'btn-primary' : 'btn-outline'; ?> btn-sm">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>

            <?php else: ?>
               <div class="empty-state">

                <div style="font-size:60px;margin-bottom:15px;">
                📋
                </div>
                            
                <h3>No Tasks Yet</h3>
                            
                <p>
                            
                Looks like your workspace is empty.
                            
                Create your first task and start tracking your productivity.
                            
                </p>
                            
                <a href="create.php" class="btn btn-primary">
                            
                + Create Task
                            
                </a>
                            
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>


<?php require '../includes/footer.php'; ?>