<?php
session_start();
session_destroy();
header('Location: /cms/auth/login.php');
exit();
?>
