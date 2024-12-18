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
        $stmt = $db->prepare('DELETE FROM games WHERE id = :id');
        $stmt->bindValue(':id', $_POST['delete_id'], SQLITE3_INTEGER);
        $stmt->execute();
		$status = $_POST['title'] . " (" . $_POST['platform'] . ") was successfully deleted from the database!";
		echo "<div class='errorbar' style='background-color: darkgreen;'><span style='margin-bottom: 2px'>✔️ " . $status . "</div>";
    } elseif (isset($_POST['update_id'])) {
        $stmt = $db->prepare('UPDATE games SET title = :title, year = :year, desc = :desc, rating = :rating, vod = :vod, cover = :cover, genre = :genre, completed = :completed, speedrun = :speedrun, platform = :platform WHERE id = :id');
        $stmt->bindValue(':id', $_POST['update_id'], SQLITE3_INTEGER);
        $stmt->bindValue(':title', $_POST['title'], SQLITE3_TEXT);
		$stmt->bindValue(':year', $_POST['year'], SQLITE3_TEXT);
		$stmt->bindValue(':desc', $_POST['desc'], SQLITE3_TEXT);
        $stmt->bindValue(':rating', $_POST['rating'], SQLITE3_FLOAT);
        $stmt->bindValue(':vod', $_POST['vod'], SQLITE3_TEXT);
        $stmt->bindValue(':cover', $_POST['cover'], SQLITE3_TEXT);
        $stmt->bindValue(':genre', $_POST['genre'], SQLITE3_TEXT);
        $stmt->bindValue(':completed', $_POST['completed'], SQLITE3_INTEGER);
        $stmt->bindValue(':speedrun', $_POST['speedrun'], SQLITE3_INTEGER);
		$stmt->bindValue(':platform', $_POST['platform'], SQLITE3_TEXT);
		$stmt->execute();
		$status = $_POST['title'] . " (" . $_POST['platform'] . ") was updated successfully!";
		echo "<div class='errorbar' style='background-color: darkgreen;'><span style='margin-bottom: 2px'>✔️ " . $status . "</div>";
    }else {
		$status = "There was an error trying to modify " . $_POST['title'] . " (" . $_POST['platform'] . ") - Please try again.";
		echo "<div class='errorbar' style='background-color: darkred;'><span style='margin-bottom: 2px'>⛔️ " . $status . "</div>";
	}
}
// --------------------------------------------------------
//
// Run Query, And Select ALL From 'games' Table
//
// --------------------------------------------------------
$result = $db->query('SELECT * FROM games');


