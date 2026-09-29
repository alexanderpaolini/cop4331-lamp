<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/contacts.php';

$method = $_SERVER['REQUEST_METHOD'];


// Get current status
if ($method === 'GET') {

    $userId = isset($_GET['userId'])
        ? (int) $_GET['userId']
        : 0;

    if ($userId <= 0) {
        apiResponse(400, [
            'error' => 'User ID is required'
        ]);
    }

    try {

        $db = contactsDb();

        $statement = $db->prepare(
            'SELECT LookingFor, GameMode
            FROM Users
            WHERE ID = :userId'
        );

        $statement->execute([
            'userId' => $userId
        ]);

        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            apiResponse(404, [
                'error' => 'User not found'
            ]);
        }

        apiResponse(200, [
            'lookingFor' => (bool) $user['LookingFor'],
            'gameMode' => $user['GameMode']
        ]);

    } catch (PDOException $exception) {

        apiResponse(500, [
            'error' => 'Unable to load status'
        ]);
    }
}


// Update current status
if ($method === 'PUT') {

    $body = jsonBody();

    $userId = isset($body['userId'])
        ? (int) $body['userId']
        : 0;

    $lookingFor = isset($body['lookingFor'])
        ? (bool) $body['lookingFor']
        : false;

    $gameMode = isset($body['gameMode']) && is_string($body['gameMode'])
        ? trim($body['gameMode'])
        : '';


    if ($userId <= 0) {
        apiResponse(400, [
            'error' => 'User ID is required'
        ]);
    }


    // Check game mode
    $validGameModes = [
        'Payload',
        'King of the Hill',
        'Control Point',
        'Capture the Flag'
    ];

    if ($lookingFor && !in_array($gameMode, $validGameModes, true)) {
        apiResponse(400, [
            'error' => 'A valid game mode is required'
        ]);
    }


    // Clear game mode when status is off
    if (!$lookingFor) {
        $gameMode = null;
    }


    try {

        $db = contactsDb();

        $statement = $db->prepare(
            'UPDATE Users
            SET LookingFor = :lookingFor,
                GameMode = :gameMode
            WHERE ID = :userId'
        );

        $statement->execute([
            'lookingFor' => $lookingFor ? 1 : 0,
            'gameMode' => $gameMode,
            'userId' => $userId
        ]);

        apiResponse(200, [
            'message' => 'Status updated successfully'
        ]);

    } catch (PDOException $exception) {

        apiResponse(500, [
            'error' => 'Unable to update status'
        ]);
    }
}


// Invalid request
apiResponse(405, [
    'error' => 'Method not allowed'
]);
