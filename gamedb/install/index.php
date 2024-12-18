<?php
// --------------------------------------------------------
//
// j0rpi_GameDB
//
// File: index.php
// Purpose: Improved installation script
//
//
// --------------------------------------------------------

//
// Define post variable so PHP won't complain about a non-existant variable
//
$_POST['generatedToken'] = "";

// 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	// Run install script
	if (isset($_POST['doInstall'])) {
		doInstall();
	}
	
	// Generate new IGDB token
	if (isset($_POST['generateToken'])) {
		generateToken($_POST['id'], $_POST['secret']);
	}
}

//
// IGDB Access Token generation function
//
function generateToken($clientID, $clientSecret) {
$ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://id.twitch.tv/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => $clientID,
        'client_secret' => $clientSecret,
        'grant_type' => 'client_credentials'
    ]));

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
	
	// It worked
	if (isset($data['access_token'])) {
		// Set this variable the same as the new generated token, allowing it 
		// to be automaticly set in the input field at POST.
		$_POST['generatedToken'] = $data['access_token'];
		echo "<div class='errorbar' style='background-color: darkgreen;'><span style='margin-bottom: 2px'>✔️ Success! Access token has been copied to Access Token field.</div><br><br>";
    } 
	// It didn't work ...
	else {
        echo "<div class='errorbar' style='background-color: darkred;'><span style='margin-bottom: 2px'>⛔️ There was an error generating an access token. Please check your ClientID and ClientSecret.</div><br><br>";
    }
}
//
// Simple install script 
// Checks if game.db exists, and if it doesn't it will
// create the database, create tables, and insert all
// data specified by the user post-install. 
//
function doInstall()
{
	$error = "";

	// Check if database exists already
	if (file_exists('../games.db')){
		$error = "GameDB seems to be installed already..";
	}
	else {
		// Define new database
		$db = new SQLite3('../games.db');
	}

	// Create games table
	$db->exec('CREATE TABLE IF NOT EXISTS games (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		title TEXT NOT NULL,
		year TEXT,
		rating REAL,
		desc TEXT,
		vod TEXT,
		cover TEXT,
		genre TEXT,
		completed INTEGER,
		speedrun INTEGER,
		platform TEXT
	)');

	// Create config table
	$db->exec('CREATE TABLE IF NOT EXISTS configuration (
		headerTitle TEXT PRIMARY KEY,
		style TEXT NOT NULL,
		listMax INTEGER NOT NULL,
		minSelectableYear INTEGER NOT NULL,
		useRatingIcons INTEGER NOT NULL,
		usePlatformIcons Integer NOT NULL,
		vodLinkText TEXT NOT NULL,
		IGDB_clientID TEXT,
		IGDB_clientSecret TEXT,
		IGDB_accessToken TEXT,
		language TEXT
	)');

	// Create admins table
	$db->exec('CREATE TABLE IF NOT EXISTS admins (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		username TEXT NOT NULL UNIQUE,
		password TEXT NOT NULL
	)');
	
	// Create categories table
	$db->exec('CREATE TABLE IF NOT EXISTS categories (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		cat_name TEXT NOT NULL UNIQUE,
		odd_genre INTEGER NOT NULL
	)');

	// Create platforms table
	$db->exec('CREATE TABLE IF NOT EXISTS platforms (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		name TEXT NOT NULL UNIQUE,
		short_prefix TEXT NOT NULL
	)');

	// Insert sample admin account
	$db->exec("INSERT INTO admins (username, password) VALUES ('" . $_POST['admin_username'] . "', '" . password_hash($_POST['admin_password'], PASSWORD_DEFAULT) . "')");

	// Create default config
	$db->exec("INSERT INTO configuration (style, headerTitle, listMax, minSelectableYear, useRatingIcons, usePlatformIcons, vodLinkText, IGDB_clientID, IGDB_clientSecret, IGDB_accessToken, language) VALUES ('" . $_POST['style'] . "','" .  $_POST['headerTitle'] . "','" .  $_POST['listMax'] . "','" . $_POST['minSelectableYear'] . "','" . $_POST['useRatingIcons'] . "','" . $_POST['usePlatformIcons'] . "','" . $_POST['vodLinkText'] . "','" . $_POST['id'] . "','" . $_POST['secret'] . "','" .  $_POST['accessToken'] . "', '" . $_POST['language'] . "')");

	// Finally when finished, redirect to admin Page
	header("Location: ../admin/login.php");
	die();
}
?>
<!DOCTYPE html>
<html lang='en'>
<head>
	<meta charset='UTF-8'>
	<meta name='viewport' content='width=device-width, initial-scale=1.0'>
	<title>GameDB :: Install</title>
	<style>
        body, html {
			height: 100%;
			margin: 0;
			font-size: 14px;
			font-family: Bahnschrift;
			color: white;
			background-attachment: fixed;
			background-image: url("../styles/default/img/bg.jpg");
			background-size: cover;
		}

		* {
			box-sizing: border-box;

		}
		h1 {
			margin: 20px;
			color: white;
		}
		.form-container a {
			text-decoration: none;
		}
		.form-container a:hover {
			text-decoration: none;
			color: #0080ff;
		}
		.form-container {
			background-color: rgb(0,0,0);
			background-color: rgba(0,0,0, 0.3);

            padding: 20px;
            border-radius: 8px;
			border: 1px solid #666666;
            margin-bottom: 20px;
        }
        .form-container h2 {
            margin-top: 0;
			font-size: 20px;
        }
        .form-container form {
            display: flex;
            flex-direction: column;
			border: 0px solid black;

        }
        .form-container input[type=text], .form-container input[type=password] {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
			font-family: Bahnschrift;
			background-color: rgb(0,0,0);
			background-color: rgba(0,0,0, 0.1);
			color:#fff;
        }
		.form-container select {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
			font-family: Bahnschrift;
			background-color: rgba(0,0,0, 0.1);
			color:#fff;
        }
		.form-container option {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
			font-family: Bahnschrift;
			background-color: rgba(0,0,0, 0.2);
			color:#000;
        }
		.form-container option:focus {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
			font-family: Bahnschrift;
			background-color: rgba(0,0,0,0.9);
        }
        .form-container button {
            padding: 10px;
            background-color: #0080ff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
			font-family: Bahnschrift;
        }
        .form-container button:hover {
            background-color: #45a049;
        }
		.bg-text {

			position: absolute;
			top: 35%;
			left: 50%;
			transform: translate(-50%, -50%);
			z-index: 1;
			width: 50%;
			height: 100%;
			padding: 0px;
			border: 0px solid black;
		}
		a {
			color: #0080ff;
		}
		.errorbar {
			width: 100%
			height: 25px;
			padding: 10px;
			font-family: Bahnschrift;
			font-size: 14px;
			background-color: darkred;
		}
		.groupBox {
			padding: 10px;
			border: 1px solid #666666;
			border-radius: 8px;
			margin-bottom: 25px;
		}
		
		.groupBox input[type="text"], input[type="submit"] {	
			width: 100%;
		}
		.groupBox2 {
			padding: 10px;
			border: 1px solid #666666;
			border-radius: 8px;
			margin-bottom: 10px;
		}
		.groupBox2 input[type="text"], input[type="password"], input[type="submit"] {	
			width: 100%;
		}
		.groupBox2 option, select {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
			font-family: Bahnschrift;
			background-color: rgba(0,0,0, 0.2);
			color:#fff;
			width: 100%
        }
		.groupBox2 option:focus, select:focus {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
			font-family: Bahnschrift;
			background-color: rgba(0,0,0,0.9);
        }
	</style>
