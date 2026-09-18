<?php
// กำหนด Header ให้ระบบรู้ว่านี่คือหน้า API ที่ส่งออกข้อมูลเป็น JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once 'db.php';

// ---------------------------------------------------------
// 1. ระบบรักษาความปลอดภัยด้วย API Key
// ---------------------------------------------------------
$valid_api_key = "EWC-SECRET-KEY-2026"; // กุญแจลับสำหรับดึงข้อมูล (เปลี่ยนได้ตามต้องการ)
$request_key = isset($_GET['key']) ? $_GET['key'] : '';

if ($request_key !== $valid_api_key) {
    http_response_code(401); // 401 Unauthorized (ไม่มีสิทธิ์เข้าถึง)
    echo json_encode([
        "status" => "error", 
        "message" => "Access Denied. Invalid API Key."
    ]);
    exit();
}

// ---------------------------------------------------------
// 2. รับค่าพารามิเตอร์สำหรับกรองข้อมูล (Filter)
// ---------------------------------------------------------
$zone = isset($_GET['zone']) ? trim($_GET['zone']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

// สร้างคำสั่ง SQL พื้นฐาน
$sql = "SELECT project_code, project_name, zone, budget, status, lat, lng, created_at FROM projects WHERE 1=1";
$params = [];

// ถ้ามีการส่งค่า zone มา ให้กรองตามพื้นที่
if ($zone !== '') {
    $sql .= " AND zone = ?";
    $params[] = $zone;
}

// ถ้ามีการส่งค่า status มา ให้กรองตามสถานะ
if ($status !== '') {
    $sql .= " AND status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY id DESC"; // เรียงจากโครงการล่าสุดลงไป

// ---------------------------------------------------------
// 3. ดึงข้อมูลและส่งออกเป็น JSON
// ---------------------------------------------------------
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($projects) > 0) {
        http_response_code(200); // 200 OK
        echo json_encode([
            "status" => "success",
            "total_records" => count($projects),
            "data" => $projects
        ], JSON_UNESCAPED_UNICODE); // ใช้ JSON_UNESCAPED_UNICODE เพื่อให้ภาษาไทยไม่เพี้ยน
    } else {
        http_response_code(404); // 404 Not Found
        echo json_encode([
            "status" => "error", 
            "message" => "ไม่พบข้อมูลโครงการที่ค้นหา"
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (PDOException $e) {
    http_response_code(500); // 500 Internal Server Error
    echo json_encode([
        "status" => "error", 
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>