<?php
session_start();
session_destroy();
header("Location: /myProjectOfApexPlanet/login.php");
exit();
?>