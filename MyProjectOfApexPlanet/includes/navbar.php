<?php
// Establish fallback path defaults
$brand_destination = "/myProjectOfApexPlanet/login.php";

// Evaluate session identities to set dynamic routing destination targets
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        $brand_destination = "/myProjectOfApexPlanet/admin/dashboard.php";
    } else {
        $brand_destination = "/myProjectOfApexPlanet/todos/view.php";
    }
}
?>

<nav class="navbar">
    <div class="navbar-brand">
        <!-- 🚀 FIX: Logo and title link adapt directly depending on account level -->
        <a href="<?php echo $brand_destination; ?>">
            <div class="brand-logo">TF</div>
            <div class="brand-name">Task<span>Flow</span></div>
        </a>
    </div>

    <?php if(isset($_SESSION['user_id'])): ?>
    <div class="navbar-search-wrapper">
        <form method="GET" action="/myProjectOfApexPlanet/todos/view.php" class="nav-search-form">
            <span class="search-icon-symbol">🔍</span>
            <input type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Search system tasks...">
        </form>
    </div>
    <?php endif; ?>

    <div class="navbar-menu">
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($_SESSION['role'] === 'admin'): ?>
                <a href="/myProjectOfApexPlanet/admin/dashboard.php" style="font-weight:600; color:#ebd3a0;">👑 Admin Control Center</a>
            <?php else: ?>
                <a href="/myProjectOfApexPlanet/todos/view.php">Dashboard</a>
                <a href="/myProjectOfApexPlanet/profile/index.php"> My Profile </a>
                <a href="/myProjectOfApexPlanet/todos/create.php">Add task</a>
            <?php endif; ?>
            <a href="/myProjectOfApexPlanet/logout.php" class="btn btn-red btn-sm" style="color:white; margin-left:10px;">Logout</a>
        <?php else: ?>
            <a href="/myProjectOfApexPlanet/login.php">Login</a>
            <a href="/myProjectOfApexPlanet/register.php">Register</a>
        <?php endif; ?>
        <button class="dark-toggle" onclick="toggleDarkMode()" id="darkToggleBtn">Dark</button>
    </div>
</nav>

<?php if(isset($_SESSION['user_id'])): ?>
<nav class="bottom-nav">
    <?php if($_SESSION['role'] === 'admin'): ?>
        <a href="/myProjectOfApexPlanet/admin/dashboard.php" class="bnav-item active"><span>Control Panel</span></a>
        <a href="/myProjectOfApexPlanet/admin/invite.php" class="bnav-item"><span>Invite Admin</span></a>
    <?php else: ?>
        <a href="/myProjectOfApexPlanet/todos/view.php" class="bnav-item active"><span>Home</span></a>
        <a href="/myProjectOfApexPlanet/todos/create.php" class="bnav-item"><span>Add</span></a>
    <?php endif; ?>
    <a href="/myProjectOfApexPlanet/logout.php" class="bnav-item"><span>Logout</span></a>
</nav>
<?php endif; ?>

