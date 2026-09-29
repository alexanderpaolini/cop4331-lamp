<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/contacts.php';

$method = $_SERVER['REQUEST_METHOD'] ?? '';

switch ($method) {

    case 'GET':
        getUsers();
        break;

    case 'POST':
        createAdmin();
        break;

    case 'PUT':
        updateUser();
        break;

    default:

        header('Allow: GET, POST, PUT');

        apiResponse(405, [
            'error' => 'Method not allowed'
        ]);
}


// =====================================================
// GET ALL USERS
// =====================================================

function getUsers(): void
{
    try {

        $db = contactsDb();

        $stmt = $db->prepare(
            'SELECT
                ID,
                FirstName,
                LastName,
                Login,
                IsAdmin,
                IsDisabled,
                MercenaryClass,
                MercenaryRank,
                LookingFor,
                GameMode,
                DateCreated,
                DateUpdated
            FROM Users
            ORDER BY ID ASC'
        );

        $stmt->execute();

        $users = $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );


        apiResponse(200, [
            'users' => $users
        ]);


    } catch (PDOException $exception) {

        apiResponse(500, [
            'error' =>
                'Failed to retrieve users'
        ]);
    }
}


// =====================================================
// CREATE ADMIN
// =====================================================

function createAdmin(): void
{
    $body = jsonBody();


    $firstName =
        isset($body['firstName']) &&
        is_string($body['firstName'])
            ? trim($body['firstName'])
            : '';


    $lastName =
        isset($body['lastName']) &&
        is_string($body['lastName'])
            ? trim($body['lastName'])
            : '';


    $login =
        isset($body['login']) &&
        is_string($body['login'])
            ? trim($body['login'])
            : '';


    $password =
        isset($body['password']) &&
        is_string($body['password'])
            ? $body['password']
            : '';


    // Required fields
    if (
        $firstName === '' ||
        $lastName === '' ||
        $login === '' ||
        $password === ''
    ) {

        apiResponse(400, [
            'error' =>
                'First name, last name, username, and password are required'
        ]);
    }


    // Basic password requirement
    if (strlen($password) < 6) {

        apiResponse(400, [
            'error' =>
                'Password must be at least 6 characters'
        ]);
    }


    try {

        $db = contactsDb();


        // Admin accounts do not need mercenary information
        $stmt = $db->prepare(
            'INSERT INTO Users (
                FirstName,
                LastName,
                Login,
                Password,
                IsAdmin,
                IsDisabled,
                MercenaryClass,
                MercenaryRank
            )
            VALUES (
                :firstName,
                :lastName,
                :login,
                :password,
                1,
                0,
                NULL,
                NULL
            )'
        );


        $stmt->execute([
            'firstName' =>
                $firstName,

            'lastName' =>
                $lastName,

            'login' =>
                $login,

            'password' =>
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                )
        ]);


        $adminId =
            (int) $db->lastInsertId();


        apiResponse(201, [

            'message' =>
                'Administrator created successfully',

            'user' => [

                'id' =>
                    $adminId,

                'firstName' =>
                    $firstName,

                'lastName' =>
                    $lastName,

                'login' =>
                    $login,

                'isAdmin' =>
                    true,

                'isDisabled' =>
                    false
            ]
        ]);


    } catch (PDOException $exception) {


        if (
            $exception->getCode() ===
            '23000'
        ) {

            apiResponse(409, [
                'error' =>
                    'Login is already in use'
            ]);
        }


        apiResponse(500, [
            'error' =>
                'Failed to create administrator'
        ]);
    }
}


// =====================================================
// UPDATE USER
//
// Supported actions:
//
// action = "disabled"
// action = "password"
// =====================================================

function updateUser(): void
{
    $body = jsonBody();


    $id =
        isset($body['id'])
            ? (int) $body['id']
            : 0;


    $action =
        isset($body['action']) &&
        is_string($body['action'])
            ? trim($body['action'])
            : '';


    if ($id <= 0) {

        apiResponse(400, [
            'error' =>
                'A valid user ID is required'
        ]);
    }


    if ($action === '') {

        apiResponse(400, [
            'error' =>
                'Update action is required'
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
                Login,
                IsAdmin,
                IsDisabled
            FROM Users
            WHERE ID = :id'
        );


        $stmt->execute([
            'id' => $id
        ]);


        $user =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );


        if (!$user) {

            apiResponse(404, [
                'error' =>
                    'User not found'
            ]);
        }


        // =================================================
        // DISABLE / ENABLE USER
        // =================================================

        if ($action === 'disabled') {

            if (
                !array_key_exists(
                    'isDisabled',
                    $body
                )
            ) {

                apiResponse(400, [
                    'error' =>
                        'Disabled status is required'
                ]);
            }


            $isDisabled =
                (int) $body['isDisabled'];


            if (
                $isDisabled !== 0 &&
                $isDisabled !== 1
            ) {

                apiResponse(400, [
                    'error' =>
                        'Invalid disabled status'
                ]);
            }


            $stmt = $db->prepare(
                'UPDATE Users
                SET IsDisabled = :isDisabled
                WHERE ID = :id'
            );


            $stmt->execute([
                'isDisabled' =>
                    $isDisabled,

                'id' =>
                    $id
            ]);


            apiResponse(200, [

                'message' =>
                    $isDisabled === 1
                        ? 'User disabled successfully'
                        : 'User enabled successfully'
            ]);
        }


        // =================================================
        // CHANGE PASSWORD
        // =================================================

        if ($action === 'password') {

            $password =
                isset($body['password']) &&
                is_string($body['password'])
                    ? $body['password']
                    : '';


            if ($password === '') {

                apiResponse(400, [
                    'error' =>
                        'New password is required'
                ]);
            }


            if (strlen($password) < 6) {

                apiResponse(400, [
                    'error' =>
                        'Password must be at least 6 characters'
                ]);
            }


            $hashedPassword =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            $stmt = $db->prepare(
                'UPDATE Users
                SET Password = :password
                WHERE ID = :id'
            );


            $stmt->execute([
                'password' =>
                    $hashedPassword,

                'id' =>
                    $id
            ]);


            apiResponse(200, [
                'message' =>
                    'Password updated successfully'
            ]);
        }


        // Unknown action
        apiResponse(400, [
            'error' =>
                'Invalid update action'
        ]);


    } catch (PDOException $exception) {

        apiResponse(500, [
            'error' =>
                'Failed to update user'
        ]);
    }
}
