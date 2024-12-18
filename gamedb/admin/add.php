<?php
// --------------------------------------------------------
//
// j0rpi_GameDB
//
// File: admin/add.php
// Purpose: Simple form to add new game to database
//
// --------------------------------------------------------
session_start();

// Define db
$db = new SQLite3('../games.db');

// --------------------------------------------------------
//
// Define Config And Skin Config
//
// --------------------------------------------------------
include('../include/config.php');
include('../include/functions.php');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: ../admin/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new SQLite3('../games.db');
    $stmt = $db->prepare('INSERT INTO games (title, year, desc, rating, vod, cover, genre, completed, speedrun, platform) VALUES (:title, :year, :desc, :rating, :vod, :cover, :genre, :completed, :speedrun, :platform)');
    $stmt->bindValue(':title', $_POST['title'], SQLITE3_TEXT);
    $stmt->bindValue(':rating', $_POST['rating'], SQLITE3_FLOAT);
	$stmt->bindValue(':year', $_POST['year'], SQLITE3_TEXT);
	$stmt->bindValue(':desc', $_POST['desc'], SQLITE3_TEXT);
    $stmt->bindValue(':vod', $_POST['vod'], SQLITE3_TEXT);
    $stmt->bindValue(':cover', $_POST['cover'], SQLITE3_TEXT);
    $stmt->bindValue(':genre', $_POST['genre'], SQLITE3_TEXT);
    $stmt->bindValue(':completed', $_POST['completed'], SQLITE3_INTEGER);
    $stmt->bindValue(':speedrun', $_POST['speedrun'], SQLITE3_INTEGER);
	$stmt->bindValue(':platform', $_POST['platform'], SQLITE3_TEXT);
    $stmt->execute();

    header('Location: ../');
    exit;
}
?>
<?php $years = range(1960, strftime("%Y", time())); ?>
<?php $ratings = range(1, 10); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Game</title>
    <link rel="stylesheet" href="../styles/<?php getConfigVar('style') ?>/style.catplat.css">
</head>
<body>
<center>
<div class="bg-text" style=" padding-top: 50px">
    <div class="form-container" style="text-align: left;;">
       
        <form method="POST">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required>
			
			<label for="genre">Genre</label>
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
            
			<label for="year">Year</label>
			<select id="year" name="year">
            <?php foreach($years as $year) : ?>
			<option value="<?php echo $year; ?>"><?php echo $year; ?></option>
			<?php endforeach; ?>
			</select>
			
			<label for="platform">Platform</label>
            <select id="platform" name="platform">
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
			
            <label for="rating">Rating:</label>
            <select id="rating" name="rating">
            <?php foreach($ratings as $rating) : ?>
			<option value="<?php echo $rating; ?>"><?php echo $rating; ?></option>
			<?php endforeach; ?>
			</select>
			
			<label for="desc">Review</label>
            <input type="text" id="desc" name="desc" required>
            
            <label for="vod">VOD</label>
            <input type="text" id="vod" name="vod" required>

            <label for="cover">Cover URL</label>
                <input type="text" id="cover" name="cover" required>
			<button type="button" style="width: 100%;" onclick="openModal()">🔍 Search</button>


            <label for="completed"  style="margin-top: 10px;">Completed</label>
            <select id="completed" name="completed">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>

            <label for="speedrun">Speedrun</label>
            <select id="speedrun" name="speedrun">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
            
			
			
            <button type="submit">✔️ Add Game</button>
        </form>
</div>
<!-- The Modal -->
    <div id="coverModal" class="modal">
        <div class="modal-content">
            <span class="close"><span style="float:left" id="modalTitle">Search for cover art</span><span class="modalCloseButton" onclick="closeModal()" style="float:right; font-size: 24px; margin-bottom: 5px;">&times;</span></span>
            <div class="cover-search">
                <input type="text" id="cover-search-input" placeholder="Search for cover art...">
                <button type="button" onclick="searchCovers()" style="margin-bottom: 10px;">🔍 Search</button>
            </div>
            <div class="cover-results" id="cover-results"></div>
			<center>
			<div style="display: inline-flex; margin-top: 25px;">
				<img src="https://www.igdb.com/packs/static/igdbLogo-bcd49db90003ee7cd4f4.svg" style="width: 57px;" /><span style="margin-top: 5px; margin-left: 5px;">Powered by <a class="a2" href="https://www.igdb.com/">IGDB API</a></span>
			</div>
			</center>
        </div>
    </div>

    <script>
        // Open the modal
        function openModal() {
            document.getElementById('coverModal').style.display = 'block';
        }

        // Close the modal
        function closeModal() {
            document.getElementById('coverModal').style.display = 'none';
        }

        // Search for cover art
        function searchCovers() {
    const query = document.getElementById('cover-search-input').value.trim();
    if (!query) {
        alert('Please enter a search query.');
        return;
    }
    fetch(`coversearch.php?query=${encodeURIComponent(query)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Network response was not ok: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response Data:', data); // Log the full response for debugging
            const resultsContainer = document.getElementById('cover-results');
            resultsContainer.innerHTML = '';
            if (data.error) {
                resultsContainer.innerHTML = `<p>Error: ${data.error}</p>`;
                if (data.status_code) {
                    resultsContainer.innerHTML += `<p>Status Code: ${data.status_code}</p>`;
                }
                if (data.response) {
                    resultsContainer.innerHTML += `<p>Response: ${data.response}</p>`;
                }
                return;
            }
            if (!data.data || data.data.length === 0) {
                resultsContainer.innerHTML = '<p>😔 No matching cover found.</p>';
                return;
            }
            data.data.forEach(game => {
                if (game.cover) {
                    const img = document.createElement('img');
                    img.src = game.cover.url.replace('thumb', 'cover_big'); // Use higher resolution image
                    img.onclick = () => {
                        document.getElementById('cover').value = img.src;
                        closeModal();
                    };
                    resultsContainer.appendChild(img);
                }
            });
        })
        .catch(error => {
            console.error('Error fetching cover art:', error);
            const resultsContainer = document.getElementById('cover-results');
            resultsContainer.innerHTML = `<p>Error: ${error.message}</p>`;
        });
}

        // Close the modal when clicking outside of the modal content
        window.onclick = function(event) {
            const modal = document.getElementById('coverModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</center>
</body>
</html>