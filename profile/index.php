<?php
require_once '../includes/auth_helper.php';
confirm_authenticated();

require '../db.php';

$user_id = $_SESSION['user_id'];

/* ============================
   Load Logged-in User
============================ */
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

/* ============================
   Task Statistics
============================ */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM todos WHERE user_id = ?");
$stmt->execute([$user_id]);
$totalTasks = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM todos WHERE user_id = ? AND status='completed'");
$stmt->execute([$user_id]);
$completedTasks = $stmt->fetchColumn();

$pendingTasks = $totalTasks - $completedTasks;

$completionRate = ($totalTasks > 0)
    ? round(($completedTasks / $totalTasks) * 100)
    : 0;


/* ==========================
   WEEKLY PROGRESS
   Reads from daily_progress (auto-archived on every login
   / todo CRUD). Limited to current week (Mon-Sun). Previous
   weeks are pruned automatically so the chart never shows
   stale data.
========================== */
$weeklyProgress=[];

$monday = strtotime("monday this week");

for($i=0;$i<7;$i++){

    $date=date("Y-m-d",$monday+($i*86400));

    $stmt=$pdo->prepare("
        SELECT
            total_tasks,
            completed_tasks,
            percentage
        FROM daily_progress
        WHERE user_id=?
          AND progress_date=?
        LIMIT 1
    ");

    $stmt->execute([$user_id,$date]);

    $row=$stmt->fetch(PDO::FETCH_ASSOC);

    $percentage = $row
        ? (int)$row['percentage']
        : 0;

    $weeklyProgress[]=[

        "day"=>date("D",strtotime($date)),
        "date"=>$date,
        "percentage"=>$percentage

    ];

}
require '../includes/header.php';
require '../includes/navbar.php';
?>

<div class="container">
    <?php if(isset($_SESSION['success'])): ?>

        <div class="success-message">

            <?php
                echo $_SESSION['success'];
                unset($_SESSION['success']);
            ?>

        </div>

    <?php endif; ?>

    <div class="profile-card">

        <!-- =========================
             PROFILE HEADER
        ========================== -->

        <div class="profile-header">

    <div class="profile-left">

        <?php
        $avatar = !empty($user['profile_image'])
            ? "/myProjectOfApexPlanet/uploads/profile/" . htmlspecialchars($user['profile_image'])
            : "/myProjectOfApexPlanet/assets/images/default-avatar.png";
        ?>

        <div class="profile-avatar">

            <img
                src="<?php echo $avatar; ?>"
                alt="Profile Picture"
                class="profile-avatar-image">

        </div>
        
    </div>
    
    <div class="profile-right">
        <div class="profile-top-row">
    <div>
        <h2><?php echo htmlspecialchars($user['username']); ?></h2>
    <div class="header-bio">

    <?php
    echo empty($user['bio'])
        ? "No bio added."
        : nl2br(htmlspecialchars($user['bio']));
    ?></div>

    </div>
    <a href="/myProjectOfApexPlanet/profile/edit.php"
       class="btn btn-primary profile-edit-btn">
        Edit Profile
    </a>
</div>



    </div>

</div>
<!-- =========================
     WEEKLY PERFORMANCE
========================= -->

<div class="weekly-progress-card">

    <div class="week-title">
        📊 Weekly Performance
    </div>

    <div class="week-chart">

        <?php foreach($weeklyProgress as $day): ?>

    <?php

    $progress = $day['percentage'];

    if($progress<=20){

        $color="#ef4444";

    }elseif($progress<=40){

        $color="#f97316";

    }elseif($progress<=60){

        $color="#eab308";

    }elseif($progress<=80){

        $color="#22c55e";

    }else{

        $color="#06b6d4";

    }

    ?>

    <div class="day-column">

        <div class="percent">

            <?= $progress ?>%

        </div>

        <div class="performance-bar">

            <div class="performance-fill"
                style="height: <?= $progress ?>%;
                    background: linear-gradient(to top, <?= $color ?>, #5eead4);">
            </div>

        </div>

        <div class="day-name">

            <?= $day['day'] ?>

        </div>

    </div>

    <?php endforeach; ?>

    </div>

</div> 
       <!-- =========================
             PROFILE INFORMATION
        ========================== -->

        <div class="profile-grid">

            <div class="profile-box">
                <h4>📧 Email</h4>
                <p>
                    <?php echo htmlspecialchars($user['email']); ?>
                </p>
            </div>

            <div class="profile-box">
                <h4>📱 Phone</h4>
                <p>
                    <?php
                    echo empty($user['phone'])
                        ? "Not Added"
                        : htmlspecialchars($user['phone']);
                    ?>
                </p>
            </div>

            <div class="profile-box">
                <h4>💼 Occupation</h4>
                <p>
                    <?php
                    echo empty($user['occupation'])
                        ? "Not Specified"
                        : htmlspecialchars($user['occupation']);
                    ?>
                </p>
            </div>
            <div class="profile-box">
                <h4>📊 Total Tasks</h4>
                <p>
                    <?php echo $totalTasks; ?>
                </p>
            </div>

            <div class="profile-box">
                <h4>🕒 Pending</h4>
                <p>
                    <?php echo $pendingTasks; ?>
                </p>
            </div>

           <div class="profile-box">
                <h4>✅ Completed</h4>
                <p>
                    <?php echo $completedTasks; ?>
                </p>
            </div>

        </div>

        

        <!-- =========================
             ACTION BUTTONS
        ========================== -->

        <div class="profile-actions">

            <a href="/myProjectOfApexPlanet/profile/edit.php"
               class="btn btn-primary">

                Edit Profile

            </a>

            <a href="change_password.php"
               class="btn btn-outline">

                Change Password

            </a>


            <a href="/myProjectOfApexPlanet/logout.php"
                class="btn btn-red">
                    Logout
            </a>

        </div>

    </div>

</div>

<?php require '../includes/footer.php'; ?>