<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$course_id = $data['course_id'];
$timer = $data['timer'];

try {
    $timerStmt = $pdo->prepare("SELECT setup_time FROM timers WHERE course_id = ?");
    $timerStmt->execute([$course_id]);
    $timerData = $timerStmt->fetch(PDO::FETCH_ASSOC);
    $setupTime = time();

    if ($timerData) {
        $remaining_time = ($timerData['setup_time'] + $timer) - time();

        if ($remaining_time <= 0) {
            $updateStmt = $pdo->prepare("UPDATE timers SET setup_time = ? WHERE course_id = ?");
            $updateStmt->execute([$setupTime, $course_id]);
            echo json_encode(["success" => true, "remaining_time" => $timer]);
        } else {
            echo json_encode(["success" => true, "remaining_time" => $remaining_time]);
        }
    } else {
        $insertStmt = $pdo->prepare("INSERT INTO timers (course_id, setup_time) VALUES (?, ?)");
        $insertStmt->execute([$course_id, $setupTime]);
        echo json_encode(["success" => true, "remaining_time" => $timer]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    http_response_code(400);
}
