<?php
require_once '../config/config.php';

// Destroy the session
session_destroy();

// Redirect to the landing page
header("Location: ../landing/index.php");
exit();
?> 