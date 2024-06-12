<?php
require_once '../vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig('C:/xampp/htdocs/MoodleWindowsInstaller-latest-401/server/moodle/mod/exportgrades/config/client_secret.json');
$client->addScope(Google_Service_Drive::DRIVE_FILE);
$client->setAccessType('offline');
$client->setPrompt('consent');

// Path to the token file
$tokenPath = 'C:/xampp/htdocs/MoodleWindowsInstaller-latest-401/server/moodle/mod/exportgrades/config/token.json';

if (file_exists($tokenPath)) {
    $accessToken = json_decode(file_get_contents($tokenPath), true);
    $client->setAccessToken($accessToken);
}

if ($client->isAccessTokenExpired()) {
    if ($client->getRefreshToken()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    } else {
        // Request authorization from the user
        $authUrl = $client->createAuthUrl();
        
        // Print the authorization URL manually
        echo "Open the following link in your browser:\n";
        echo $authUrl . "\n";
        echo "Enter verification code: ";
        $authCode = trim(readline());

        // Exchange authorization code for an access token
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        $client->setAccessToken($accessToken);

        // Check to see if there was an error
        if (array_key_exists('error', $accessToken)) {
            throw new Exception(join(', ', $accessToken));
        }
    }
    // Save the token to a file
    if (!file_exists(dirname($tokenPath))) {
        mkdir(dirname($tokenPath), 0700, true);
    }
    file_put_contents($tokenPath, json_encode($client->getAccessToken()));
}

echo "Authentication successful!";
