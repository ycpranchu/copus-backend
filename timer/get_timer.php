<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(["success" => false, "message" => "Invalid request: ID missing"]);
    http_response_code(400);
    exit;
}

$id = $data['id'];
$timer = 30; // Default timer duration in seconds

try {
    $timerStmt = $pdo->prepare("SELECT setup_time FROM timer WHERE id = ?");
    $timerStmt->execute([$id]);
    $timerData = $timerStmt->fetch(PDO::FETCH_ASSOC);

    if ($timerData) {
        $setupTime = $timerData["setup_time"];
        $currentTime = time();

        if ($setupTime + $timer > $currentTime) {
            $remainTime = $timer - ($currentTime - $setupTime);
            echo json_encode(['timer_check' => false, 'timer_remain' => $remainTime]);
        } else {
            echo json_encode(['timer_check' => true]);
        }

        http_response_code(200);
    } else {
        echo json_encode(["success" => false, "message" => "No active timer found"]);
        http_response_code(200);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    http_response_code(400);
}
?>