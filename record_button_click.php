<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['protocolId'], $data['selectedButtons'], $data['message'])) {
    echo json_encode(["success" => false, "message" => "Invalid data received"]);
    exit;
}

$protocolId = intval($data['protocolId']);
$selectedButtons = $data['selectedButtons'];
$message = $data['message'];

if (!is_array($selectedButtons) || empty($selectedButtons)) {
    echo json_encode(["success" => false, "message" => "No buttons selected"]);
    exit;
}

$selectedButtons = array_filter($selectedButtons, function ($item) {
    return !empty($item['img_name']);
});
$imgNames = array_map(function ($item) {
    return $item['img_name'];
}, $selectedButtons);

$imgNamesString = implode(',', $imgNames);

try {
    $stmt = $pdo->prepare("INSERT INTO submit_records (protocol_id, img_name, message) VALUES (:protocolId, :imgNames, :message)");
    $stmt->execute([
        'protocolId' => $protocolId,
        'imgNames' => $imgNamesString,
        'message' => $message
    ]);
    echo json_encode(["success" => true, "message" => "Submit recorded"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>