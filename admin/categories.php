<?php
// --------------------------------------------------------
//
// j0rpi_GameDB
//
// File: admin/categories.php
// Purpose: Manage categories
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
        $stmt = $db->prepare('DELETE FROM categories WHERE id = :id');
        $stmt->bindValue(':id', $_POST['delete_id'], SQLITE3_INTEGER);
        $stmt->execute();
		$status = $_POST['cat_name'] . " was deleted from the database.";
		echo "<div class='errorbar' style='background-color: darkgreen;'><span style='margin-bottom: 2px'>✔️ " . $status . "</div>";
    } elseif (isset($_POST['update_id'])) {
        $stmt = $db->prepare('UPDATE categories SET cat_name = :cat_name, odd_genre = :odd_genre WHERE id = :id');
        $stmt->bindValue(':id', $_POST['update_id'], SQLITE3_INTEGER);
        $stmt->bindValue(':cat_name', $_POST['cat_name'], SQLITE3_TEXT);
		$stmt->bindValue(':odd_genre', $_POST['odd_genre'], SQLITE3_INTEGER);
        $status = $_POST['cat_name'] . " was updated.";
		echo "<div class='errorbar' style='background-color: darkgreen;'><span style='margin-bottom: 2px'>✔️ " . $status . "</div>";
        $stmt->execute();
    } elseif (isset($_POST['add_cat'])) {
		$stmt = $db->prepare('INSERT INTO categories (cat_name, odd_genre) VALUES (:cat_name, :odd_genre)');
		$stmt->bindValue(':cat_name', $_POST['cat_name_add'], SQLITE3_TEXT);
		$stmt->bindValue(':odd_genre', $_POST['odd_genre_add'], SQLITE3_INTEGER);
		$status = $_POST['cat_name_add'] . " was added to the database.";
		echo "<div class='errorbar' style='background-color: darkgreen;'><span style='margin-bottom: 2px'>✔️ " . $status . "</div>";
		$stmt->execute();
	} else {
		$status = "There was an error trying to delete/update selected post.";
		echo "<div class='errorbar' style='background-color: darkred;'><span style='margin-bottom: 2px'>⛔️ " . $status . "</div>";
	}
}
// --------------------------------------------------------
//
// Run Query, And Select ALL From 'categories' Table
//
// --------------------------------------------------------
$result = $db->query('SELECT * FROM categories');
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
	echo "<br><br><div class='bg-text' style='margin-top: 50px;'>";
}
?>
<?php
// --------------------------------------------------------
//
// Category List
//
// --------------------------------------------------------
?>
    <h1>Admin Dashboard<span style="float:right; font-size: 16px; font-weight: normal;">Logged in As <strong><?php echo $_SESSION['admin_username']; ?></strong></span><br><span style="float:left; font-size: 12px; font-weight: normal;"></span><span style="float:right; font-size: 12px; font-weight: normal;"><a href="password.php" style="">Change Password</a> | <a href="logout.php" style="">Logout</a></span></h1><br>
    
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
		<p>✏️  Update Category</p>
		<p>❌  Delete Category</p>
		<p style="margin-top: 25px;"></p>
		<span>Add New Category</span>
		<div class="thindivider"></div>
		<form method="POST">
            <label for="cat_name_add">Category Name</label>
            <input type="text" id="cat_name_add" name="cat_name_add" required>
			<label for="odd_genre_add">Odd?</label>
			<input type="text" id="odd_genre_add" name="odd_genre_add" placeholder="Must be 0 or 1 ..." required>
            <button type="submit" name="add_cat">✔️ Add Category</button>
        </form>
		</p>
		<p>Categories marked as <strong>ODD</strong> will be listed under <strong>Others</strong> in the search filters.</p>
	</div>
	
	<?php
	// --------------------------------------------------------
	//
	// Main form container. 
	//
	// --------------------------------------------------------
	?>
	<div class="form-container">
	<?php
	// --------------------------------------------------------
	//
	// Setup Table Headers
	//
	// --------------------------------------------------------
	?>
        <span class="button-row-text"><a href="index.php">Admin Dashboard</a> > Manage Genres</span><br>
		<div class="thindivider"></div>
		<table>
            <thead>
            <tr style="font-size: 14px;">
				<th style="text-align: center; width: 10px">ID</th>
				<th style="text-align: center; width: 50px">Name</th>
				<th style="text-align: center; width: 5px">Is Odd</th>
				<th style="text-align: center; width: 100px">Actions</th>
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
                <?php while ($cat = $result->fetchArray(SQLITE3_ASSOC)): ?>
                <tr>
                    <form method="POST">
						<td style="text-align: center;" name="id"><?= $cat['id'] ?></td>
						<td><center><input type="text" name="cat_name" value="<?= $cat['cat_name'] ?>" style="width: 250px;"></center></td>
						<td style="text-align: center;"><input type="text" name="odd_genre" value="<?= $cat['odd_genre'] ?>"  style="width: 30px;"></td>
					<?php
					// --------------------------------------------------------
					//
					// Action Buttons
					//
					// --------------------------------------------------------
					?>
                        <td>
						<center>
                            <button type="submit" style="vertical-align: middle; margin-bottom: 8px;" name="update_id" title="Update this game" value="<?= $cat['id'] ?>">✏️</button>
                            <button type="submit" style="background-color: rgba(255,0,0,0.6); vertical-align: middle; margin-bottom: 8px;" title="Delete" name="delete_id" value="<?= $cat['id'] ?>">❌</button>
                        </center>
						</td>
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
		$count = $db->querySingle("SELECT COUNT(*) as total from categories");
		if ($count < 1) {
			echo "<center>No categories were found in the database.</center>";
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
