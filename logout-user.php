<?php
session_start();
unset($_SESSION['user_id'], $_SESSION['user_nombre'], $_SESSION['user_correo']);
header('Location: index.php');
exit;
