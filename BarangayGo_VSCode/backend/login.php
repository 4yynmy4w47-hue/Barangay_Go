<?php
require "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(["error" => "Invalid request."], 405);
}

$data = json_decode(file_get_contents("php://input"), true);

$mobile = trim($data["mobile"] ?? "");
$password = $data["password"] ?? "";

$stmt = $pdo->prepare("SELECT * FROM users WHERE mobile = ?");
$stmt->execute([$mobile]);

$user = $stmt->fetch();

if (!$user || !password_verify($password, $user["password"])) {
    respond(["error" => "Incorrect mobile number or password."], 401);
}

session_regenerate_id(true);

$_SESSION["user_id"] = $user["id"];
$_SESSION["fullname"] = $user["fullname"];
$_SESSION["role"] = $user["role"];
$_SESSION["csrf_token"] = bin2hex(random_bytes(32));

respond([
    "message" => "Login successful!",
    "role" => $user["role"]
]);
