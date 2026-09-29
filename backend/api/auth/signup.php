<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/contacts.php';

requirePost();

$body = jsonBody();


// =====================================================
// INPUT
// =====================================================

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

$accountType =
    isset($body['accountType']) &&
    is_string($body['accountType'])
        ? trim($body['accountType'])
        : 'user';

$adminCode =
    isset($body['adminCode']) &&
    is_string($body['adminCode'])
        ? $body['adminCode']
        : '';

$mercenaryClass =
    isset($body['mercenaryClass']) &&
    is_string($body['mercenaryClass'])
        ? trim($body['mercenaryClass'])
        : '';

$mercenaryRank =
    isset($body['mercenaryRank']) &&
    $body['mercenaryRank'] !== ''
        ? (int) $body['mercenaryRank']
        : 0;


// =====================================================
// REQUIRED INFORMATION
// =====================================================

if (
    $firstName === '' ||
    $lastName === '' ||
    $login === '' ||
    $password === '' ||
    $mercenaryClass === '' ||
    $mercenaryRank === 0
) {

    apiResponse(400, [
        'error' =>
            'All required fields must be completed'
    ]);
}


// =====================================================
// VALID MERCENARY CLASS
// =====================================================

$validClasses = [
    'Scout',
    'Soldier',
    'Pyro',
    'Demoman',
    'Heavy',
    'Engineer',
    'Medic',
    'Sniper',
    'Spy'
];

if (
    !in_array(
        $mercenaryClass,
        $validClasses,
        true
    )
) {

    apiResponse(400, [
        'error' => 'Invalid mercenary class'
    ]);
}


// =====================================================
// VALID MERCENARY RANK
// =====================================================

if (
    $mercenaryRank < 1 ||
    $mercenaryRank > 13
) {

    apiResponse(400, [
        'error' => 'Invalid mercenary rank'
    ]);
}


// =====================================================
// ACCOUNT TYPE
// =====================================================

// Normal account by default
$isAdmin = 0;


// Admin accounts require registration code
if ($accountType === 'admin') {

    $correctAdminCode =
        getenv(
            'ADMIN_REGISTRATION_CODE'
        ) ?: '';

    if (
        $correctAdminCode === '' ||
        !hash_equals(
            $correctAdminCode,
            $adminCode
        )
    ) {

        apiResponse(403, [
            'error' =>
                'Invalid admin registration code'
        ]);
    }

    $isAdmin = 1;
}


// All new accounts begin enabled
$isDisabled = 0;


// =====================================================
// CREATE USER
// =====================================================

try {

    $db = contactsDb();


    $statement = $db->prepare(
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
            :isAdmin,
            :isDisabled,
            :mercenaryClass,
            :mercenaryRank
        )'
    );


    $statement->execute([
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
            ),

        'isAdmin' =>
            $isAdmin,

        'isDisabled' =>
            $isDisabled,

        'mercenaryClass' =>
            $mercenaryClass,

        'mercenaryRank' =>
            $mercenaryRank
    ]);


    $userId =
        (int) $db->lastInsertId();


} catch (PDOException $exception) {


    // Duplicate username
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
            'Unable to register user'
    ]);
}


// =====================================================
// RESPONSE
// =====================================================

apiResponse(201, [

    'message' =>
        'User registered successfully',

    'user' => [

        'id' =>
            $userId,

        'firstName' =>
            $firstName,

        'lastName' =>
            $lastName,

        'login' =>
            $login,

        'isAdmin' =>
            (bool) $isAdmin,

        'isDisabled' =>
            false,

        'mercenaryClass' =>
            $mercenaryClass,

        'mercenaryRank' =>
            $mercenaryRank
    ]
]);
