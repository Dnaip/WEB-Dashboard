<?php
session_start();
require_once 'db.php';

// บังคับให้ไฟล์นี้ส่งผลลัพธ์เป็น JSON
header('Content-Type: application/json');

// รับค่าตัวกรองที่ส่งมาจาก JavaScript (ถ้าไม่มีให้ตั้งค่าเริ่มต้นเป็น 'all')
$zone = isset($_GET['zone']) ? $_GET['zone'] : 'all';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// สร้างคำสั่ง SQL แบบยืดหยุ่น (Dynamic SQL)
$sql = "SELECT project_code, budget FROM projects WHERE 1=1";
$params = [];

if ($zone !== 'all') {
    $sql .= " AND zone = ?";
    $params[] = $zone;
}
if ($status !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status;
}

// เรียงลำดับตามวันที่สร้าง เพื่อดูแนวโน้ม
$sql .= " ORDER BY created_at ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// แยกข้อมูลออกเป็น 2 แกน (แกน X: ชื่อ/รหัส, แกน Y: งบประมาณ)
$labels = [];
$values = [];

foreach($results as $row) {
    $labels[] = $row['project_code'];
    $values[] = $row['budget'];
}

// ส่งข้อมูลกลับไปให้ JavaScript นำไปวาดกราฟ
echo json_encode([
    'labels' => $labels,
    'values' => $values
]);
?>