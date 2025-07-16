<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

header(header: 'Content-Type: application/json');

// Decode the JSON input from the request body
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['img_path'])) {
    echo json_encode(["success" => false, "message" => "Invalid data received"]);
    exit;
}

$img_path = $data['img_path'];

try {
    $image_data = file_get_contents($img_path);
    $base64 = base64_encode($image_data);

    // Ensure the function decodeImage exists and processes the file
    if ($base64) {
        echo json_encode(["success" => true, "base64" => $base64]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to decode the image"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Failed to decode the image" . $e->getMessage()]);
}
?>