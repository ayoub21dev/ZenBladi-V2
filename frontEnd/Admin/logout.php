<?php
require_once __DIR__ . '/../../Includes/session_config.php';
session_unset();
session_destroy();
header('Location: ../login.php');
exit();
