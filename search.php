<?php
// --------------------------------------------------------
//
// j0rpi_GameDB
//
// File: search.php
// Purpose: Provides functionality for search engine 
// 			on main page.
//
// --------------------------------------------------------
include('include/config.php');
header('Content-Type: application/json');

try {
    $db = new SQLite3('games.db');

    // Get search parameters
    $search = isset($_GET['q']) ? $_GET['q'] : '';
    $genre = isset($_GET['genre']) ? $_GET['genre'] : '';
    $completed = isset($_GET['completed']) ? $_GET['completed'] : '';
    $speedrun = isset($_GET['speedrun']) ? $_GET['speedrun'] : '';
    $year = isset($_GET['year']) ? $_GET['year'] : '';
	$rating = isset($_GET['rating']) ? $_GET['rating'] : '';
	$platform = isset($_GET['platform']) ? $_GET['platform'] : '';

    // Debugging: log received parameters
    error_log("Search parameters: q={$search}, genre={$genre}, completed={$completed}, speedrun={$speedrun}, year={$year}, rating={$rating}, platform={$platform}");

    // Build query with filters
    $query = 'SELECT * FROM games WHERE title LIKE :search';
    $params = [':search' => '%' . $search . '%'];

    if ($genre) {
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
	
	if ($rating !== '') {
		$query .= ' AND rating = :rating';
		$params[':rating'] = $rating;
	}
	
	if ($platform !== '') {
		$query .= ' AND platform = :platform';
		$params[':platform'] = $platform;
	}

    // Debugging: log constructed query and parameters
    error_log("Query: {$query}");
    error_log("Parameters: " . json_encode($params));

    $stmt = $db->prepare($query);

    foreach ($params as $key => $value) {
        if (is_int($value)) {
            $stmt->bindValue($key, $value, SQLITE3_INTEGER);
        } else {
            $stmt->bindValue($key, $value, SQLITE3_TEXT);
        }
    }

    $result = $stmt->execute();

    $games = [];
    while ($game = $result->fetchArray(SQLITE3_ASSOC)) {
        $games[] = $game;
    }

    // Return results as JSON
    echo json_encode(['games' => $games]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['error' => 'An error occurred while processing your request.']);
}
?>