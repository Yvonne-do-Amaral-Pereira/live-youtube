<?php

define(
    'CLIENT_FILENAME',
    'private/client_secret.json'
);
define(
    'AUTH_FILENAME',
    'private/auth.json'
);
define('CHANNEL_ID', 'UCYe-QcCgU2DJEp_UH8Md-Ig');

chdir(dirname(__DIR__));

require_once 'vendor/autoload.php';

$authToken = new AuthToken(CLIENT_FILENAME, AUTH_FILENAME);
$authorization = $authToken->getAuthorization();

$broadcast = new Broadcast($authorization);
$broadcastId = '';

// Se não tiver nada ao vivo ou agendado, mostra o último
if (file_exists('private/lastBroadcast.txt')) {
    $broadcastId = file_get_contents('private/lastBroadcast.txt');    
}

// Se tiver um ou mais ao vivo, mostra o que iniciou por último
$activeBroadcasts = $broadcast->getLive(CHANNEL_ID);
$dateLast = '1900-01-01';
foreach ($activeBroadcasts as $item) {
    if ($dateLast < $item->scheduledStartTime) {
        $dateLast = $item->scheduledStartTime;
        $broadcastId = $item->id;
    }
}

// Se não tiver nenhum ao vivo, mostra o próximo agendado
if (empty($activeBroadcasts)) {
    $upcomingBroadcasts = $broadcast->getUpcoming(CHANNEL_ID);
    $dateNext = '9999-99-99';

    foreach($upcomingBroadcasts as $item) {
        if ($dateNext > $item->scheduledStartTime) {
            $dateNext = $item->scheduledStartTime;
            $broadcastId = $item->id;
        }
    }
}

file_put_contents('private/lastBroadcast.txt', $broadcastId);
echo("Location: https://www.youtube.com/watch?v={$broadcastId}");