<body>
	<div class='bg-text'>
		<center><img src='../styles/default/img/logo.png' style='margin-top: 200px;' />
			<h2>Install Script</h2></center>
				<div class='form-container'>
				<form method="POST">
				<div class='groupBox'>
				<h2>IGDB Settings</h2>
					<p>GameDB uses IGDB for game covers and other features. You will need a <a href="https://twitch.tv">Twitch Account</a> to make use of IGDB. <a href="https://api-docs.igdb.com/#getting-started">Click here to get started.</a></p>
						<label for='IGDB_clientID'>IGDB Client ID</label><br>
						<input type='text' id='IGDB_clientID' name='id' placeholder='' value=''><br><br>

						<label for='IGDB_clientSecret'>IGDB Client Secret</label><br>
						<input type='text' id='IGDB_clientSecret' name='secret' placeholder='' value=''><br><br>

						<label for='IGDB_accessToken'>IGDB Access Token</label><br>
						<input type='text' id='IGDB_accessToken' name='accessToken' value='<?php echo $_POST['generatedToken'] ?>'><br><br>
						
						<input type='submit' type="button" name="generateToken" value="Generate Access Token" style="background-color: #0080ff; border: 1px solid black; border-radius: 4px; padding: 10px; color: white; font-family: Bahnschrift;"><br>
				</div>
				<div class='groupBox2'>
				<h2>GameDB Settings</h2>
						<label for='admin_username'>Language</label><br>
						<select name="language">
							<option value="english">🇬🇧 English</option>
							<option value="swedish">🇸🇪 Swedish</option>
						</select><br>

						<label for='admin_username'>Admin Username</label><br>
						<input type='text' id='admin_username' name='admin_username' placeholder=''><br><br>

						<label for='admin_password'>Admin Password</label><br>
						<input type='password' id='admin_password' name='admin_password' placeholder='' autocomplete='new-password'><br><br>

						<label for='style'>Style</label><br>
						<input type='text' id='style' name='style' placeholder='' value='default'><br><br>

						<label for='headerTitle'>Header Title</label><br>
						<input type='text' id='headerTitle' name='headerTitle' placeholder=''><br><br>

						<label for='listMax'>Max Games Per Page</label><br>
						<input type='text' id='listMax' name='listMax' placeholder='Max games per page' value='10'><br><br>

						<label for='minSelectableYear'>Minimum Selectable Year For Filters</label><br>
						<input type='text' id='minSelectableYear' name='minSelectableYear' placeholder='Minimum Selectable Year For Filters' value='1960'><br><br>

						<label for='useRatingIcons'>Use Rating Icons</label><br>
						<input type='text' id='useRatingIcons' name='useRatingIcons' placeholder='Must be 0 or 1'><br><br>

						<label for='usePlatformIcons'>Use Platform Icons</label><br>
						<input type='text' id='usePlatformIcons' name='usePlatformIcons' placeholder='Must be 0 or 1'><br><br>

						<label for='vodLinkText'>VOD Link Text</label><br>
						<input type='text' id='vodLinkText' name='vodLinkText' placeholder='VOD Link Text' value='WATCH'><br><br>

						<input type='submit' name='doInstall' value='Install' style='background-color: #0080ff; border: 1px solid black; border-radius: 4px; padding: 10px; color: white; font-family: Bahnschrift;'>
						</div>
					</div>
				</form>
		</div>
</body>
</html>
