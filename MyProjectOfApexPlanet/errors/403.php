<?php
$pageTitle = "403 - Access Denied";
require "../includes/header.php";
?>

<div class="error-page">

    <div class="error-code forbidden">
        403
    </div>

    <h1>Access Denied</h1>

    <p>
        Sorry, you don't have permission to
        access this page.
    </p>

    <div class="error-buttons">

        <a href="/myProjectOfApexPlanet/index.php"
           class="btn btn-primary">
            🏠 Home
        </a>

        <button onclick="history.back()"
                class="btn btn-outline">
            ← Go Back
        </button>

    </div>

</div>

<?php require "../includes/footer.php"; ?>