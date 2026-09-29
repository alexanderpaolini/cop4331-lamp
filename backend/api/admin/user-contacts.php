<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/contacts.php';

$method = $_SERVER['REQUEST_METHOD'] ?? '';

if ($method !== 'GET') {

    header('Allow: GET');

    apiResponse(405, [
        'error' => 'Method not allowed'
    ]);
}


// =====================================================
// GET USER ENTRIES
// =====================================================

$userId =
    isset($_GET['userId'])
        ? (int) $_GET['userId']
        : 0;


if ($userId <= 0) {

    apiResponse(400, [
        'error' => 'A valid user ID is required'
    ]);
}


try {

    $db = contactsDb();


    // Make sure user exists
    $stmt = $db->prepare(
        'SELECT
            ID,
            FirstName,
            LastName,
            Login
        FROM Users
        WHERE ID = :userId'
    );


    $stmt->execute([
        'userId' => $userId
    ]);


    $user =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );


    if (!$user) {

        apiResponse(404, [
            'error' => 'User not found'
        ]);
    }


    // Get both registered mercenaries
    // and manually-created contacts
    $stmt = $db->prepare(
        'SELECT

            m.ID AS ListID,

            m.MercenaryID,
            m.ContactID,

            CASE
                WHEN m.MercenaryID IS NOT NULL
                    THEN "registered"
                ELSE "manual"
            END AS SourceType,

            COALESCE(
                u.FirstName,
                c.FirstName
            ) AS FirstName,

            COALESCE(
                u.LastName,
                c.LastName
            ) AS LastName,

            COALESCE(
                u.Login,
                c.Login
            ) AS Login,

            c.Nickname,

            COALESCE(
                u.MercenaryClass,
                c.MercenaryClass
            ) AS MercenaryClass,

            COALESCE(
                u.MercenaryRank,
                c.MercenaryRank
            ) AS MercenaryRank,

            COALESCE(
                u.LookingFor,
                c.LookingFor
            ) AS LookingFor,

            COALESCE(
                u.GameMode,
                c.GameMode
            ) AS GameMode,

            c.Email,
            c.Phone,
            c.Address,
            c.Note,

            m.DateCreated

        FROM Mercenaries m

        LEFT JOIN Users u
            ON m.MercenaryID = u.ID

        LEFT JOIN Contacts c
            ON m.ContactID = c.ID

        WHERE m.UserID = :userId

        ORDER BY
            COALESCE(
                u.Login,
                c.Login,
                c.Nickname,
                c.LastName,
                c.FirstName
            ) ASC'
    );


    $stmt->execute([
        'userId' => $userId
    ]);


    $entries =
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );


    apiResponse(200, [

        'user' => $user,

        'entries' => $entries
    ]);


} catch (PDOException $exception) {

    apiResponse(500, [
        'error' =>
            'Failed to retrieve user entries'
    ]);
}
