<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/contacts.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];


// =====================================================
// GET
// Get manually-created contacts for one user
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
                c.ID,
                c.UserID,
                c.SourceUserID,
                c.FirstName,
                c.LastName,
                c.Email,
                c.Phone,
                c.Address,
                c.Nickname,
                c.MercenaryClass,
                c.MercenaryRank,
                c.LookingFor,
                c.GameMode,
                c.Note,
                c.Photo,
                c.DateCreated,
                c.DateUpdated
            FROM Contacts c
            WHERE c.UserID = :userId
            AND c.SourceUserID IS NULL
            ORDER BY c.LastName ASC, c.FirstName ASC'
        );

        $statement->execute([
            'userId' => $userId
        ]);

        $contacts = $statement->fetchAll(PDO::FETCH_ASSOC);

        apiResponse(200, [
            'contacts' => $contacts
        ]);

    } catch (PDOException $exception) {

        apiResponse(500, [
            'error' => 'Unable to load contacts'
        ]);
    }
}


// =====================================================
// POST
// Create manual contact and add it to My Mercenaries
// =====================================================

if ($method === 'POST') {

    $body = jsonBody();

    $userId = isset($body['userId'])
        ? (int) $body['userId']
        : 0;

    $firstName = trim($body['firstName'] ?? '');
    $lastName = trim($body['lastName'] ?? '');

    $email = trim($body['email'] ?? '');
    $phone = trim($body['phone'] ?? '');
    $address = trim($body['address'] ?? '');
    $nickname = trim($body['nickname'] ?? '');

    $mercenaryClass = trim($body['mercenaryClass'] ?? '');

    $mercenaryRank =
        isset($body['mercenaryRank']) &&
        $body['mercenaryRank'] !== ''
            ? (int) $body['mercenaryRank']
            : null;

    $lookingFor = !empty($body['lookingFor']) ? 1 : 0;

    $gameMode = trim($body['gameMode'] ?? '');
    $note = trim($body['note'] ?? '');
    $photo = trim($body['photo'] ?? '');

    if ($userId <= 0) {
        apiResponse(400, [
            'error' => 'User ID is required'
        ]);
    }

    if ($firstName === '' || $lastName === '') {
        apiResponse(400, [
            'error' => 'First name and last name are required'
        ]);
    }

    // Optional rank validation
    if (
        $mercenaryRank !== null &&
        ($mercenaryRank < 1 || $mercenaryRank > 13)
    ) {
        apiResponse(400, [
            'error' => 'Mercenary rank must be between 1 and 13'
        ]);
    }

    // No game mode if they are not looking
    if ($lookingFor === 0) {
        $gameMode = '';
    }

    try {

        $db = contactsDb();

        /*
         * We use a transaction because BOTH inserts need to succeed:
         *
         * 1. Create Contacts row
         * 2. Add that Contacts.ID into Mercenaries
         */

        $db->beginTransaction();


        // ---------------------------------------------
        // Make sure owner exists
        // ---------------------------------------------

        $statement = $db->prepare(
            'SELECT ID
            FROM Users
            WHERE ID = :userId'
        );

        $statement->execute([
            'userId' => $userId
        ]);

        if (!$statement->fetch()) {

            $db->rollBack();

            apiResponse(404, [
                'error' => 'User not found'
            ]);
        }


        // ---------------------------------------------
        // Create manual contact
        // ---------------------------------------------

        $statement = $db->prepare(
            'INSERT INTO Contacts (
                UserID,
                SourceUserID,
                FirstName,
                LastName,
                Email,
                Phone,
                Address,
                Nickname,
                MercenaryClass,
                MercenaryRank,
                LookingFor,
                GameMode,
                Note,
                Photo
            )
            VALUES (
                :userId,
                NULL,
                :firstName,
                :lastName,
                :email,
                :phone,
                :address,
                :nickname,
                :mercenaryClass,
                :mercenaryRank,
                :lookingFor,
                :gameMode,
                :note,
                :photo
            )'
        );

        $statement->execute([
            'userId' => $userId,
            'firstName' => $firstName,
            'lastName' => $lastName,

            'email' => $email !== '' ? $email : null,
            'phone' => $phone !== '' ? $phone : null,
            'address' => $address !== '' ? $address : null,
            'nickname' => $nickname !== '' ? $nickname : null,

            'mercenaryClass' =>
                $mercenaryClass !== ''
                    ? $mercenaryClass
                    : null,

            'mercenaryRank' => $mercenaryRank,

            'lookingFor' => $lookingFor,

            'gameMode' =>
                $gameMode !== ''
                    ? $gameMode
                    : null,

            'note' =>
                $note !== ''
                    ? $note
                    : null,

            'photo' =>
                $photo !== ''
                    ? $photo
                    : null
        ]);


        // New Contacts.ID
        $contactId = (int) $db->lastInsertId();


        // ---------------------------------------------
        // Add contact into My Mercenaries
        // ---------------------------------------------

        $statement = $db->prepare(
            'INSERT INTO Mercenaries (
                UserID,
                MercenaryID,
                ContactID
            )
            VALUES (
                :userId,
                NULL,
                :contactId
            )'
        );

        $statement->execute([
            'userId' => $userId,
            'contactId' => $contactId
        ]);


        $db->commit();


        apiResponse(201, [
            'message' => 'Contact created successfully',
            'contactId' => $contactId
        ]);

    } catch (PDOException $exception) {

        if ($db->inTransaction()) {
            $db->rollBack();
        }

        apiResponse(500, [
            'error' => 'Unable to create contact'
        ]);
    }
}


