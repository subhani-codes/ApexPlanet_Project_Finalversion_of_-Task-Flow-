<?php
// ===============================
// Authentication
// ===============================
require_once '../includes/auth_helper.php';
confirm_authenticated();

// ===============================
// Database
// ===============================
require '../db.php';

// ===============================
// Logger
// ===============================
require_once '../helpers/logger.php';

$error = "";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];
$error = "";
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Fetch the targeted todo record instance safely using parameter bindings
$stmt = $pdo->prepare("SELECT * FROM todos WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$todo = $stmt->fetch();

// Abort and redirect if record mapping fails
if(!$todo){
    header("Location: view.php");
    exit();
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $status = sanitize_input($_POST['status']);
    
    
    // Server-Side Validation Check Triggers
    if(empty($title)){
        $error = "❌ Task Title is explicitly required!";
    } elseif (strlen($description) > 500) {
        $error = "❌ Description threshold exceeded! Keep comments under 500 characters.";
    } else {
        // Execute dynamic row mutation update securely
        $updateStmt = $pdo->prepare("UPDATE todos SET title = ?, description = ?, status = ? WHERE id = ? AND user_id = ?");
        $updateStmt->execute([$title, $description, $status, $id, $user_id]);
        
        header("Location: view.php?success=" . urlencode("Task updated successfully!"));
        exit();
    }
    logActivity(
    $pdo,
    $user_id,
    "Updated",
    $title
);
}

require '../includes/header.php';
require '../includes/navbar.php';
?>

<!-- 🌟 FIX: Viewport Flexbox Layout Centering wrapper with strict navbar clearance margins -->
<div style="display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 140px); padding: 40px 16px; box-sizing: border-box;">
    
    <div class="card" style="width: 100%; max-width: 600px; padding: 35px; border: 2px solid var(--border); background: var(--surface); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); margin: 0;">
        <h2 style="color: var(--text); margin-bottom: 5px; font-weight: 800;">🔄 Modify Operational Task</h2>
        <p style="color: var(--text2); font-size: 13px; margin-bottom: 25px;">Adjust task title properties, update descriptors, and track current resolution lifecycle states.</p>
        
        <?php if(!empty($error)): ?>
            <div class="alert alert-error" style="background: #fee2e2; color: #991b1b; border-left: 4px solid #dc2626; padding: 12px; margin-bottom: 20px; border-radius: 8px; font-weight: 600;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label style="color: var(--text); font-weight: 700;">Task Target Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($todo['title']); ?>" required style="border: 1.5px solid var(--border); background: var(--bg); color: var(--text); padding: 12px; border-radius: 8px; width: 100%;">
            </div>
            
            <div class="form-group" style="margin-top: 20px;">
                <label style="color: var(--text); font-weight: 700;">Operational Specifications (Details)</label>
                <textarea name="description" rows="5" style="border: 1.5px solid var(--border); background: var(--bg); color: var(--text); padding: 12px; border-radius: 8px; width: 100%; font-family: inherit; resize: vertical;"><?php echo htmlspecialchars($todo['description']); ?></textarea>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label style="color: var(--text); font-weight: 700;">Lifecycle Resolution State</label>
                <select name="status" style="border: 1.5px solid var(--border); background: var(--bg); color: var(--text); padding: 12px; border-radius: 8px; width: 100%; font-weight: 600; cursor: pointer;">
                    <option value="pending" <?php echo $todo['status'] === 'pending' ? 'selected' : ''; ?>>⏳ Pending Operation</option>
                    <option value="completed" <?php echo $todo['status'] === 'completed' ? 'selected' : ''; ?>>✅ Completed Node</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 15px; margin-top: 35px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center;">💾 Commit Updates</button>
                <a href="view.php" class="btn btn-outline" style="text-decoration: none; text-align: center; line-height: 20px; color: var(--text); display: inline-flex; justify-content: center; align-items: center;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require '../includes/footer.php'; ?>