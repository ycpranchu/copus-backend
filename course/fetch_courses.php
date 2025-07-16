<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Decode the JSON input from the request body
$data = json_decode(file_get_contents("php://input"), true);
$user_id = intval($data['user_id']);

try {
    $my_stmt = $pdo->prepare("SELECT * FROM courses WHERE creater = ?");
    $my_stmt->execute([$user_id]);
    $my_courses = $my_stmt->fetchAll(PDO::FETCH_ASSOC);

    $shared_stmt = $pdo->prepare("SELECT * FROM courses WHERE FIND_IN_SET(?, owner) > 0");
    $shared_stmt->execute([$user_id]);
    $shared_courses = $shared_stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "my_courses" => $my_courses, "shared_courses" => $shared_courses]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    http_response_code(500);
}
