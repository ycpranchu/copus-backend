<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Decode the JSON input from the request body
$data = json_decode(file_get_contents("php://input"), true);

$protocol_id = intval($data['protocol_id']);

try {
    // 1️⃣ 取得所有使用該 `protocol_id` 的 `course_id`
    $stmt = $pdo->prepare("SELECT id FROM courses WHERE protocol_id = ?");
    $stmt->execute([$protocol_id]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($courses) {
        // 2️⃣ 刪除 `course_id` 對應的 `course_record` / `timer`
        $course_ids = array_column($courses, 'id');
        $course_placeholders = implode(',', array_fill(0, count($course_ids), '?'));

        $deleteCourseRecordStmt = $pdo->prepare("DELETE FROM courses_record WHERE course_id IN ($course_placeholders)");
        $deleteCourseRecordStmt->execute($course_ids);

        $deleteTimerStmt = $pdo->prepare("DELETE FROM timers WHERE course_id IN ($course_placeholders)");
        $deleteTimerStmt->execute($course_ids);

        // 3️⃣ 刪除 `courses`
        $deleteCoursesStmt = $pdo->prepare("DELETE FROM courses WHERE protocol_id = ?");
        $deleteCoursesStmt->execute([$protocol_id]);
    }

    // 4️⃣ 刪除 `protocol`
    $deleteProtocolStmt = $pdo->prepare("DELETE FROM protocols WHERE id = ?");
    $deleteProtocolStmt->execute([$protocol_id]);

    echo json_encode(["success" => true, "message" => "Protocol and related data deleted successfully"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    http_response_code(500);
}