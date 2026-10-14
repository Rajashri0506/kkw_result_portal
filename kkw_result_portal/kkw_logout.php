<?php
/* kkw_logout.php - log out and go back to login page */
require 'kkw_db_config.php';
$_SESSION = [];
session_destroy();
session_name('KKW_SRMS_SESSION');
session_start();
kkw_msg('You have been logged out.', 'info');
header('Location: kkw_login.php');
exit;
