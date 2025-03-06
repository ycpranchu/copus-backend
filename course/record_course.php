<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Decode the JSON input from the request body
$data = json_decode(file_get_contents("php://input"), true);

$user_name = $data['user_name'];
$course_id =  $data['course_id'];
$message = $data['message'];
$selectedStatus = $data['selectedStatus'];

$log_file = "logs/$course_id.txt";

try {
    file_put_contents($log_file, $user_name . ',' . $message . ','. $selectedStatus . PHP_EOL, FILE_APPEND | LOCK_EX);
    echo json_encode(["success" => true, "message" => "Log recorded successfully"]);
} catch (Exception $e) {
    error_log("Error writing log: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error recording log"]);
}
