<?php
session_start();
session_unset();
session_destroy();
// The login page is in the parent directory of the Seller folder
header('Location: ../login.php');
exit();
?> 