<?php
// --------------------------------------------------------
//
// j0rpi_GameDB
//
// File: admin/nuke.php
// Purpose: Provides the ability to wipe the database
//
// --------------------------------------------------------

session_start();
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
include('../version.php');

// --------------------------------------------------------
//
// Setup POST Variables
//
// --------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['wipe_games'])) {
		wipeGames();
	}
	if (isset($_POST['wipe_cats'])) {
		wipeCats();
	}
	if (isset($_POST['wipe_plats'])) {
		wipePlats();
	}
	if (isset($_POST['wipe_all'])) {
		wipeAll();
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../styles/<?php getConfigVar('style') ?>/style.catplat.css">
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
	<div class="info-container">
		<span>Note</span>
		<div class="thindivider"></div>
		<p><strong>"Wipe All"</strong> will wipe the whole database.</p><p><strong>User accounts</strong> and <strong>configuration</strong> will not be wiped.</p>
	</div>
	<div class="form-container" style="text-align: center; width: 75%;">
	<span class="button-row-text"><a href="index.php">Admin Dashboard</a> > Wipe Database</span><br>
    <div class="thindivider"></div>
	
		
		<form method="POST">
			<center>
				<button type="submit" name="wipe_db" style="width: 22%;">☢️ Wipe Games</button> <button type="submit" name="wipe_db" style="width: 22%;">☢️ Wipe Cats</button> <button type="submit" name="wipe_db" style="width: 22%;">☢️ Wipe Plats</button> <button type="submit" name="wipe_db" style="width: 22%; background-color: rgb(200,25,25);">☢️ Wipe All</button>
			</center>
		</form>
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
