<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/contacts.php';

requirePost();
$body = jsonBody();

$login = isset($body['login']) && is_string($body['login'])
    ? trim($body['login'])
    : '';
$password = isset($body['password']) && is_string($body['password'])
    ? $body['password']
    : '';

if ($login === '' || $password === '') {
    apiResponse(400, ['error' => 'Login and password are required']);
}

try {
    $statement = contactsDb()->prepare(
        'SELECT ID, FirstName, LastName, Login, Password FROM Users WHERE Login = :login LIMIT 1'
    );
    $statement->execute(['login' => $login]);
    $user = $statement->fetch();
} catch (PDOException $exception) {
    apiResponse(500, ['error' => 'Unable to process login']);
}

if (!$user || !password_verify($password, $user['Password'])) {
    apiResponse(401, ['error' => 'Invalid login or password']);
}

apiResponse(200, [
    'message' => 'Login successful',
    'user' => [
        'id' => (int) $user['ID'],
        'firstName' => $user['FirstName'],
        'lastName' => $user['LastName'],
        'login' => $user['Login'],
    ],
]);
