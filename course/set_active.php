<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Decode the JSON input from the request body
$data = json_decode(file_get_contents("php://input"), true);

$course_id = intval($data['course_id']);
$active = intval($data['active']);

try {
    if ($active == 1) {
        $user_id = intval($data['user_id']);
        $stmt = $pdo->prepare("UPDATE courses SET active = ?, activer = ? WHERE id = ?");
        $stmt->execute([$active, $user_id, $course_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE courses SET active = ? WHERE id = ?");
        $stmt->execute([$active, $course_id]);
    }

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    http_response_code(500);
}
