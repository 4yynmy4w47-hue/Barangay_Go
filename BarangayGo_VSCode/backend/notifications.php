<?php
require "config.php";

requireRole("resident");

$stmt = $pdo->prepare(
    "SELECT id, message, is_read, created_at
     FROM notifications
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->execute([$_SESSION["user_id"]]);

respond($stmt->fetchAll());
