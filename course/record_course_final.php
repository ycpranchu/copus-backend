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

    $all_words = [];
    foreach ($lines as $line) {
        $words = explode(",", $line);
        $all_words = array_merge($all_words, $words);
    }

    $unique_words = array_unique($all_words); // 移除重複詞
    $record = implode(",", $unique_words); // 重新合併為字串

    $stmt = $pdo->prepare("INSERT INTO courses_record (course_id, record) VALUES (?, ?)");
    $stmt->execute([$course_id, $record]);

    file_put_contents($log_file, '');
    echo json_encode(["success" => true, "message" => "Record saved successfully"]);
} catch (Exception $e) {
    error_log("Error processing log: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error processing log"]);
}
