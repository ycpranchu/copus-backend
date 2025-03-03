<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Decode the JSON input from the request body
$data = json_decode(file_get_contents("php://input"), true);
$course_id = $data['course_id'];
$course_name = $data['course_name'];

$filename = "{$course_name}.csv";
$file_path = "exports/{$filename}";

try {
    $stmt = $pdo->prepare("SELECT record, record_at FROM courses_record WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $output = fopen($file_path, 'w');
    if (!$output) {
        throw new Exception("Failed to open file for writing");
    }

    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, ['記錄', '記錄時間']);

    foreach ($records as $row) {
        fputcsv($output, [$row['record'], $row['record_at']]);
    }

    fclose($output);

    // 設定 Headers 讓瀏覽器下載
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    exit;
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    http_response_code(500);
}