?>
<?php $years = range(1900, strftime("%Y", time())); ?>
<?php $ratings = range(1, 10); ?>
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
	echo "<div class='bg-text' style='margin-top: 50px; width: 95%;'>";
	$keygen = true;
}
else
{
	echo "<br><br><div class='bg-text' style='margin-top: 50px; width: 95%;'>";
}
?><br>
<?php
// --------------------------------------------------------
//
// Game List
//
// --------------------------------------------------------
?>
    <h1>Admin Dashboard<span style="float:right; font-size: 16px; font-weight: normal;">Logged in As <strong><?php echo $_SESSION['admin_username']; ?></strong></span><br><span style="float:left; font-size: 12px; font-weight: normal;"></span><span style="float:right; font-size: 12px; font-weight: normal;"><a href="password.php" style="">Change Password</a> | <a href="logout.php" style="">Logout</a></h1><br>
    <div class="form-container" style='width: 100%;'>
	<?php
	// --------------------------------------------------------
	//
	// Setup Table Headers
	//
	// --------------------------------------------------------
	?>
        <table>
		<span class="button-row-text"><a href="index.php">Dashboard</a> > Manage Games</span><div class="button-row"><a href="add.php" class="add-game" style="margin-left: 25x;">🕹️ Add New Game</a></div>
            <thead>
                <tr style="font-size: 14px;">
					<th style="text-align: center; border-right: 1px solid #0080ff; width: 100px">#</th>
            <th style="text-align: center; border-right: 1px solid #0080ff; width: 100px">Title</th>
            <th style="text-align: center; border-right: 1px solid #0080ff; width: 100px">Genre</th>
            <th style="text-align: center; border-right: 1px solid #0080ff; width: 100px">Year</th>
			<th style="text-align: center; border-right: 1px solid #0080ff; width: 100px">Platform</th>
            <th style="text-align: center; border-right: 1px solid #0080ff; width: 100px">Review</th>
            <th style="text-align: center; border-right: 1px solid #0080ff; width: 100px">Rating</th>
			<th style="text-align: center; border-right: 1px solid #0080ff; width: 100px">VOD</th>
			<th style="text-align: center; border-right: 1px solid #0080ff; width: 100px">Cover</th>
            <th style="text-align: center; border-right: 1px solid #0080ff; width: 100px">Completed</th>
            <th style="text-align: center; border-right: 1px solid #0080ff; width: 100px">Speedrun</th>
            <th style="text-align: center; border-right: 0x solid #0080ff; width: 100px">Actions</th>
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
                <?php while ($game = $result->fetchArray(SQLITE3_ASSOC)): ?>
                <tr>
                    <form method="POST">
						<td><img src="<?= $game['cover'] ?>" style="width: 100px;" /></td>
                        <td><input type="text" name="title" value="<?= $game['title'] ?>"></td>
						<td>
					<?php
					// --------------------------------------------------------
					//
					// Fetch Genres From Categories Database Table
					//
					// --------------------------------------------------------
					?>
					<select name="genre">
							<?php 
							// Querystring for non-odd genres
							$cat_query = 'SELECT cat_name FROM categories WHERE odd_genre = 0';
		
							// Querystring for the ODD genres
							$cat_query_odd = 'SELECT cat_name FROM categories WHERE odd_genre = 1';
		
							// Query the db for non-odd genres
							$cat_stmt = $db->prepare($cat_query);
							$cat_results = $cat_stmt->execute();
		
							// Copy pasta same, but use ODD query string
							$cat_stmt_odd = $db->prepare($cat_query_odd);
							$cat_results_odd = $cat_stmt_odd->execute();
							?>
							<?php
							// --------------------------------------------------------
							//
							// Query Genres Not Flagged As 'Odd'
							//
							// --------------------------------------------------------
							?>
							<?php while ($option = $cat_results->fetchArray(SQLITE3_ASSOC)): ?>
								<option><?php echo $option['cat_name']; ?></option>
							<?php endwhile; ?>
								<option disabled>Other Genres ...</option>
							<?php
							// --------------------------------------------------------
							//
							// Now Query The Odd Genres
							//
							// --------------------------------------------------------
							?>
							<?php while ($option_odd = $cat_results_odd->fetchArray(SQLITE3_ASSOC)): ?>
								<option><?php echo $option_odd['cat_name']; ?></option>
							<?php endwhile; ?>	
					</select>
					<?php
					// --------------------------------------------------------
					//
					// Year Selection Box
					//
					// --------------------------------------------------------
					?>
						</td>
						<td>
							<select name="year">
									<option value="<?= $game['year'] ?>"><?= $game['year'] ?></option>
								<?php foreach($years as $year) : ?>
									<option value="<?php echo $year; ?>"><?php echo $year; ?></option>
								<?php endforeach; ?>
							</select>
						<td>
					<?php
					// --------------------------------------------------------
					//
					// Fetch Platforms And Popuplate Selection Box
					//
					// --------------------------------------------------------
					?>
							<select id="platform" name="platform" onchange="searchGames()" style="width: 75px;">
								<option value="<?= $game['platform'] ?>"><?= $game['platform'] ?></option>
								<?php 
								// Querystring for platforms
								$platform_query = 'SELECT * FROM platforms ORDER BY name';
		
								// Setup connection
								$platform_stmt = $db->prepare($platform_query);
								$platform_results = $platform_stmt->execute();
								?>
								<?php
								// --------------------------------------------------------
								//
								// Query all platforms
								//
								// --------------------------------------------------------
								?>
								<?php while ($platform = $platform_results->fetchArray(SQLITE3_ASSOC)): ?>
									<option value="<?php echo $platform['short_prefix']; ?>"><?php echo $platform['name']; ?></option>
								<?php endwhile; ?>
							</select>
					<?php
					// --------------------------------------------------------
					//
					// Fetch Description
					//
					// --------------------------------------------------------
					?>
						</td>
						</td>
						<td><textarea name="desc" style="width: 200px;"><?= $game['desc'] ?></textarea></td>
                        <td>
					<?php
					// --------------------------------------------------------
					//
					// Rating Selection Box
					//
					// --------------------------------------------------------
					?>
						<select id="rating" name="rating">
							<?php foreach($ratings as $rating) : ?>
								<option value="<?php echo $rating; ?>"><?php echo $rating; ?></option>
							<?php endforeach; ?>
						</select>
						</td>
					<?php
					// --------------------------------------------------------
					//
					// VOD Link
					//
					// --------------------------------------------------------
					?>
                        <td><input style="width: 75px" type="text" name="vod" value="<?= $game['vod'] ?>"></td>
					<?php
					// --------------------------------------------------------
					//
					// Cover
					//
					// --------------------------------------------------------
					?>
                        <td><input style="width: 75px" type="text" name="cover" value="<?= $game['cover'] ?>"></td>
					<?php
					// --------------------------------------------------------
					//
					// Fetch Completion Status
					//
					// --------------------------------------------------------
					?>
						<td>
                            <select name="completed">
                                <option value="0" <?= $game['completed'] ? '' : 'selected' ?>>No</option>
                                <option value="1" <?= $game['completed'] ? 'selected' : '' ?>>Yes</option>
                            </select>
                        </td>
                        <td>
					<?php
					// --------------------------------------------------------
					//
					// Fetch Speedrun Status
					//
					// --------------------------------------------------------
					?>
                            <select name="speedrun">
                                <option value="0" <?= $game['speedrun'] ? '' : 'selected' ?>>No</option>
                                <option value="1" <?= $game['speedrun'] ? 'selected' : '' ?>>Yes</option>
                            </select>
                        </td>
					<?php
					// --------------------------------------------------------
					//
					// Action Buttons
					//
					// --------------------------------------------------------
					?>
                        <td>
                            <button type="submit" style="vertical-align: middle; margin-bottom: 8px;" name="update_id" title="Update this game" value="<?= $game['id'] ?>">✏️</button>
                            <button type="submit" style="background-color: rgba(255,0,0,0.6); vertical-align: middle; margin-bottom: 8px;" title="Delete" name="delete_id" value="<?= $game['id'] ?>">❌</button>
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
		$count = $db->querySingle("SELECT COUNT(*) as total from games");
		if ($count < 1) {
			echo "<center>No games were found in the database.</center>";
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