// =====================================================
// PUT
// Edit manual contact
// =====================================================

if ($method === 'PUT') {

    $body = jsonBody();

    $userId = isset($body['userId'])
        ? (int) $body['userId']
        : 0;

    $contactId = isset($body['contactId'])
        ? (int) $body['contactId']
        : 0;

    $firstName = trim($body['firstName'] ?? '');
    $lastName = trim($body['lastName'] ?? '');

    $email = trim($body['email'] ?? '');
    $phone = trim($body['phone'] ?? '');
    $address = trim($body['address'] ?? '');
    $nickname = trim($body['nickname'] ?? '');

    $mercenaryClass = trim($body['mercenaryClass'] ?? '');

    $mercenaryRank =
        isset($body['mercenaryRank']) &&
        $body['mercenaryRank'] !== ''
            ? (int) $body['mercenaryRank']
            : null;

    $lookingFor = !empty($body['lookingFor']) ? 1 : 0;

    $gameMode = trim($body['gameMode'] ?? '');
    $note = trim($body['note'] ?? '');
    $photo = trim($body['photo'] ?? '');

    if ($userId <= 0 || $contactId <= 0) {
        apiResponse(400, [
            'error' => 'User ID and contact ID are required'
        ]);
    }

    if ($firstName === '' || $lastName === '') {
        apiResponse(400, [
            'error' => 'First name and last name are required'
        ]);
    }

    if (
        $mercenaryRank !== null &&
        ($mercenaryRank < 1 || $mercenaryRank > 13)
    ) {
        apiResponse(400, [
            'error' => 'Mercenary rank must be between 1 and 13'
        ]);
    }

    if ($lookingFor === 0) {
        $gameMode = '';
    }

    try {

        $db = contactsDb();

        $statement = $db->prepare(
            'UPDATE Contacts
            SET
                FirstName = :firstName,
                LastName = :lastName,
                Email = :email,
                Phone = :phone,
                Address = :address,
                Nickname = :nickname,
                MercenaryClass = :mercenaryClass,
                MercenaryRank = :mercenaryRank,
                LookingFor = :lookingFor,
                GameMode = :gameMode,
                Note = :note,
                Photo = :photo
            WHERE ID = :contactId
            AND UserID = :userId
            AND SourceUserID IS NULL'
        );

        $statement->execute([
            'firstName' => $firstName,
            'lastName' => $lastName,

            'email' => $email !== '' ? $email : null,
            'phone' => $phone !== '' ? $phone : null,
            'address' => $address !== '' ? $address : null,
            'nickname' => $nickname !== '' ? $nickname : null,

            'mercenaryClass' =>
                $mercenaryClass !== ''
                    ? $mercenaryClass
                    : null,

            'mercenaryRank' => $mercenaryRank,

            'lookingFor' => $lookingFor,

            'gameMode' =>
                $gameMode !== ''
                    ? $gameMode
                    : null,

            'note' =>
                $note !== ''
                    ? $note
                    : null,

            'photo' =>
                $photo !== ''
                    ? $photo
                    : null,

            'contactId' => $contactId,
            'userId' => $userId
        ]);

        if ($statement->rowCount() === 0) {

            // Could also mean identical data, so verify existence
            $check = $db->prepare(
                'SELECT ID
                FROM Contacts
                WHERE ID = :contactId
                AND UserID = :userId
                AND SourceUserID IS NULL'
            );

            $check->execute([
                'contactId' => $contactId,
                'userId' => $userId
            ]);

            if (!$check->fetch()) {
                apiResponse(404, [
                    'error' => 'Contact not found'
                ]);
            }
        }

        apiResponse(200, [
            'message' => 'Contact updated successfully'
        ]);

    } catch (PDOException $exception) {

        apiResponse(500, [
            'error' => 'Unable to update contact'
        ]);
    }
}


// =====================================================
// DELETE
// Delete manual contact
// Mercenaries row is automatically deleted by FK CASCADE
// =====================================================

if ($method === 'DELETE') {

    $body = jsonBody();

    $userId = isset($body['userId'])
        ? (int) $body['userId']
        : 0;

    $contactId = isset($body['contactId'])
        ? (int) $body['contactId']
        : 0;

    if ($userId <= 0 || $contactId <= 0) {
        apiResponse(400, [
            'error' => 'User ID and contact ID are required'
        ]);
    }

    try {

        $db = contactsDb();

        $statement = $db->prepare(
            'DELETE FROM Contacts
            WHERE ID = :contactId
            AND UserID = :userId
            AND SourceUserID IS NULL'
        );

        $statement->execute([
            'contactId' => $contactId,
            'userId' => $userId
        ]);

        if ($statement->rowCount() === 0) {
            apiResponse(404, [
                'error' => 'Contact not found'
            ]);
        }

        /*
         * Mercenaries.ContactID has ON DELETE CASCADE,
         * so deleting Contacts automatically removes
         * its corresponding Mercenaries row.
         */

        apiResponse(200, [
            'message' => 'Contact deleted successfully'
        ]);

    } catch (PDOException $exception) {

        apiResponse(500, [
            'error' => 'Unable to delete contact'
        ]);
    }
}


// =====================================================
// Invalid request
// =====================================================

apiResponse(405, [
    'error' => 'Method not allowed'
]);
