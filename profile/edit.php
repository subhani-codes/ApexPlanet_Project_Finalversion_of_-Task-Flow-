<?php
require_once '../includes/auth_helper.php';
confirm_authenticated();

require '../db.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);

$user = $stmt->fetch();

$message = "";

if($_SERVER["REQUEST_METHOD"]=="POST"){

$name=trim($_POST['username']);
$email=trim($_POST['email']);
$phone=trim($_POST['phone']);
$occupation=trim($_POST['occupation']);
$bio=trim($_POST['bio']);

/* ============================
   Profile Image Upload
============================ */

$profileImage = $user['profile_image'];

if (
    isset($_FILES['profile_image']) &&
    $_FILES['profile_image']['error'] == 0
) {

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    $fileType = mime_content_type($_FILES['profile_image']['tmp_name']);

    if (isset($allowedTypes[$fileType])) {

        $extension = $allowedTypes[$fileType];

        $newFileName = uniqid('profile_', true) . "." . $extension;

        $uploadDir = __DIR__ . "/../uploads/profile/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

move_uploaded_file(
    $_FILES['profile_image']['tmp_name'],
    $uploadDir . $newFileName
);

        $profileImage = $newFileName;
    }
}

$update = $pdo->prepare("
UPDATE users
SET
username = ?,
email = ?,
phone = ?,
occupation = ?,
bio = ?,
profile_image = ?
WHERE id = ?
");

if ($update->execute([
    $name,
    $email,
    $phone,
    $occupation,
    $bio,
    $profileImage,
    $user_id
])) {

    $_SESSION['username'] = $name;

    $message = "Profile updated successfully.";

    // Reload latest user data
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
     header("Location: index.php");

    exit();
}

}

require '../includes/header.php';
require '../includes/navbar.php';
?>

<div class="container">

<div class="card">

<h2>Edit Profile</h2>

<?php if($message!=""): ?>

<div class="alert alert-success">

<?php echo $message; ?>

</div>

<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<div class="form-group">

<label>Name</label>

<input
type="text"
name="username"
value="<?php echo htmlspecialchars($user['username']); ?>"
required>

</div>

<div class="form-group">

<label>Email</label>

<input
type="email"
name="email"
value="<?php echo htmlspecialchars($user['email']); ?>"
required>

</div>

<div class="form-group">

<label>Phone</label>

<input
type="text"
name="phone"
value="<?php echo htmlspecialchars($user['phone']); ?>">

</div>

<div class="form-group">

<label>Occupation</label>

<input
type="text"
name="occupation"
placeholder="Student / Employee / Business..."
value="<?php echo htmlspecialchars($user['occupation']); ?>">

</div>

<div class="form-group">

<label>Bio</label>

<textarea
name="bio"
rows="4"
maxlength="120"><?php echo htmlspecialchars($user['bio']); ?></textarea>

</div>
<div class="form-group">

<label>Profile Picture</label>

<input
type="file"
name="profile_image"
accept=".jpg,.jpeg,.png,.webp">

</div>

<button class="btn btn-primary">

Save Changes

</button>

</form>

</div>

</div>


<?php require '../includes/footer.php'; ?>