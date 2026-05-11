<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: register.html', true, 302);
    exit;
}

$fullName = trim((string) ($_POST['fullName'] ?? ''));
$address = trim((string) ($_POST['address'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($fullName === '' || $address === '' || $phone === '' || $email === '' || $username === '' || $password === '') {
    http_response_code(400);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Register</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="pages.css"></head><body><main class="page-shell lt-shell" style="padding:24px;"><p>Please complete all fields.</p><p><a href="register.html">Back to register</a></p></main></body></html>';
    exit;
}

$con = @mysqli_connect('localhost', 'root', '', 'car_sales');
if ($con === false) {
    http_response_code(503);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Register</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="pages.css"></head><body><main class="page-shell lt-shell" style="padding:24px;"><p>Database connection failed.</p><p><a href="register.html">Back</a></p></main></body></html>';
    exit;
}

mysqli_set_charset($con, 'utf8mb4');
$hash = password_hash($password, PASSWORD_DEFAULT);
if ($hash === false) {
    mysqli_close($con);
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Register</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="pages.css"></head><body><main class="page-shell lt-shell" style="padding:24px;"><p>Could not process password.</p><p><a href="register.html">Back</a></p></main></body></html>';
    exit;
}

$sql = 'INSERT INTO sellers (fullName, address, phone, email, username, password) VALUES (?, ?, ?, ?, ?, ?)';
$stmt = mysqli_prepare($con, $sql);
if ($stmt === false) {
    mysqli_close($con);
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Register</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="pages.css"></head><body><main class="page-shell lt-shell" style="padding:24px;"><p>Server error.</p><p><a href="register.html">Back</a></p></main></body></html>';
    exit;
}

mysqli_stmt_bind_param($stmt, 'ssssss', $fullName, $address, $phone, $email, $username, $hash);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
mysqli_close($con);

if (!$ok) {
    http_response_code(400);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Register</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="pages.css"></head><body><main class="page-shell lt-shell" style="padding:24px;"><h1 style="font-size:1.1rem;">Could not register</h1><p>Username may already exist, or data did not validate.</p><p><a href="register.html">Back to register</a></p></main></body></html>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="pages.css">
</head>
<body>
  <script src="auth-toast.js"></script>
  <script>
    AuthFlowToast.show(
      "Registration successful.",
      "Redirecting to the sign-in page.",
      1000,
      function () {
        window.location.replace("login.html");
      }
    );
  </script>
</body>
</html>
