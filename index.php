<?php
// --------------------------------------------------------
//
// j0rpi_GameDB
//
// File: index.php
// Purpose: Display all games in database
//
// --------------------------------------------------------
//
// Redirect to install script if games.db does not exist.
//
// --------------------------------------------------------
if(!file_exists('games.db'))
{
	header('Location: install');
    exit;
}

session_start();

// --------------------------------------------------------
//
// Define Config And Skin Config
//
// --------------------------------------------------------
include('version.php');
include('include/config.php');
include('include/functions.php');

// --------------------------------------------------------
//
// Year Selector Min/Max
//
// --------------------------------------------------------
$yearss = range((int)getConfigVarInt('minSelectableYear'), strftime("%Y", time()));
// --------------------------------------------------------
//
// Define Database
//
// --------------------------------------------------------
$db = new SQLite3('games.db');
// --------------------------------------------------------
//
// Get current page and search/filter query
//
// --------------------------------------------------------
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$genre = isset($_GET['genre']) ? $_GET['genre'] : '';
$completed = isset($_GET['completed']) ? $_GET['completed'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';
$speedrun = isset($_GET['speedrun']) ? $_GET['speedrun'] : '';
$platform = isset($_GET['platform']) ? $_GET['platform'] : '';
// --------------------------------------------------------
//
// Set total number of items per page
//
// --------------------------------------------------------
$per_page = (int)getConfigVarInt('listMax');
$offset = ($page - 1) * $per_page;
// --------------------------------------------------------
//
// Query based on search/filters
//
// --------------------------------------------------------
$query = 'SELECT * FROM games WHERE title LIKE :search';
$params = [':search' => '%' . $search . '%'];

if ($genre !== '') {
    $query .= ' AND genre = :genre';
    $params[':genre'] = $genre;
}

if ($completed !== '') {
    $query .= ' AND completed = :completed';
    $params[':completed'] = $completed;
}

if ($speedrun !== '') {
    $query .= ' AND speedrun = :speedrun';
    $params[':speedrun'] = $speedrun;
}

if ($year !== '') {
	$query .= ' AND year = :year';
	$params[':year'] = $year;
}

if ($platform !== '') {
	$query .= ' AND platform = :platform';
	$params[':platform'] = $platform;
}


$query .= ' LIMIT :limit OFFSET :offset';
$params[':limit'] = $per_page;
$params[':offset'] = $offset;

$stmt = $db->prepare($query);

foreach ($params as $key => $value) {
    if (is_int($value)) {
        $stmt->bindValue($key, $value, SQLITE3_INTEGER);
    } else {
        $stmt->bindValue($key, $value, SQLITE3_TEXT);
    }
}

$result = $stmt->execute();
// --------------------------------------------------------
//
// Count query based on filters
//
// --------------------------------------------------------
$count_query = 'SELECT COUNT(*) as count FROM games WHERE title LIKE :search';
$count_params = [':search' => '%' . $search . '%'];

if ($genre !== '') {
    $count_query .= ' AND genre = :genre';
    $count_params[':genre'] = $genre;
}

if ($completed !== '') {
    $count_query .= ' AND completed = :completed';
    $count_params[':completed'] = $completed;
}

if ($speedrun !== '') {
    $count_query .= ' AND speedrun = :speedrun';
    $count_params[':speedrun'] = $speedrun;
}

if ($year !== '') {
	$count_query .= ' AND year = :year';
	$count_params[':year'] = $year;
}

if ($platform !== '') {
	$count_query .= ' AND platform = :platform';
	$count_params[':platform'] = $platform;
}

$count_stmt = $db->prepare($count_query);

foreach ($count_params as $key => $value) {
    if (is_int($value)) {
        $count_stmt->bindValue($key, $value, SQLITE3_INTEGER);
    } else {
        $count_stmt->bindValue($key, $value, SQLITE3_TEXT);
    }
}
$count_result = $count_stmt->execute();
$total_count = $count_result->fetchArray(SQLITE3_ASSOC)['count'];
?>
<?php
// --------------------------------------------------------
//
// Main HTML
//
// --------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $headerTitle; ?> :: Index</title>
	<link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="styles/<?php getConfigVar('style') ?>/style.main.css">
	<link rel="stylesheet" href="styles/<?php getConfigVar('style') ?>/style.ratings.css">
	<script>
<?php

// --------------------------------------------------------
//
// Search games
//
// --------------------------------------------------------
?>
    function searchGames() {
    const query = document.getElementById('search').value;
    const genre = document.getElementById('genre').value;
    const completed = document.getElementById('completed').value;
    const speedrun = document.getElementById('speedrun').value;
    const year = document.getElementById('year').value;
	const rating = document.getElementById('rating').value;
	const platform = document.getElementById('platform').value;

    fetch(`search.php?q=${query}&genre=${genre}&completed=${completed}&speedrun=${speedrun}&year=${year}&rating=${rating}&platform=${platform}`)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('tbody');
            tableBody.innerHTML = '';
            data.games.forEach(game => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><img src="${game.cover}" alt="${game.title} cover" style="width: 100px;"></td>
                    <td style="text-align: center">${game.title}</td>
                    <td style="text-align: center">${game.genre}</td>
                    <td style="text-align: center">${game.year}</td>
				<td style="text-align: center"><img title="${game.platform}" class="platformicon" src="styles/<?php getConfigVar("style") ?>/img/platform_icons/${game.platform}.png" /></td>
                    <td style="text-align: center">${game.desc}</td>
                    <td class="rating-column">
						<div class="rating-segment" data-rating="${game.rating}">
							<span class="rating-text">${game.rating}</span>
						</div>
					</td>
                    <td style="text-align: center;">${game.completed ? 'Yes' : 'No'}</td>
                    <td style="text-align: center;">${game.speedrun ? 'Yes' : 'No'}</td>
                    <td><a href="${game.vod}"><a href="${game.vod}">WATCH</a></td>
                `;
                row.onclick = () => openModal(game);
                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error('Error:', error));
}
</script>

<?php
// --------------------------------------------------------
//
// Fetch inner HTML elements for modal
//
// --------------------------------------------------------
?>
	<script>
	        function openModal(game) {
            document.getElementById('modalTitle').innerText = game.title;
            document.getElementById('modalRating').innerText = game.rating;
            document.getElementById('modalVOD').href = game.vod;
            document.getElementById('modalCover').src = game.cover;
            document.getElementById('modalGenre').innerText = game.genre;
            document.getElementById('modalCompleted').innerText = game.completed ? 'Yes' : 'No';
            document.getElementById('modalSpeedrun').innerText = game.speedrun ? 'Yes' : 'No';
			document.getElementById('modalDesc').innerText = game.desc;
			document.getElementById('modalPlatform').innerText = game.platform;
			document.title = "GameDB :: " + game.title + " (" + game.platform + ")";
            
            document.getElementById('gameModal').style.display = 'flex';
            document.body.classList.add('blur');
        }

        function closeModal() {
            document.getElementById('gameModal').style.display = 'none';
            document.body.classList.remove('blur');
			document.title = "GameDB :: Index";
        }
	</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Select all elements with class starting with "rating-segment-"
    document.querySelectorAll('[class^="rating-segment-"]').forEach(function (circle) {
        const rating = parseInt(circle.getAttribute('data-rating')) || 1;
        const normalizedRating = Math.min(Math.max(rating, 1), 10);
        circle.style.setProperty('--rating', normalizedRating);
    });
});
</script>



</script>
</head>
<body>
<?php
// --------------------------------------------------------
//
// Header
//
// --------------------------------------------------------
?>
<div class="bg-text">
<div class="logodiv">
    <img src="styles/<?php getConfigVar('style')?>/img/logo.png" /> 
</div>
<?php 
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) 
{
    echo '<div class="form-container"><a href="admin/index.php" class="add-game" style="float:right">Admin Login</a>';
} 
else 
{
    echo '<div class="form-container"><a href="admin/index.php" class="add-game" style="line-height: 18px; float:right">💎 Logged in as ' . $_SESSION['admin_username'] . '</a>';
} 
?>

<?php
// --------------------------------------------------------
//
// Searchbox and filters
//
// --------------------------------------------------------
?>
<div class="search-box" style="margin-right: -20px;">
    <input type="text" id="search" style="width: 350px;" oninput="searchGames()" placeholder="Search games...">
</div>
<div class="filters" style="width: 700px">

<?php
// --------------------------------------------------------
//
// Fetch categories from database and sort them
//
// --------------------------------------------------------
?>
    <select id="genre" onchange="searchGames()" style="width: 175px;">
        <option value="">🎲 Genre</option>
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
		// Query the non-odd genres
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
		// Query the odd genres
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
// Year Filter
//
// --------------------------------------------------------
?>
	<select id="year" name="year" onchange="searchGames()" style="width: 100px;">
		<option value="">📆 Year?</option>
		<?php foreach ($yearss as $year) : ?>
			<option value="<?php echo $year; ?>"><?php echo $year; ?></option>
		<?php endforeach; ?>
	</select>
<?php

// --------------------------------------------------------
//
// Fetch platform/systems from database
//
// --------------------------------------------------------
?>
	<select id="platform" name="platform" onchange="searchGames()" style="width: 200px;">
	<option value="">🕹️ Platform</option>
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
// Rating Filter
//
// --------------------------------------------------------
?>
	<select id="rating" onchange="searchGames()">
		<option value="">⭐ Rating</option>
		<option value="1">1</option>
		<option value="2">2</option>
		<option value="3">3</option>
		<option value="4">4</option>
		<option value="5">5</option>
		<option value="6">6</option>
		<option value="7">7</option>
		<option value="8">8</option>
		<option value="9">9</option>
		<option value="10">10</option>
	</select>

<?php

// --------------------------------------------------------
//
// Completion Filter
//
// --------------------------------------------------------
?>
    <select id="completed" onchange="searchGames()" style="width: 135px;">
        <option value="">✔️ Completed?</option>
        <option value="1">Yes</option>
        <option value="0">No</option>
    </select>
<?php

// --------------------------------------------------------
//
// Speedrun Filter
//
// --------------------------------------------------------
?>
    <select id="speedrun" onchange="searchGames()" style="width: 125px;">
        <option value="">🏃🏻 Speedrun?</option>
        <option value="1">Yes</option>
        <option value="0">No</option>
    </select>	
</div> 
<br>
<?php
// --------------------------------------------------------
//
// Game List
//
// --------------------------------------------------------
?>
<table>
    <thead>
	<?php
	// --------------------------------------------------------
	//
	// Title headers for table
	//
	// --------------------------------------------------------
	?>
        <tr>
            <th style="text-align: center; border-right: 1px solid #0080ff; width: 100px">#</th>
            <th style="text-align: center; border-right: 1px solid #0080ff; width: 300px">Title</th>
            <th style="text-align: center; border-right: 1px solid #0080ff; width: 100px">Genre</th>
            <th style="text-align: center; border-right: 1px solid #0080ff; width: 100px">Year</th>
			<th style="text-align: center; border-right: 1px solid #0080ff; width: 200px">Platform</th>
            <th style="text-align: center; border-right: 1px solid #0080ff; width: 500px">Review</th>
            <th style="text-align: center; border-right: 1px solid #0080ff; width: 50px">Rating</th>
            <th style="text-align: center; border-right: 1px solid #0080ff; width: 100px">Completed</th>
            <th style="text-align: center; border-right: 1px solid #0080ff; width: 100px">Speedrun</th>
            <th>VOD</th>
        </tr>
    </thead>
	<?php
	// --------------------------------------------------------
	//
	// Rows
	//
	// --------------------------------------------------------
	?>
    <tbody>
        <?php while ($game = $result->fetchArray(SQLITE3_ASSOC)): ?>
        <tr onclick="openModal(<?= htmlspecialchars(json_encode($game)) ?>)">
            <td><img src="<?= $game['cover'] ?>" alt="<?= $game['title'] ?> cover" style="width: 100px;"></td>
            <td><?= $game['title'] ?></td>
            <td style="text-align: center"><?= $game['genre'] ?></td>
			<td style="text-align: center"><?= $game['year'] ?></td>
			<td style="text-align: center">
			<img 
				title="<?= getPlatformName($game['platform']) ?>"  class="platformicon" src="styles/<?= getConfigVar('style') ?>/img/platform_icons/<?= $game['platform'] ?>.png">
			</td>
            <td><?= $game['desc'] ?></td>
            <td class="rating-column">
				<div class="rating-segment-<?= $game['rating']; ?>" data-rating="<?= $game['rating']; ?>">
					<span class="rating-text"><?= $game['rating']; ?></span>
				</div>
			</td>
            <td style="text-align: center;"><?= $game['completed'] ? 'Yes' : 'No' ?></td>
            <td style="text-align: center;"><?= $game['speedrun'] ? 'Yes' : 'No' ?></td>
            <td><a href="<?= $game['vod'] ?>">WATCH</a></td>
        </tr>
        <?php endwhile; ?>
		<?php
		// --------------------------------------------------------
		//
		// No Results 
		//
		// --------------------------------------------------------
		?>
		<?php $count = $db->querySingle("SELECT COUNT(*) as total from games"); ?>
        <?php if ($count == 0): ?>
            <tr><td colspan="9"><br><center>😔 No games found.</center><br></td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php
// --------------------------------------------------------
//
// Pagination
//
// --------------------------------------------------------
?>
<div class="pagination">
    <?php for ($i = 1; $i <= ceil($total_count / (int)$per_page); $i++): ?>
         <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
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
</div>
</div>
<?php
// --------------------------------------------------------
//
// Modal
//
// --------------------------------------------------------
?>
<div id="gameModal" class="modal">
    <div class="modal-content">
		<span class="modal-close"><span style="float:left" id="modalTitle"></span><span class="modalCloseButton" onclick="closeModal()">&times;</span></span>
			<table style="border-bottom: 0px solid black;"><thead>
				<tr>
					<td style="width: 600px; border-bottom: none !important;">
					<img id="modalCover" src="" alt="Cover" style="width:128px; margin-top: 46px;">
					<h2 id="modalTitle"></h2>
					<p><strong>Genre:</strong> <span id="modalGenre" style="font-weight: 300;"></span>
					<p><strong>Platform:</strong> <span id="modalPlatform"></span></p>
					<p><strong>Rating:</strong> <span id="modalRating"></span></p>
					<p><strong>Completed:</strong> <span id="modalCompleted"></span></p>
					<p><strong>Speedrun:</strong> <span id="modalSpeedrun"></span></p>
					<p><strong>VOD:</strong> <a id="modalVOD" href="" target="_blank">Watch</a></p>
					</td>
					<td style="border-bottom: none !important;">
					<center><h2>Review</h2></center>
					<textarea id="modalDesc" disabled></textarea>
					</td>
				</tr></thead>
			</table>
			<center>This game was added on <strong>2024-06-30</strong>
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