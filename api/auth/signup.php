<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/contacts.php';

requirePost();
$body = jsonBody();

$firstName = isset($body['firstName']) && is_string($body['firstName'])
    ? trim($body['firstName'])
    : '';
$lastName = isset($body['lastName']) && is_string($body['lastName'])
    ? trim($body['lastName'])
    : '';
$login = isset($body['login']) && is_string($body['login'])
    ? trim($body['login'])
    : '';
$password = isset($body['password']) && is_string($body['password'])
    ? $body['password']
    : '';

$accountType = isset($body['accountType']) && is_string($body['accountType'])
    ? trim($body['accountType'])
    : 'user';

$adminCode = isset($body['adminCode']) && is_string($body['adminCode'])
    ? $body['adminCode']
    : '';

if ($firstName === '' || $lastName === '' || $login === '' || $password === '') {
    apiResponse(400, ['error' => 'First name, last name, login, and password are required']);
}

$isAdmin = 0;

// Normal accounts are not admins
$isAdmin = 0;


// If Admin was selected, verify the admin registration code
if ($accountType === 'admin') {

    $correctAdminCode = getenv('ADMIN_REGISTRATION_CODE') ?: '';

    if (
        $correctAdminCode === '' ||
        !hash_equals($correctAdminCode, $adminCode)
    ) {
        apiResponse(403, [
            'error' => 'Invalid admin registration code'
        ]);
    }

    $isAdmin = 1;
}


try {
    $statement = contactsDb()->prepare(
        'INSERT INTO Users (FirstName, LastName, Login, Password) '
        . 'VALUES (:firstName, :lastName, :login, :password)'
    );
    $statement->execute([
        'firstName' => $firstName,
        'lastName' => $lastName,
        'login' => $login,
        'password' => password_hash($password, PASSWORD_DEFAULT),
	'IsAdmin' => (bool) $IsAdmin,
    ]);
    $userId = (int) contactsDb()->lastInsertId();
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        apiResponse(409, [
            'error' => 'Login is already in use'
        ]);
    }

    apiResponse(500, [
        'error' => 'Unable to register user'
    ]);
}

apiResponse(201, [
    'message' => 'User registered successfully',
    'user' => [
        'id' => $userId,
        'firstName' => $firstName,
        'lastName' => $lastName,
        'login' => $login,
	'IsAdmin' => (bool) $IsAdmin
    ],
]);
