<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/contacts.php';

requirePost();

$body = jsonBody();

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


// =====================================================
// CHECK REQUIRED LOGIN INFORMATION
// =====================================================

if (
    $login === '' ||
    $password === ''
) {

    apiResponse(400, [
        'error' =>
            'Login and password are required'
    ]);
}


// =====================================================
// FIND USER
// =====================================================

try {

    $db = contactsDb();

    $statement = $db->prepare(
        'SELECT
            ID,
            FirstName,
            LastName,
            Login,
            Password,
            IsAdmin,
            IsDisabled,
            MercenaryClass,
            MercenaryRank,
            LookingFor,
            GameMode
        FROM Users
        WHERE Login = :login
        LIMIT 1'
    );

    $statement->execute([
        'login' => $login
    ]);

    $user =
        $statement->fetch(
            PDO::FETCH_ASSOC
        );

} catch (PDOException $exception) {

    apiResponse(500, [
        'error' =>
            'Unable to log in'
    ]);
}


// =====================================================
// CHECK USER EXISTS
// =====================================================

if (!$user) {

    apiResponse(401, [
        'error' =>
            'Invalid username or password'
    ]);
}


// =====================================================
// CHECK PASSWORD
// =====================================================

if (
    !password_verify(
        $password,
        $user['Password']
    )
) {

    apiResponse(401, [
        'error' =>
            'Invalid username or password'
    ]);
}


// =====================================================
// CHECK DISABLED STATUS
// =====================================================

if (
    (int) $user['IsDisabled'] === 1
) {

    apiResponse(403, [
        'error' =>
            'This account has been disabled'
    ]);
}


// =====================================================
// RETURN LOGGED-IN USER
// =====================================================

apiResponse(200, [

    'message' =>
        'Login successful',

    'user' => [

        'id' =>
            (int) $user['ID'],

        'firstName' =>
            $user['FirstName'],

        'lastName' =>
            $user['LastName'],

        'login' =>
            $user['Login'],

        'isAdmin' =>
            (bool) $user['IsAdmin'],

        'isDisabled' =>
            (bool) $user['IsDisabled'],

        'mercenaryClass' =>
            $user['MercenaryClass'],

        'mercenaryRank' =>
            $user['MercenaryRank'],

        'lookingFor' =>
            (bool) $user['LookingFor'],

        'gameMode' =>
            $user['GameMode']
    ]
]);
