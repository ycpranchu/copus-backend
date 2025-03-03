<?php
include '../db.php';
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Decode the JSON input from the request body
$data = json_decode(file_get_contents("php://input"), true);

$user_id = intval($data['user_id']); // 確保是整數
$username = trim($data['username']);
$password = password_hash($data['password'], PASSWORD_DEFAULT); // 加密密碼


try {
    // 檢查 Username 是否已經註冊
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $checkStmt->execute([$username]);

    if ($checkStmt->fetch()) {
        echo json_encode(["success" => false, "message" => "使用者名稱已被註冊"]);
        http_response_code(201);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
    $stmt->execute([$username, $password, $user_id]);

    echo json_encode(["success" => true, "message" => "更新成功！"]);
    http_response_code(200);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "資料庫錯誤: " . $e->getMessage()]);
    http_response_code(500);
}
