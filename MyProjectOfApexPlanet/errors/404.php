<?php
$pageTitle = "404 - Page Not Found";
require "../includes/header.php";
?>

<div class="error-page">

    <div class="error-code">
        404
    </div>

    <h1>Oops! Page Not Found</h1>

    <p>
        The page you're looking for doesn't exist,
        may have been moved, or the URL is incorrect.
    </p>

    <div class="error-buttons">

        <a href="/myProjectOfApexPlanet/index.php"
           class="btn btn-primary">
            🏠 Go Home
        </a>

        <button onclick="history.back()"
                class="btn btn-outline">
            ← Go Back
        </button>

    </div>

</div>

<?php require "../includes/footer.php"; ?>