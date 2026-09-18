<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

// รับค่าปีที่ส่งมา (ถ้าไม่ส่งมาหรือเลือก 'all' ให้ดึงทั้งหมด)
$year = isset($_GET['year']) ? $_GET['year'] : 'all';

$whereClause = "";
$params = [];
if ($year !== 'all') {
    // กรองเฉพาะข้อมูลที่ created_at ตรงกับปีที่เลือก
    $whereClause = "WHERE YEAR(created_at) = ?";
    $params[] = $year;
}

// 1. ดึงยอดรวม (KPIs)
$stmtKpi = $pdo->prepare("SELECT COUNT(*) as total_projects, COALESCE(SUM(budget), 0) as total_budget FROM projects $whereClause");
$stmtKpi->execute($params);
$kpi = $stmtKpi->fetch(PDO::FETCH_ASSOC);

// 2. ดึงข้อมูลกราฟ (แยกมูลค่าการลงทุนตามพื้นที่ Zone)
$stmtChart = $pdo->prepare("SELECT zone, COALESCE(SUM(budget), 0) as budget FROM projects $whereClause GROUP BY zone");
$stmtChart->execute($params);
$chartData = $stmtChart->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$values = [];
foreach($chartData as $row) {
    $labels[] = $row['zone'];
    $values[] = $row['budget'];
}

// 3. ดึงข้อมูลกราฟโดนัท (แยกตามสถานะโครงการ)
$stmtDoughnut = $pdo->prepare("SELECT status, COUNT(*) as count FROM projects $whereClause GROUP BY status");
$stmtDoughnut->execute($params);
$doughnutData = $stmtDoughnut->fetchAll(PDO::FETCH_ASSOC);

$d_labels = [];
$d_values = [];
foreach($doughnutData as $row) {
    $d_labels[] = $row['status'];
    $d_values[] = $row['count'];
}

// ... โค้ดส่วนที่ 1, 2, 3 เดิม ...

// 4. ดึงข้อมูลพิกัดแผนที่ (เอาเฉพาะที่มีพิกัด)
$stmtMap = $pdo->prepare("SELECT project_code, project_name, budget, status, lat, lng FROM projects $whereClause AND lat IS NOT NULL");
$stmtMap->execute($params);
$mapData = $stmtMap->fetchAll(PDO::FETCH_ASSOC);

// อัปเดต json_encode เพิ่ม map_data เข้าไป
echo json_encode([
    'total_projects' => number_format($kpi['total_projects']),
    'total_budget' => number_format($kpi['total_budget'], 2),
    'chart_labels' => $labels,
    'chart_values' => $values,
    'doughnut_labels' => $d_labels, 
    'doughnut_values' => $d_values,
    'map_data' => $mapData // ข้อมูลสำหรับปักหมุด
]);
?>
