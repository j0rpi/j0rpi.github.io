<?php
// --------------------------------------------------------
//
// j0rpi_GameDB
//
// File: coversearch.php
// Purpose: Provides IGDB cover search functionality.
//
// --------------------------------------------------------
include('../include/functions.php');
header('Content-Type: application/json');

$query = $_GET['query'] ?? '';
if (empty($query)) {
    echo json_encode(['error' => 'Search query is required']);
    exit;
}

$clientID = getIGDBVar('IGDB_clientID');
$accessToken = getIGDBVar('IGDB_accessToken');

$postData = "fields name, cover.url; search \"$query\"; limit 16;";
error_log("IGDB Query: " . $postData);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.igdb.com/v4/games');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Client-ID: ' . $clientID,
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode === 401) {
    error_log("Authentication Error: Response - $response");
    echo json_encode(['error' => 'Authentication Error: Please check Client ID and Access Token', 'status_code' => $httpCode]);
    exit;
}

if ($httpCode === 200) {
    error_log("IGDB Raw Response: " . $response);
    $decodedResponse = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['error' => 'Invalid JSON response from IGDB']);
        exit;
    }
    
    if (empty($decodedResponse)) {
        echo json_encode(['error' => 'No games found']);
        exit;
    }

    echo json_encode(['data' => $decodedResponse]); // Send expected structure
} else {
    error_log("IGDB Error Response: " . $response);
    echo json_encode(['error' => 'Failed to fetch data from IGDB', 'status_code' => $httpCode, 'response' => $response]);
}

curl_close($ch);
?>