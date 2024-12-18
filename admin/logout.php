<?php
// --------------------------------------------------------
//
// j0rpi_GameDB
//
// File: index.php
// Purpose: Kill session
//
// --------------------------------------------------------

session_start();
session_unset();
session_destroy();

header('Location: login.php');
exit;
?>