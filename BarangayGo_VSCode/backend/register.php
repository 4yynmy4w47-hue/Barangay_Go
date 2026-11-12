<?php
require "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(["error" => "Invalid request."], 405);
}

$data = json_decode(file_get_contents("php://input"), true);

$fullname = trim($data["fullname"] ?? "");
$mobile = trim($data["mobile"] ?? "");
$email = trim($data["email"] ?? "");
$address = trim($data["address"] ?? "");
$password = $data["password"] ?? "";

if (!$fullname || !$mobile || !$email || !$address || !$password) {
    respond(["error" => "Please complete all fields."], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(["error" => "Invalid email address."], 400);
}

if (strlen($password) < 8) {
    respond(["error" => "Password must have at least 8 characters."], 400);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare(
        "INSERT INTO users (fullname, mobile, email, address, password)
         VALUES (?, ?, ?, ?, ?)"
    );

    $stmt->execute([$fullname, $mobile, $email, $address, $hash]);

    respond(["message" => "Registration successful!"]);
} catch (PDOException $e) {
    respond(["error" => "Mobile number or email is already registered."], 409);
}
