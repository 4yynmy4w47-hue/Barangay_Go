<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");

$host = "localhost";
$dbname = "barangaygo";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed."]);
    exit;
}

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function requireLogin() {
    if (!isset($_SESSION["user_id"])) {
        respond(["error" => "Please log in first."], 401);
    }
}

function requireRole($role) {
    requireLogin();

    if ($_SESSION["role"] !== $role) {
        respond(["error" => "Access denied."], 403);
    }
}

function verifyCsrf() {
    $token = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

    if (
        empty($_SESSION["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $token)
    ) {
        respond(["error" => "Invalid security token. Refresh and try again."], 403);
    }
}

function notifyUser($userId, $requestId, $message) {
    global $pdo;

    $stmt = $pdo->prepare(
        "INSERT INTO notifications (user_id, request_id, message)
         VALUES (?, ?, ?)"
    );

    $stmt->execute([$userId, $requestId, $message]);
}
