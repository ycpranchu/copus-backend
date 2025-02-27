<?php
include '../db.php';
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Decode the JSON input from the request body
$data = json_decode(file_get_contents("php://input"), true);

$username = trim($data['username']);
$password = trim($data['password']);

try {
    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['password'])) {
            echo json_encode(["success" => true, "user_id" => $user['id'], 'user_password' => $password]);
            http_response_code(201);
        } else {
            echo json_encode(["success" => false, "message" => "密碼錯誤"]);
            http_response_code(201);
        }
    } else {
        echo json_encode(["success" => false, "message" => "帳號不存在"]);
        http_response_code(201);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "資料庫錯誤"]);
    http_response_code(201);
}
