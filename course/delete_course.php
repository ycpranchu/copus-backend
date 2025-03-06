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
    // 1️⃣ 刪除 `courses_record` 中的相關記錄
    $deleteCourseRecordStmt = $pdo->prepare("DELETE FROM courses_record WHERE course_id = ?");
    $deleteCourseRecordStmt->execute([$course_id]);

    // 2️⃣ 刪除 `timers` 中的相關計時器記錄
    $deleteTimerStmt = $pdo->prepare("DELETE FROM timers WHERE course_id = ?");
    $deleteTimerStmt->execute([$course_id]);

    // 3️⃣ 刪除 `courses` 中的記錄
    $deleteCoursesStmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $deleteCoursesStmt->execute([$course_id]);

    echo json_encode(["success" => true, "message" => "Course and related data deleted successfully"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    http_response_code(500);
}
