<?php
// --------------------------------------------------------
//
// j0rpi_GameDB
//
// File: admin/platforms.php
// Purpose: Manage platforms
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
// For when we edit/delete posts
$status = "";
// --------------------------------------------------------
//
// Define Database
//
// --------------------------------------------------------
$db = new SQLite3('../games.db');
// --------------------------------------------------------
//
// Setup POST Variables
//
// --------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $stmt = $db->prepare('DELETE FROM platforms WHERE id = :id');
        $stmt->bindValue(':id', $_POST['delete_id'], SQLITE3_INTEGER);
        $stmt->execute();
		$status = $_POST['name'] . " (" . $_POST['short_prefix'] . ") was deleted from the database.";
		echo "<div class='errorbar' style='background-color: darkgreen;'><span style='margin-bottom: 2px'>✔️ " . $status . "</div>";
    } elseif (isset($_POST['update_id'])) {
        $stmt = $db->prepare('UPDATE platforms SET name = :name, short_prefix = :short_prefix WHERE id = :id');
        $stmt->bindValue(':id', $_POST['update_id'], SQLITE3_INTEGER);
        $stmt->bindValue(':name', $_POST['name'], SQLITE3_TEXT);
		$stmt->bindValue(':short_prefix', $_POST['short_prefix'], SQLITE3_TEXT);
		$status = $_POST['name'] . " (" . $_POST['short_prefix'] . ") was updated.";
		echo "<div class='errorbar' style='background-color: darkgreen;'><span style='margin-bottom: 2px'>✔️ " . $status . "</div>";
        $stmt->execute();
    } elseif (isset($_POST['add_plat'])) {
		$stmt = $db->prepare('INSERT INTO platforms (name, short_prefix) VALUES (:name, :short_prefix)');
		$stmt->bindValue(':name', $_POST['name_add'], SQLITE3_TEXT);
		$stmt->bindValue(':short_prefix', $_POST['short_prefix_add'], SQLITE3_TEXT);
		$status = $_POST['name_add'] . " was added to the database.";
		echo "<div class='errorbar' style='background-color: darkgreen;'><span style='margin-bottom: 2px'>✔️ " . $status . "</div>";
		$stmt->execute();
	} else {
		$status = "There was an error trying to delete/update selected post.";
		echo "<div class='errorbar' style='background-color: darkred;'><span style='margin-bottom: 2px'>⛔️ " . $status . "</div>";
	}
}
// --------------------------------------------------------
//
// Run Query, And Select ALL From 'platforms' Table
//
// --------------------------------------------------------
$result = $db->query('SELECT * FROM platforms');
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
	echo "<br><br><div class='bg-text' style='margin-top: 50px;'>";
	$keygen = true;
}
else
{
	echo "<br><br><div class='bg-text' style='margin-top: 50px;'>";
}
?>
<?php
// --------------------------------------------------------
//
// Platform List
//
// --------------------------------------------------------
?>
    <h1>Admin Dashboard <span style="float:right; font-size: 16px; font-weight: normal;">Logged in As <strong><?php echo $_SESSION['admin_username']; ?></strong></span><br><span style="float:left; font-size: 12px; font-weight: normal;"></span><span style="float:right; font-size: 12px; font-weight: normal;"><a href="password.php" style="">Change Password</a> | <a href="logout.php" style="">Logout</a></h1><br>
    <?php
	// --------------------------------------------------------
	//
	// The little info box to the right of all posts
	//
	// --------------------------------------------------------
	?>
	<div class="info-container">
		<span>How to</span>
		<div class="thindivider"></div>
		<p>✏️ Update Platform</p>
		<p>❌ Delete Platform</p>
		<p style="margin-top: 25px;"></p>
		<span>Add New Platform</span>
		<div class="thindivider"></div>
		<form method="POST">
            <label for="name_add">Platform Name</label>
            <input type="text" id="name_add" name="name_add" required>
			<label for="short_prefix_add">Short Prefix</label>
			<input type="text" id="short_prefix_add" name="short_prefix_add" required>
            <button type="submit" name="add_plat">✔️ Add Platform</button>
        </form>
		</p>
		<p>Note: When adding new platforms, GameDB will try to look for each
		platforms icon by its short prefix that is specified.</p>
	</div>
	<div class="form-container">
	<?php
	// --------------------------------------------------------
	//
	// Setup Table Headers
	//
	// --------------------------------------------------------
	?>
        <span class="button-row-text"><a href="index.php">Admin Dashboard</a> > Manage Platforms</span><br>
		<div class="thindivider"></div>
		<table>
            <thead>
            <tr style="font-size: 14px;">
				<th style="text-align: center; width: 10px">ID</th>
				<th style="text-align: center; width: 32px">Icon</th>
				<th style="text-align: center; width: 125px">Name</th>
				<th style="text-align: center; width: 125px">Short Prefix</th>
				<th style="text-align: center; width: 125px">Actions</th>
                </tr>
            </thead>
            <tbody>
				<?php
				// --------------------------------------------------------
				//
				// Connect To Database And Populate The Table
				//
				// --------------------------------------------------------
				?>
                <?php while ($platform = $result->fetchArray(SQLITE3_ASSOC)): ?>
                <tr>
                    <form method="POST">
						<td style="text-align: center;" name="id"><?= $platform['id'] ?></td>
						<?php
						// --------------------------------------------------------
						//
						// If platform does not have an existing platform icon, display a default one
						//
						// --------------------------------------------------------
						?>
						<?php 
						
						if (file_exists("../styles/" . $style . "/img/platform_icons/" . $platform['short_prefix'] . ".png")) {
							echo "<td style='text-align: center;' name='platform_icon'><img style='width: 32px;' src='../styles/" . $style . "/img/platform_icons/" . $platform['short_prefix'] . ".png' /></td>";
						}
						else {
							echo "<td style='text-align: center;' name='platform_icon'><img style='width: 32px;' src='../styles/" . $style . "/img/platform_icons/unknown.png' /></td>";
						}
						?>
						<td><center><input type="text" name="name" value="<?= $platform['name'] ?>" style="width: 200px;"></center></td>
						<td style="text-align: center;"><center><input type="text" name="short_prefix" value="<?= $platform['short_prefix'] ?>" style="width: 125px;"></center></td>
					<?php
					// --------------------------------------------------------
					//
					// Action Buttons
					//
					// --------------------------------------------------------
					?>
                        <td>
						<center>
                            <button type="submit" style="vertical-align: middle; margin-bottom: 8px;" name="update_id" title="Update this game" value="<?= $platform['id'] ?>">✏️</button>
                            <button type="submit" style="background-color: rgba(255,0,0,0.6); vertical-align: middle; margin-bottom: 8px;" title="Delete" name="delete_id" value="<?= $platform['id'] ?>">❌</button>
                        </td>
						</center>
                    </form>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
		<?php
		// --------------------------------------------------------
		//
		// If Database Is Empty, Tell The User
		//
		// --------------------------------------------------------
		?>
		<?php 
		$count = $db->querySingle("SELECT COUNT(*) as total from platforms");
		if ($count < 1) {
			echo "<center>No platforms were found in the database.</center>";
		}
		?>
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
