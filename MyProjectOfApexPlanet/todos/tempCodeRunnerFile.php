<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
require '../db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: /myProjectOfApexPlanet/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Get all todos
$stmt = $pdo->prepare("SELECT * FROM todos WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$todos = $stmt->fetchAll();

// Count stats
$total = count($todos);
$completed = count(array_filter($todos, fn($t) => $t['status'] == 'completed'));
$pending = $total - $completed;
?>
<?php require '../includes/header.php'; ?>
<?php require '../includes/navbar.php'; ?>

<?php
$percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
?>

<div class="hero">
    <h1>Hey, <span><?php echo htmlspecialchars($_SESSION['username']); ?></span> 👋</h1>
    <p>
        <?php if($pending > 0): ?>
            <?php echo $pending; ?> task<?php echo $pending != 1 ? 's' : ''; ?> pending — stay focused and keep going!
        <?php else: ?>
            All tasks completed! Amazing work today! 🎉
        <?php endif; ?>
    </p>
    <div class="hero-btns">
        <a href="create.php" class="btn btn-primary">+ Add task</a>
    </div>
</div>

<div class="container">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-num"><?php echo $total; ?></div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-num"><?php echo $completed; ?></div>
            <div class="stat-label">Done</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-num"><?php echo $pending; ?></div>
            <div class="stat-label">Pending</div>
        </div>
    </div>

    <div class="progress-wrap">
        <div class="progress-top">
            <span>Progress</span>
            <span><?php echo $percentage; ?>% complete</span>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" style="width:<?php echo $percentage; ?>%"></div>
        </div>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>

    <div class="section-header">
        <div class="section-title">My tasks</div>
        <a href="create.php" class="btn btn-primary btn-sm">+ Add</a>
    </div>

    <?php if($total > 0): ?>
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
                        <?php echo $todo['status'] == 'completed' ? '✓ Completed' : '⏳ Pending'; ?>
                    </span>
                    <div style="display:flex;gap:4px">
                        <a href="edit.php?id=<?php echo $todo['id']; ?>" class="btn btn-amber btn-sm">Edit</a>
                        <a href="/myProjectOfApexPlanet/todos/delete.php?id=<?php echo $todo['id']; ?>" class="btn btn-red btn-sm" onclick="return confirmDelete()">Delete</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <h3>No tasks yet!</h3>
            <p>Start by adding your first task for today.</p>
            <a href="create.php" class="btn btn-primary">+ Add your first task</a>
        </div>
    <?php endif; ?>

</div>
<?php require '../includes/footer.php'; ?>