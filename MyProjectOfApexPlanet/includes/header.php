<?php

header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

?>
<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Flow</title>
    <link rel="stylesheet" href="/myProjectOfApexPlanet/assets/css/style.css">
    <link rel="stylesheet" href="/myProjectOfApexPlanet/assets/css/profile.css">
    <link rel="icon" type="image/x-icon" href="/myProjectOfApexPlanet/assets/images/favicon.png">
    <link rel="stylesheet" href="/myProjectOfApexPlanet/assets/css/error.css">
</head>
<body>