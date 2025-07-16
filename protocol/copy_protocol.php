<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Decode the JSON input from the request body
$data = json_decode(file_get_contents("php://input"), true);

$protocol_id = intval($data['protocol_id']);
$user_id = intval($data['user_id']);

try {
    $stmt = $pdo->prepare("SELECT * FROM protocols WHERE id = ?");
    $stmt->execute([$protocol_id]);
    $protocol = $stmt->fetch(PDO::FETCH_ASSOC);

    $new_protocol_name = $protocol['name'] . " (Copy)";
    $new_protocol_content = $protocol['content'];
    $row = $protocol['row'];
    $col = $protocol['col'];
    $owner = '';

    $stmt = $pdo->prepare("INSERT INTO protocols (name, row, col, owner, creater, content) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$new_protocol_name, $row, $col, $owner, $user_id, $new_protocol_content]);
    $new_protocol_id = $pdo->lastInsertId();

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    http_response_code(500);
}