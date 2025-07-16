<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Decode the JSON input from the request body
$data = json_decode(file_get_contents("php://input"), true);
$course_id =  $data['course_id'];
$log_file = "logs/$course_id.txt";

try {
    $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $mergedRecords = [];

    foreach ($lines as $line) {
        $parts = explode(",", $line);

        $username = $parts[0];  // 使用者名稱
        $message = $parts[1];   // 訊息
        $status = array_slice($parts, 2); // 取得狀態 (0,1)

        $mergedRecords[$username]["message"][] = $message;

        // 合併狀態（邏輯 OR 運算：如果有 1 則結果為 1）
        foreach ($status as $index => $value) {
            $mergedRecords[$username]["status"][$index] |= intval($value);
        }
    }

    // 轉換成儲存格式
    $finalRecords = [];
    foreach ($mergedRecords as $username => $data) {
        $mergedMessage = implode(" ", array_unique($data["message"])); // 合併訊息並去重複
        $mergedStatus = implode(",", $data["status"]); // 合併狀態
        $finalRecords[] = "$username,$mergedMessage,$mergedStatus";
    }

    $stmt = $pdo->prepare("INSERT INTO courses_record (course_id, record) VALUES (?, ?)");

    foreach ($mergedRecords as $username => $data) {
        $mergedMessage = implode(" ", array_unique($data["message"])); // 合併訊息並去重複
        $mergedStatus = implode(",", $data["status"]); // 合併狀態
        $recordString = "$username,$mergedMessage,$mergedStatus";

        $stmt->execute([$course_id, $recordString]); // **分行插入**
    }

    // 清空 log 檔案
    file_put_contents($log_file, '');
    
    echo json_encode(["success" => true, "message" => "Record saved successfully"]);
} catch (Exception $e) {
    error_log("Error processing log: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error processing log"]);
}
