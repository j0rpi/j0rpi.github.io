<?php
// --------------------------------------------------------
//
// j0rpi_GameDB
//
// File: config.php
// Purpose: Configuration file for GameDB
//
// Update: This file is deprecated.
//
// --------------------------------------------------------

// Define Database
$db = new SQLite3('../games.db');

// Skin
$style = 'default';

// The title in the users webbrowser window/tab
$headerTitle = 'GameDB';

// Maximum games shown on main page
$listMax = '25';

// Minimum selectable year for filter and admin page
$minSelectableYear = '1960';

// Use rating icons instead of numbers
$useRatingIcons = 1;

// Use platform icons instead of text
$usePlatformIcons = 1;

// Text displayed on VOD links
$vodLinkText = "Watch VOD";

$igdbClientID = "";
$igdbClientSecret = "";
$igdbAccessToken = "";
?>