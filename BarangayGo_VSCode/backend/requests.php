<?php
require "config.php";

requireLogin();

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {

    if ($_SESSION["role"] === "admin") {
        $stmt = $pdo->query(
            "SELECT requests.*, users.fullname, users.mobile
             FROM requests
             JOIN users ON requests.user_id = users.id
             ORDER BY requests.created_at DESC"
        );
    } else {
        $stmt = $pdo->prepare(
            "SELECT * FROM requests
             WHERE user_id = ?
             ORDER BY created_at DESC"
        );

        $stmt->execute([$_SESSION["user_id"]]);
    }

    respond($stmt->fetchAll());
}

verifyCsrf();

if ($method === "POST") {

    if ($_SESSION["role"] !== "resident") {
        respond(["error" => "Residents only."], 403);
    }

    $document = trim($_POST["document_type"] ?? "");
    $purpose = trim($_POST["purpose"] ?? "");
    $details = trim($_POST["details"] ?? "");

    $allowedDocuments = [
        "Barangay Clearance",
        "Certificate of Indigency",
        "Certificate of Residency",
        "Barangay Certificate"
    ];

    $allowedPurposes = [
        "Employment",
        "School/Enrollment",
        "Financial Assistance",
        "Business Requirement",
        "Personal Use",
        "Other"
    ];

    if (
        !in_array($document, $allowedDocuments, true) ||
        !in_array($purpose, $allowedPurposes, true)
    ) {
        respond(["error" => "Invalid document type or purpose."], 400);
    }

    if (!isset($_FILES["id_file"]) ||
        $_FILES["id_file"]["error"] !== UPLOAD_ERR_OK) {
        respond(["error" => "Please upload a valid ID."], 400);
    }

    $file = $_FILES["id_file"];

    if ($file["size"] > 5 * 1024 * 1024) {
        respond(["error" => "File must not exceed 5MB."], 400);
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file["tmp_name"]);

    $allowedTypes = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "application/pdf" => "pdf"
    ];

    if (!isset($allowedTypes[$mime])) {
        respond(["error" => "Only JPG, PNG, and PDF files are allowed."], 400);
    }

    $uploadDir = __DIR__ . "/../uploads/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = bin2hex(random_bytes(16)) . "." . $allowedTypes[$mime];
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($file["tmp_name"], $destination)) {
        respond(["error" => "File upload failed."], 500);
    }

    $reference = "BG-" . date("Ymd") . "-" . strtoupper(bin2hex(random_bytes(3)));

    $stmt = $pdo->prepare(
        "INSERT INTO requests
        (reference_no, user_id, document_type, purpose, details, id_file)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    $stmt->execute([
        $reference,
        $_SESSION["user_id"],
        $document,
        $purpose,
        $details,
        $filename
    ]);

    $requestId = $pdo->lastInsertId();

    notifyUser(
        $_SESSION["user_id"],
        $requestId,
        "Your request $reference has been submitted successfully."
    );

    respond(["message" => "Request submitted successfully!"]);
}

if ($method === "PATCH") {

    requireRole("admin");

    $data = json_decode(file_get_contents("php://input"), true);

    $id = intval($data["id"] ?? 0);
    $status = $data["status"] ?? "";

    $allowedStatuses = [
        "Pending",
        "Under Review",
        "Approved",
        "Rejected",
        "Ready for Pickup",
        "Completed"
    ];

    if (!$id || !in_array($status, $allowedStatuses, true)) {
        respond(["error" => "Invalid request or status."], 400);
    }

    $stmt = $pdo->prepare(
        "SELECT user_id FROM requests WHERE id = ?"
    );
    $stmt->execute([$id]);
    $request = $stmt->fetch();

    if (!$request) {
        respond(["error" => "Request not found."], 404);
    }

    $stmt = $pdo->prepare(
        "UPDATE requests SET status = ? WHERE id = ?"
    );
    $stmt->execute([$status, $id]);

    notifyUser(
        $request["user_id"],
        $id,
        "Your document request status has been updated to: $status."
    );

    respond(["message" => "Request status updated."]);
}

respond(["error" => "Method not allowed."], 405);
