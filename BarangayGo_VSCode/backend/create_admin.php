<?php
require "config.php";

// Run this file only once, then DELETE it.

$stmt = $pdo->prepare(
    "SELECT id FROM users WHERE mobile = ?"
);
$stmt->execute(["09170000000"]);

if ($stmt->fetch()) {
    exit("Admin account already exists. Delete this file.");
}

$hash = password_hash("ChangeMe123!", PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    "INSERT INTO users
    (fullname, mobile, email, address, password, role)
    VALUES (?, ?, ?, ?, ?, 'admin')"
);

$stmt->execute([
    "BarangayGo Administrator",
    "09170000000",
    "admin@barangaygo.local",
    "Barangay Hall",
    $hash
]);

echo "Admin created. DELETE this file immediately.";
