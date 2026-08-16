<?php
// ======================================
// Authentication
// ======================================
require_once '../includes/auth_helper.php';
confirm_authenticated();

// ======================================
// Database
// ======================================
require '../db.php';

// ======================================
// Activity Logger
// ======================================
require_once "../helpers/logger.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 2. Capture and sanitize inputs
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $user_id = $_SESSION['user_id'];

    logActivity(
    $pdo,
    $user_id,
    "Created",
    $title
);
    // 🚀 NEW INTERNSHIP REQUIREMENTS: ADVANCED SERVER-SIDE VALIDATION
    
    // Check 1: Block empty titles or strings made of pure spaces (e.g., "    ")
    if (empty($title)) {
        $error = "❌ Task Title cannot be left blank or filled with empty spaces!";
        
    // Check 2: Imposed description length boundary caps to protect server memory
    } elseif (strlen($description) > 500) {
        $error = "❌ Description is too heavy! Please limit it to under 500 characters.";
        
    } else {
        // 3. Security Check passed! Execute data insertion safely using parameters
        $stmt = $pdo->prepare("INSERT INTO todos (user_id, title, description, status) VALUES (?, ?, ?, 'pending')");
        if ($stmt->execute([$user_id, $title, $description])) {
            // Send user back to dashboard with a clear success parameter token
            header("Location: view.php?success=" . urlencode("Task created successfully!"));
            exit();
        } else {
            $error = "❌ System Error: Could not compile data pipeline task to database.";
        }
    }
}

require '../includes/header.php';
require '../includes/navbar.php';
?>

<div style="display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 140px); padding: 40px 16px; box-sizing: border-box;">
    
    <div class="card" style="width: 100%; max-width: 600px; padding: 35px; border: 1.5px solid var(--border); background: var(--surface); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); margin: 0;">
        <h2 style="color: var(--text); margin-bottom: 5px; font-weight: 800;">📝 Create New Task Node</h2>
        <p style="color: var(--text2); font-size: 13px; margin-bottom: 25px;">Map out your daily milestones and technical targets cleanly.</p>
        
        <?php if(!empty($error)): ?>
            <div class="alert alert-error" style="background: #fee2e2; color: #991b1b; border-left: 4px solid #dc2626; padding: 12px; margin-bottom: 20px; border-radius: 8px; font-weight: 600;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label style="color: var(--text); font-weight: 700;">Task Target Title</label>
                <input type="text" name="title" placeholder="What needs to be compiled?" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required style="border: 1.5px solid var(--border); background: var(--bg); color: var(--text); padding: 12px; border-radius: 8px; width: 100%;">
            </div>
            
            <div class="form-group" style="margin-top: 20px;">
                <label style="color: var(--text); font-weight: 700;">Operational Specifications (Details)</label>
                <textarea name="description" rows="5" placeholder="Describe the technical details of this target... (Max 500 characters)" style="border: 1.5px solid var(--border); background: var(--bg); color: var(--text); padding: 12px; border-radius: 8px; width: 100%; font-family: inherit; resize: vertical;"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>
            
            <div style="display: flex; gap: 15px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center;">🚀 Launch Task</button>
                <a href="view.php" class="btn btn-outline" style="text-decoration: none; text-align: center; line-height: 20px; color: var(--text);">Cancel</a>
            </div>
        </form>
    </div>
</div>



<?php require '../includes/footer.php'; ?>