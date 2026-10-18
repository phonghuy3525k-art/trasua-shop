<?php
include 'config.php';
session_start();

// Sử dụng đúng tên session thống nhất với dashboard
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    header("Location: admin_dashboard.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    if (!empty($user) && !empty($pass)) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$user]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            if ($pass === $admin['password'] || password_verify($pass, $admin['password'])) {
                $_SESSION['admin_logged'] = true;
                $_SESSION['admin_user'] = $admin['username'];
                header("Location: admin_dashboard.php");
                exit;
            } else {
                $error = "Mật khẩu không chính xác!";
            }
        } else {
            $error = "Tài khoản quản trị không tồn tại!";
        }
    } else {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Nhập Quản Trị</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 320px; text-align: center; }
        .login-card h2 { margin-bottom: 20px; color: #333; }
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        .form-group input { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .btn { background: #ff7675; color: white; border: none; padding: 10px; width: 100%; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .btn:hover { background: #e84393; }
        .error { color: red; margin-bottom: 15px; font-size: 14px; }
        .back-link { display: block; margin-top: 15px; color: #666; text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Đăng Nhập Quản Trị</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Tài khoản:</label>
                <input type="text" name="username" required placeholder="Nhập tài khoản">
            </div>
            <div class="form-group">
                <label>Mật khẩu:</label>
                <input type="password" name="password" required placeholder="Nhập mật khẩu">
            </div>
            <button type="submit" name="login" class="btn">Đăng Nhập</button>
        </form>
        <a href="index.php" class="back-link">← Quay lại trang chủ</a>
    </div>
</body>
</html>