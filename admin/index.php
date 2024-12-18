<?php
// --------------------------------------------------------
//
// j0rpi_GameDB
//
// File: admin/index.php
// Purpose: Main administrator page
//
// --------------------------------------------------------

session_start();
// Include version info 
include('../version.php');
// --------------------------------------------------------
//
// Check If User Is Authenticated. Otherwise Redirect To
// Login Page.
//
// --------------------------------------------------------
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}
// --------------------------------------------------------
//
// Define Config And Skin Config
//
// --------------------------------------------------------
include('../include/config.php');
include('../include/functions.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../styles/<?php getConfigVar('style') ?>/style.admin.css">
</head>
<body>
<?php
// --------------------------------------------------------
//
// Display Warning If INSTALL Directory Is Still Present
// While Showing, Tell The User To Genererate IGDB Token
//
// --------------------------------------------------------
$folder = "../install";
$keygen = false;
if(is_dir($folder)) {
	echo "<div class='errorbar'><span style='margin-bottom: 2px'>⚠</span>️ Its strongly adviced to generate an <a href='../install/generate_token.php' style='text-decoration: none; border-bottom: 1px solid white;'>Access Token key for IGDB</a> now before removing the <strong>INSTALL</strong> folder for cover art search support!</div><br><br>";
	echo "<div class='bg-text' style='margin-top: 50px;'>";
	$keygen = true;
}
else
{
	echo "<div class='bg-text'>";
}
?><br>
<?php
// --------------------------------------------------------
//
// Dashboard container
//
// --------------------------------------------------------
?>
    <h1>Admin Dashboard<span style="float:right; font-size: 16px; font-weight: normal;">Logged in As <strong><?php echo $_SESSION['admin_username']; ?></strong></span><br><span style="float:left; font-size: 12px; font-weight: normal;"></span><span style="float:right; font-size: 12px; font-weight: normal;"><a href="password.php" style="">Change Password</a> | <a href="logout.php" style="">Logout</a></h1><br>
    <div class="form-container">
		<h2>Management</h2>
		<div class="thindivider"></div>
		👪 <a href="users">Add/Remove Users</a><br><br><br>
		<h2>Games</h2>
		<div class="thindivider"></div>
		🕹️ <a href="games.php">Manage Games</a><br>
		🎲 <a href="categories.php">Manage Genres</a><br>
		🎮 <a href="platforms.php">Manage Platforms</a><br><br><br>
		<h2>Administrative</h2>
		<div class="thindivider"></div>
		🔑️ <a href="password.php">Change Password</a><br>
		🛠️ <a href="config.php">GameDB Configuration</a><br>
		📸 <a href="igdb_token.php">Generate IGDB Access Token</a><br>
		♻️ <a href="update.php">Check for GameDB Updates</a><br>
		⚙️ <a href="phpinfo.php">PHP Info</a><br><br><br>
		<h2>Database</h2>
		<div class="thindivider"></div>
		💿 <a href="backup.php">Import/Export/Backup Database</a><br>
		☢️ <a href="nuke.php">Wipe Database </a><br><br>
	</div>
<?php
// --------------------------------------------------------
//
// Footer
//
// --------------------------------------------------------
?>
<div class="footerdivider">
	<div class="footer-content">
		<center><a href='https://github.com/j0rpi/GameDB' style='text-decoration: none; border-bottom: 1px dotted white;'>GameDB</a> made with ❤️ by j0rpi<br><span style="font-weight: 200; font-size: 12px;"><?php echo $version; ?></span></center> 
	</div>
</div>
</div>
<?php
// --------------------------------------------------------
//
// End of file.
//
// --------------------------------------------------------
?>
</body>
</html>
