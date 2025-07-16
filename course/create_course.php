<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Decode the JSON input from the request body
$data = json_decode(file_get_contents("php://input"), true);

$user_id = intval($data['user_id']);
$course_name = trim($data['course_name']);
$protocol_id = intval($data['protocol_id']);
$owners_id = trim($data['owners_id']);

try {
    $stmt = $pdo->prepare("INSERT INTO courses (name, protocol_id, creater, owner, active, activer, created_at) VALUES (?, ?, ?, ?, 0, -1, NOW())");
    $stmt->execute([$course_name, $protocol_id, $user_id, $owners_id]);
    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    http_response_code(500);
}
