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

if ($firstName === '' || $lastName === '' || $login === '' || $password === '') {
    apiResponse(400, ['error' => 'First name, last name, login, and password are required']);
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
    ]);
    $userId = (int) contactsDb()->lastInsertId();
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        apiResponse(409, ['error' => 'Login is already in use']);
    }

    apiResponse(500, ['error' => 'Unable to register user']);
}

apiResponse(201, [
    'message' => 'User registered successfully',
    'user' => [
        'id' => $userId,
        'firstName' => $firstName,
        'lastName' => $lastName,
        'login' => $login,
    ],
]);
