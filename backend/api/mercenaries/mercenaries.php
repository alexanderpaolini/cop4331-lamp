<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/contacts.php';

$method = $_SERVER['REQUEST_METHOD'];


// =====================================================
// GET
// Load both registered and manual mercenaries
// =====================================================

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
            'SELECT
                m.ID AS ListID,

                m.MercenaryID,
                m.ContactID,

                CASE
                    WHEN m.MercenaryID IS NOT NULL
                        THEN "registered"
                    ELSE "manual"
                END AS SourceType,

                CASE
                    WHEN m.MercenaryID IS NOT NULL
                        THEN u.ID
                    ELSE c.ID
                END AS ID,

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
                c.Photo,

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

        $statement->execute([
            'userId' => $userId
        ]);

        $mercenaries =
            $statement->fetchAll(PDO::FETCH_ASSOC);

        apiResponse(200, [
            'mercenaries' => $mercenaries
        ]);

    } catch (PDOException $exception) {

        apiResponse(500, [
            'error' => 'Unable to load mercenaries'
        ]);
    }
}


// =====================================================
// POST
// Add registered mercenary
// =====================================================

if ($method === 'POST') {

    $body = jsonBody();

    $userId = isset($body['userId'])
        ? (int) $body['userId']
        : 0;

    $mercenaryId = isset($body['mercenaryId'])
        ? (int) $body['mercenaryId']
        : 0;


    if ($userId <= 0 || $mercenaryId <= 0) {

        apiResponse(400, [
            'error' =>
                'User ID and mercenary ID are required'
        ]);
    }


    if ($userId === $mercenaryId) {

        apiResponse(400, [
            'error' => 'You cannot add yourself'
        ]);
    }


    try {

        $db = contactsDb();


        // Check owner exists
        $statement = $db->prepare(
            'SELECT ID
            FROM Users
            WHERE ID = :userId'
        );

        $statement->execute([
            'userId' => $userId
        ]);

        if (!$statement->fetch()) {

            apiResponse(404, [
                'error' => 'User not found'
            ]);
        }


        // Check mercenary exists
        $statement = $db->prepare(
            'SELECT ID
            FROM Users
            WHERE ID = :mercenaryId
            AND IsAdmin = 0'
        );

        $statement->execute([
            'mercenaryId' => $mercenaryId
        ]);

        if (!$statement->fetch()) {

            apiResponse(404, [
                'error' => 'Mercenary not found'
            ]);
        }


        // Add registered mercenary
        $statement = $db->prepare(
            'INSERT INTO Mercenaries (
                UserID,
                MercenaryID,
                ContactID
            )
            VALUES (
                :userId,
                :mercenaryId,
                NULL
            )'
        );

        $statement->execute([
            'userId' => $userId,
            'mercenaryId' => $mercenaryId
        ]);


        apiResponse(201, [
            'message' =>
                'Mercenary added successfully'
        ]);


    } catch (PDOException $exception) {


        // Duplicate relationship
        if ($exception->getCode() === '23000') {

            apiResponse(409, [
                'error' =>
                    'Mercenary is already added'
            ]);
        }


        apiResponse(500, [
            'error' =>
                'Unable to add mercenary'
        ]);
    }
}


// =====================================================
// DELETE
// Remove registered mercenary
// Manual contacts are deleted through manual-contacts.php
// =====================================================

if ($method === 'DELETE') {

    $body = jsonBody();

    $userId = isset($body['userId'])
        ? (int) $body['userId']
        : 0;

    $mercenaryId = isset($body['mercenaryId'])
        ? (int) $body['mercenaryId']
        : 0;


    if ($userId <= 0 || $mercenaryId <= 0) {

        apiResponse(400, [
            'error' =>
                'User ID and mercenary ID are required'
        ]);
    }


    try {

        $db = contactsDb();


        $statement = $db->prepare(
            'DELETE FROM Mercenaries
            WHERE UserID = :userId
            AND MercenaryID = :mercenaryId
            AND ContactID IS NULL'
        );


        $statement->execute([
            'userId' => $userId,
            'mercenaryId' => $mercenaryId
        ]);


        if ($statement->rowCount() === 0) {

            apiResponse(404, [
                'error' => 'Mercenary not found'
            ]);
        }


        apiResponse(200, [
            'message' =>
                'Mercenary removed successfully'
        ]);


    } catch (PDOException $exception) {

        apiResponse(500, [
            'error' =>
                'Unable to remove mercenary'
        ]);
    }
}


// =====================================================
// INVALID REQUEST
// =====================================================

apiResponse(405, [
    'error' => 'Method not allowed'
]);
