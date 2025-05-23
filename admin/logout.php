<?php
session_start();

// Hapus session admin
unset($_SESSION['admin_logged_in']);
unset($_SESSION['user']);

// Redirect ke halaman login admin
header("Location: login.php");
exit;
