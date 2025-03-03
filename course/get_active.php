<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Decode the JSON input from the request body
$data = json_decode(file_get_contents("php://input"), true);
$course_id = intval($data['course_id']);

try {
    $stmt = $pdo->prepare("SELECT active from courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "active" => $result['active']]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    http_response_code(500);
}
