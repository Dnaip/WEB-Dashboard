<?php
session_start();
require_once 'db.php';

// เช็กสิทธิ์ (ถ้าต้องการให้เฉพาะ Admin โหลดได้ก็เปิดโค้ดนี้ไว้)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 1. ตั้งค่า Header เพื่อบังคับให้เบราว์เซอร์ดาวน์โหลดไฟล์
$filename = "EEC_Investment_Report_" . date('Ymd') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// 2. เปิดไฟล์เขียน (Output Stream)
$output = fopen('php://output', 'w');

// 3. เทคนิคพิเศษ: ใส่ UTF-8 BOM เพื่อให้ MS Excel อ่านภาษาไทยได้ 100% โดยไม่เป็นภาษาต่างดาว
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// 4. สร้างหัวตาราง (Column Headers)
fputcsv($output, ['รหัสโครงการ', 'ชื่อโครงการ/บริษัท', 'พื้นที่ (Zone)', 'งบประมาณ (ล้านบาท)', 'สถานะ', 'วันที่บันทึก']);

// 5. ดึงข้อมูลจากฐานข้อมูล (คุณสามารถรับค่า $_GET มาทำ Filter เหมือนหน้า API ได้ด้วยนะครับ)
$stmt = $pdo->query("SELECT project_code, project_name, zone, budget, status, created_at FROM projects ORDER BY id DESC");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6. วนลูปนำข้อมูลใส่ลงไปทีละบรรทัด
foreach ($projects as $row) {
    fputcsv($output, [
        $row['project_code'],
        $row['project_name'],
        $row['zone'],
        $row['budget'],
        $row['status'],
        $row['created_at']
    ]);
}

// 7. ปิดการเชื่อมต่อ
fclose($output);
exit();
?>