<?php
require "config.php";

if (!isset($_SESSION["user_id"])) {
    respond([
        "loggedIn" => false
    ]);
}

respond([
    "loggedIn" => true,
    "user" => [
        "id" => $_SESSION["user_id"],
        "fullname" => $_SESSION["fullname"],
        "role" => $_SESSION["role"]
    ],
    "csrf" => $_SESSION["csrf_token"]
]);
