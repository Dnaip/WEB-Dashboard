<?php
require_once 'db.php';

$username = 'admin';
$password = 'admin123'; // รหัสผ่านตั้งต้น
// เข้ารหัสผ่านด้วยมาตรฐาน password_hash
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$name = 'ผู้ดูแลระบบ EEC';

// เช็คว่ามี admin อยู่หรือยัง
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->rowCount() == 0) {
    $insert = $pdo->prepare("INSERT INTO users (username, password, name) VALUES (?, ?, ?)");
    $insert->execute([$username, $hashed_password, $name]);
    echo "สร้างบัญชี Admin สำเร็จ!<br>Username: <b>admin</b> <br>Password: <b>admin123</b><br><br>";
    echo "<a href='login.php'>ไปหน้าเข้าสู่ระบบ</a>";
} else {
    echo "มีบัญชี admin อยู่ในระบบแล้ว <a href='login.php'>ไปหน้าเข้าสู่ระบบ</a>";
}
?>