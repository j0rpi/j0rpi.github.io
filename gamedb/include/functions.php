<?php
// --------------------------------------------------------
//
// j0rpi_GameDB
//
// File: include/functions.php
// Purpose: Make stuff easier right.. 
//
// --------------------------------------------------------

function getPlatformName($short_prefix)
{
    $db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/gamedb/games.db', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    $stmt = $db->prepare('SELECT "name" FROM "platforms" WHERE "short_prefix" = :short_prefix');
    $stmt->bindValue(':short_prefix', $short_prefix, SQLITE3_TEXT); // Use SQLITE3_TEXT since short_prefix is likely text
    $result = $stmt->execute();
    $platform = $result->fetchArray(SQLITE3_ASSOC);
    return $platform['name'] ?? 'Unknown'; // Return 'Unknown' if no name is found
}

// --------------------------------------------------------
//
// Get game title by game ID
// 
// Usage: displayGameID(id) 
//
// --------------------------------------------------------
function displayGameByID($gameID)
{
    $db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/gamedb/games.db', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    $stmt = $db->prepare('SELECT "title" FROM "games" WHERE "id" = :gameID');
    $stmt->bindValue(':gameID', $gameID, SQLITE3_INTEGER);
    $game = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    print_r($game['title']);
}

// --------------------------------------------------------
//
// Get game cover by game ID
// 
//
// --------------------------------------------------------
function displayGameCoverByID($gameID, $width)
{
    $db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/gamedb/games.db', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    $stmt = $db->prepare('SELECT "cover" FROM "games" WHERE "id" = :gameID');
    $stmt->bindValue(':gameID', $gameID, SQLITE3_INTEGER);
    $cover = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    print_r($cover['cover']);
}
// --------------------------------------------------------
//
// Get IGDB ClientID and AccessToken for Coversearches
// 
// Usage: getIGDBVar(keyname) 
//
// --------------------------------------------------------
function getIGDBVar($config_var) {
    $db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/gamedb/games.db', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    $stmt = $db->prepare('SELECT "' . $config_var . '" FROM configuration');
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    if ($result) {
        return trim($result[$config_var]); // Trim whitespace, newlines, etc.
    }
    return null; // Return null if the value is not found
}
// --------------------------------------------------------
//
// Get configuration value from database
// 
// Usage: getConfigVar(keyname) 
//
// --------------------------------------------------------
function getConfigVar($config_var) {
    $db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/gamedb/games.db', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    $stmt = $db->prepare('SELECT "' . $config_var . '" FROM configuration');
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    if ($result) {
        print( trim($result[$config_var]) ); // Trim whitespace, newlines, etc.
    }
    return null; // Return null if the value is not found
}
// --------------------------------------------------------
//
// Get configuration value from database
// 
// Usage: getConfigInt(keyname) 
//
// --------------------------------------------------------
function getConfigVarInt($config_var)
{
    $db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/gamedb/games.db', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    $stmt = $db->prepare('SELECT "' . $config_var . '" FROM "configuration"');
    $stmt->bindValue(':config_var', $config_var, SQLITE3_INTEGER);
    $var = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    return($var[$config_var]);
}

// --------------------------------------------------------
//
// Refresh IGDB access token
// 
// Usage: refreshIGDBKey(client id, client secret) 
//
// --------------------------------------------------------
function refreshIGDBKey($clientID, $clientSecret)
{
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

    if (isset($data['access_token'])) {
        file_put_contents('access_token.txt', $data['access_token']);
        echo 'Access token generated successfully. Please delete access_token.txt after setting IGDB_accessToken.';
    } else {
        echo 'Failed to refresh access token';
        echo '<pre>' . print_r($data, true) . '</pre>';
    }
}

// --------------------------------------------------------
//
// Wipe the whole database
// 
// Usage: wipeDB() 
//
// --------------------------------------------------------
function wipeAll()
{
    $db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/gamedb/games.db', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['wipeAll'])) {
            $stmt1 = $db->prepare('DELETE FROM categories');
            $stmt2 = $db->prepare('DELETE FROM platforms');
            $stmt3 = $db->prepare('DELETE FROM games');
            $stmt1->execute();
            $stmt2->execute();
            $stmt3->execute();
            $status = "The database was completely wiped.";
            echo "<div class='errorbar' style='background-color: darkgreen;'><span style='margin-bottom: 2px'>✔️ " . $status . "</div>";
        } else {
            $status = "There was an error trying to wipe the database.";
            echo "<div class='errorbar' style='background-color: darkred;'><span style='margin-bottom: 2px'>⛔️ " . $status . "</div>";
        }
    }
}

