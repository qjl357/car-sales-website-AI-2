<?php
session_start();

// 验证是否登录
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$seller_username = $_SESSION['username'];

$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'car_sales';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    header('Location: publish.php?error=1');
    exit;
}

$conn->set_charset('utf8mb4');

$color = $_POST['color'] ?? '';
$model = $_POST['model'] ?? '';
$year = $_POST['year'] ?? '';
$location = $_POST['location'] ?? '';
$price = $_POST['price'] ?? '';

$hasError = false;
if (empty($color)) $hasError = true;
if (empty($model)) $hasError = true;
if (empty($year) || !preg_match('/^\d{4}$/', $year)) $hasError = true;
if (empty($location)) $hasError = true;
if (empty($price) || !preg_match('/^\d+(\.\d+)?$/', $price)) $hasError = true;

if ($hasError) {
    header('Location: publish.php?error=1');
    exit;
}

// 处理图片上传
$image_path = '';
if (isset($_FILES['vehicle_image']) && $_FILES['vehicle_image']['error'] == 0) {
    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $ext = pathinfo($_FILES['vehicle_image']['name'], PATHINFO_EXTENSION);
    $new_filename = time() . '_' . uniqid() . '.' . $ext;
    $image_path = $upload_dir . $new_filename;
    move_uploaded_file($_FILES['vehicle_image']['tmp_name'], $image_path);
}

// 插入数据（包含 seller_username）
$sql = "INSERT INTO vehicles (color, model, year, location, price, image_path, seller_username) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssissss', $color, $model, $year, $location, $price, $image_path, $seller_username);

if ($stmt->execute()) {
    header('Location: publish.php?success=1');
} else {
    header('Location: publish.php?error=1');
}

$stmt->close();
$conn->close();
?>