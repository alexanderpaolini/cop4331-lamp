<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/contacts.php';

$query = isset($_GET['q']) && is_string($_GET['q'])
    ? trim($_GET['q'])
    : '';

$userId = isset($_GET['userId'])
    ? (int) $_GET['userId']
    : 0;


// Check search information
if ($query === '' || $userId <= 0) {
    apiResponse(400, [
        'error' => 'Search query and user ID are required'
    ]);
}


// Search users
try {

    $db = contactsDb();

    $statement = $db->prepare(
        'SELECT
            ID,
            FirstName,
            LastName,
            Login,
            MercenaryClass,
            MercenaryRank,
            LookingFor,
            GameMode
        FROM Users
        WHERE Login LIKE :query
        AND ID != :userId
        AND IsAdmin = 0
        ORDER BY Login ASC
        LIMIT 20'
    );

    $statement->execute([
        'query' => $query . '%',
        'userId' => $userId
    ]);

    $users = $statement->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $exception) {

    apiResponse(500, [
        'error' => 'Unable to search users'
    ]);
}


// Return search results
apiResponse(200, [
    'users' => $users
]);