// --------------------------------------------------------
//
// Wipes categories from database
// 
// Usage: wipeCats() 
//
// --------------------------------------------------------
function wipeCats()
{
    $db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/gamedb/games.db', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['wipeCats'])) {
            $stmt = $db->prepare('DELETE FROM categories');
            $stmt->execute();
            $status = "Categories was completely wiped.";
            echo "<div class='errorbar' style='background-color: darkgreen;'><span style='margin-bottom: 2px'>✔️ " . $status . "</div>";
        } else {
            $status = "There was an error trying to wipe categories.";
            echo "<div class='errorbar' style='background-color: darkred;'><span style='margin-bottom: 2px'>⛔️ " . $status . "</div>";
        }
    }
}

// --------------------------------------------------------
//
// Wipes platforms from database
// 
// Usage: wipePlats() 
//
// --------------------------------------------------------
function wipePlats()
{
    $db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/gamedb/games.db', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['wipePlats'])) {
            $stmt = $db->prepare('DELETE FROM platforms');
            $stmt->execute();
            $status = "Platforms was completely wiped.";
            echo "<div class='errorbar' style='background-color: darkgreen;'><span style='margin-bottom: 2px'>✔️ " . $status . "</div>";
        } else {
            $status = "There was an error trying to wipe platforms";
            echo "<div class='errorbar' style='background-color: darkred;'><span style='margin-bottom: 2px'>⛔️ " . $status . "</div>";
        }
    }
}

// --------------------------------------------------------
//
// Wipes platforms from database
// 
// Usage: wipePlats() 
//
// --------------------------------------------------------
function wipeGames()
{
    $db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/gamedb/games.db', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['wipePlats'])) {
            $stmt = $db->prepare('DELETE FROM games');
            $stmt->execute();
            $status = "Games was completely wiped.";
            echo "<div class='errorbar' style='background-color: darkgreen;'><span style='margin-bottom: 2px'>✔️ " . $status . "</div>";
        } else {
            $status = "There was an error trying to wipe games.";
            echo "<div class='errorbar' style='background-color: darkred;'><span style='margin-bottom: 2px'>⛔️ " . $status . "</div>";
        }
    }
}

// --------------------------------------------------------
//
// Change password
// 
// Usage: changePassword(Old Password, New Password, Confirm New Password)
//
// --------------------------------------------------------
function changePassword($oldPassword, $newPassword, $confirmPassword) {
    // Check if the user is logged in
    if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
        return 'You must be logged in to change your password.';
    }

    // Get the current username from the session
    $username = $_SESSION['admin_username'];

    // Validate the new password and confirmation
    if (empty($newPassword) || empty($confirmPassword)) {
        echo "<div class='errorbar' style='background-color: darkred;'><span style='margin-bottom: 2px'>⛔️ Fields cannot be empty.</div>";
    }

    if ($newPassword !== $confirmPassword) {
        return "<div class='errorbar' style='background-color: darkred;'><span style='margin-bottom: 2px'>⛔️ New and confirmed password does not match.</div>";
    }

    if (strlen($newPassword) < 8) {
        return "<div class='errorbar' style='background-color: darkred;'><span style='margin-bottom: 2px'>⛔️ Password needs to be atleast 8 characters long.</div>";
    }

    // Use try-catch to handle potential database connection errors
    try {
        // Establish a secure connection to the database
        $db = new SQLite3('../games.db');

        // Use prepared statements to select the current password
        $stmt = $db->prepare('SELECT password FROM admins WHERE username = :username');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute();

        // Check if the result is valid and fetch the current password
        if ($result && ($admin = $result->fetchArray(SQLITE3_ASSOC))) {
            // Verify the old password
            if (password_verify($oldPassword, $admin['password'])) {
                // Hash the new password securely
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

                // Prepare the update statement
                $stmt = $db->prepare('UPDATE admins SET password = :password WHERE username = :username');
                $stmt->bindValue(':password', $newPasswordHash, SQLITE3_TEXT);
                $stmt->bindValue(':username', $username, SQLITE3_TEXT);
                $stmt->execute();

                // Close the statement
                $stmt->close();
                // Finalize the result set to free resources
                $result->finalize();

                return "<div class='errorbar' style='background-color: darkgreen;'><span style='margin-bottom: 2px'>✔️ Password was successfully changed.</div>";
            } else {
                // Finalize the result set if the password is incorrect
                $result->finalize();
                return "<div class='errorbar' style='background-color: darkred;'><span style='margin-bottom: 2px'>⛔️ Old password is incorrect.</div>";
            }
        } else {
            // Return an error if the user was not found or if there was a database error
            return "<div class='errorbar' style='background-color: darkred;'><span style='margin-bottom: 2px'>⛔️ Could not fetch user. Please try to login again.</div>";
        }
    } catch (Exception $e) {
        // Handle any errors (e.g., database connection issues)
        return "<div class='errorbar' style='background-color: darkred;'><span style='margin-bottom: 2px'>⛔️ Unknown error occured.</div>";
    }
}
?>
