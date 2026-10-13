<?php
include 'config.php';
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_user'] = $admin['username'];
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Sai tên đăng nhập hoặc mật khẩu!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Nhập Quản Trị</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 300px; }
        .login-box h2 { text-align: center; margin-bottom: 20px; color: #333; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn { width: 100%; background: #ff7675; color: white; border: none; padding: 10px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .error { color: red; font-size: 14px; margin-bottom: 10px; text-align: center; }
        .back { text-align: center; margin-top: 10px; display: block; color: #666; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Quản Trị Đăng Nhập</h2>
        <?php if($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Tài khoản</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Đăng Nhập</button>
        </form>
        <a href="index.php" class="back">← Quay lại trang chủ</a>
    </div>
</body>
</html>