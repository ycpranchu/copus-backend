<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Decode the JSON input from the request body
$data = json_decode(file_get_contents('php://input'), true);

$id = intval($data['id']);
$name = trim($data['name']);
$row = intval($data['row']);
$col = intval($data['col']);
$user_id = intval($data['user_id']);
$owners_id = $data['owners_id'];
$content = $data['content'];

try {
    // 檢查是否已存在該 Protocol
    $checkStmt = $pdo->prepare('SELECT id FROM protocols WHERE id = ?');
    $checkStmt->execute([$id]);
    $existingProtocol = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingProtocol) {
        $protocolId = $id;
    } else {
        $insertStmt = $pdo->prepare('INSERT INTO protocols (name, row, col, owner, creater, content) VALUES (?, ?, ?, ?, ?, ?)');
        $insertStmt->execute([$name, $row, $col, $owners_id, $user_id, json_encode($content)]);
        $protocolId = $pdo->lastInsertId();
    }

    // 設置存儲圖片的資料夾
    $protocolFolder = "uploads/$protocolId";

    if (!file_exists($protocolFolder) && !mkdir($protocolFolder, 0777, true)) {
            die(json_encode(["success" => false, 'message' => 'Failed to create directory: ' . $protocolFolder]));
    }

    // 處理每個 Content，並存儲圖片
    foreach ($content as $index => $item) {
        if (!empty($item['img_path']) && preg_match('/^data:image\/(\w+);base64,/', $item['img_path'], $matches)) {
            $imageType = strtolower($matches[1]);
            $imageType = in_array($imageType, ['jpg', 'jpeg', 'png', 'gif']) ? $imageType : 'jpg';
            $imageData = substr($item['img_path'], strpos($item['img_path'], ',') + 1);
            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                echo json_encode(["success" => false, 'message' => 'Invalid Base64 image data']);
                continue;
            }

            $imageFilename = $index . "." . $imageType;
            $imagePath = "$protocolFolder/$imageFilename";

            if (file_put_contents($imagePath, $imageData) === false) {
                die(json_encode(["success" => false, 'message' => 'Failed to save image: ' . $imagePath]));
            }

            // 更新 JSON 內容的 image 欄位
            $content[$index]['img_path'] = $imagePath;
        }
    }

    // Update protocol into the database
    $stmt = $pdo->prepare('UPDATE protocols SET name = ?, row = ?, col = ?, owner = ?, content = ? WHERE id = ?');
    $stmt->execute([$name, $row, $col, $owners_id, json_encode($content), $id]);

    // Return a success message
    echo json_encode(["success" => true, 'protocol_id' => $protocolId]);
    http_response_code(200);
} catch (PDOException $e) {
    echo json_encode(["success" => false, 'message' => 'Database error: ' . $e->getMessage()]);
    http_response_code(500);
}
