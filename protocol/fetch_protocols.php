<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Decode the JSON input from the request body
$data = json_decode(file_get_contents("php://input"), true);

if ($data['fetch_type'] == 'protocol') {
    $user_id = intval($data['user_id']);

    try {
        $my_stmt = $pdo->prepare("SELECT * FROM protocols WHERE creater = ?");
        $my_stmt->execute([$user_id]);
        $my_protocols = $my_stmt->fetchAll(PDO::FETCH_ASSOC);

        $shared_stmt = $pdo->prepare("SELECT * FROM protocols WHERE FIND_IN_SET(?, owner) > 0");
        $shared_stmt->execute([$user_id]);
        $shared_protocols = $shared_stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "my_protocols" => $my_protocols, "shared_protocols" => $shared_protocols]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
        http_response_code(500);
    }
} else if ($data['fetch_type'] == 'course') {
    $user_id = intval($data['user_id']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM protocols WHERE creater = ? OR FIND_IN_SET(?, owner) > 0");
        $stmt->execute([$user_id, $user_id]);
        $protocols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "protocols" => $protocols]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
        http_response_code(500);
    }
} else if ($data['fetch_type'] == 'course_id') {
    $course_id = intval($data['course_id']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$course_id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        $protocol_id = $course['protocol_id'];

        $stmt = $pdo->prepare("SELECT * FROM protocols WHERE id = ?");
        $stmt->execute([$protocol_id]);
        $protocol = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "protocol" => $protocol, "course_name" => $course['name'], "active" => $course['active']]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
        http_response_code(500);
    }
} else {
    $protocol_id = intval($data['protocol_id']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM protocols WHERE id = ?");
        $stmt->execute([$protocol_id]);
        $protocol = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "protocol" => $protocol]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
        http_response_code(500);
    }
}
