<?php
include 'config.php';
session_start();

// Kiểm tra quyền đăng nhập
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Xử lý Thêm món
if (isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    $image = trim($_POST['image']);
    if(empty($image)) $image = 'https://images.unsplash.com/photo-1541658016709-82535e94bc69?w=300';

    $stmt = $pdo->prepare("INSERT INTO products (name, price, description, image) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $price, $description, $image]);
    header("Location: admin_dashboard.php?tab=products");
    exit;
}

// Xử lý Xóa món
if (isset($_GET['delete_product'])) {
    $id = $_GET['delete_product'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin_dashboard.php?tab=products");
    exit;
}

// Xử lý Sửa món
if (isset($_POST['edit_product'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    $image = trim($_POST['image']);

    $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ? WHERE id = ?");
    $stmt->execute([$name, $price, $description, $image, $id]);
    header("Location: admin_dashboard.php?tab=products");
    exit;
}

// Xử lý Cập nhật trạng thái đơn hàng
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    header("Location: admin_dashboard.php?tab=orders");
    exit;
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'orders';

// Lấy danh sách sản phẩm
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách đơn hàng
$orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Thống kê doanh thu theo tuần / tháng
$stats_type = isset($_GET['stats']) ? $_GET['stats'] : 'month';
if($stats_type == 'week') {
    // Thống kê 7 ngày gần nhất
    $stmt_stat = $pdo->query("SELECT DATE(created_at) as period, SUM(total_price) as revenue, COUNT(id) as total_orders FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at)");
} else {
    // Thống kê theo tháng trong năm hiện tại
    $stmt_stat = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as period, SUM(total_price) as revenue, COUNT(id) as total_orders FROM orders GROUP BY DATE_FORMAT(created_at, '%Y-%m') DESC");
}
$stats_data = $stmt_stat->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Quản Trị Tiệm Trà Sữa</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f6f9; color: #333; display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 250px; background: #2f3640; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { padding: 20px; margin: 0; background: #e84118; font-size: 18px; text-align: center; }
        .sidebar a { padding: 15px 20px; color: #f5f6fa; text-decoration: none; border-bottom: 1px solid #353b48; display: block; }
        .sidebar a:hover, .sidebar a.active { background: #353b48; color: #4cd137; }
        .content { flex: 1; padding: 25px; overflow-y: auto; }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: white; padding: 15px 20px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .card { background: white; padding: 20px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 14px; }
        table th { background: #f8f9fa; }
        .btn { background: #4cd137; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 13px;}
        .btn-danger { background: #e84118; }
        .form-control { padding: 6px; width: 100%; box-sizing: border-box; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Quản Trị Tiệm Trà</h2>
        <a href="admin_dashboard.php?tab=orders" class="<?= $tab=='orders'?'active':'' ?>">📦 Quản Lý Đơn Hàng</a>
        <a href="admin_dashboard.php?tab=products" class="<?= $tab=='products'?'active':'' ?>">🧋 Quản Lý Món</a>
        <a href="admin_dashboard.php?tab=stats" class="<?= $tab=='stats'?'active':'' ?>">📊 Thống Kê Doanh Thu</a>
        <a href="admin_logout.php" style="color: #ff7675;">🚪 Đăng Xuất</a>
    </div>

    <div class="content">
        <div class="header-top">
            <h3>Xin chào, <strong><?= htmlspecialchars($_SESSION['admin_user']) ?></strong></h3>
            <a href="index.php" target="_blank" class="btn" style="background:#718093;">Xem Website Khách</a>
        </div>

        <?php if($tab == 'orders'): ?>
            <div class="card">
                <h2>Lịch Sử & Trạng Thái Đơn Hàng</h2>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Khách Hàng</th>
                        <th>SĐT / Địa Chỉ</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Chi Tiết</th>
                        <th>Thao Tác</th>
                    </tr>
                    <?php foreach($orders as $o): ?>
                    <tr>
                        <td>#<?= $o['id'] ?></td>
                        <td><?= htmlspecialchars($o['customer_name']) ?></td>
                        <td><?= htmlspecialchars($o['phone']) ?><br><small><?= htmlspecialchars($o['address']) ?></small></td>
                        <td><?= number_format($o['total_price']) ?> đ</td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                <select name="status" class="form-control" style="margin-bottom:0; font-size:12px;">
                                    <option value="Chờ xác nhận" <?= $o['status']=='Chờ xác nhận'?'selected':'' ?>>Chờ xác nhận</option>
                                    <option value="Đang pha chế" <?= $o['status']=='Đang pha chế'?'selected':'' ?>>Đang pha chế</option>
                                    <option value="Đang giao hàng" <?= $o['status']=='Đang giao hàng'?'selected':'' ?>>Đang giao hàng</option>
                                    <option value="Hoàn thành" <?= $o['status']=='Hoàn thành'?'selected':'' ?>>Hoàn thành</option>
                                    <option value="Đã hủy" <?= $o['status']=='Đã hủy'?'selected':'' ?>>Đã hủy</option>
                                </select>
                        </td>
                        <td>
                            <?php 
                                $stmt_i = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                                $stmt_i->execute([$o['id']]);
                                foreach($stmt_i->fetchAll() as $item) {
                                    echo "• {$item['product_name']} x{$item['quantity']}<br>";
                                }
                            ?>
                        </td>
                        <td>
                                <button type="submit" name="update_status" class="btn" style="padding:4px 8px;">Cập nhật</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

        <?php elseif($tab == 'products'): ?>
            <div class="card">
                <h2>Thêm Món Mới</h2>
                <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; align-items: end;">
                    <div>
                        <label>Tên món</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div>
                        <label>Giá (VNĐ)</label>
                        <input type="number" name="price" class="form-control" required>
                    </div>
                    <div>
                        <label>Link ảnh (URL)</label>
                        <input type="text" name="image" class="form-control">
                    </div>
                    <div style="grid-column: span 3;">
                        <label>Mô tả</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                    <button type="submit" name="add_product" class="btn" style="grid-column: span 3; width: 150px;">Thêm Món</button>
                </form>
            </div>

            <div class="card">
                <h2>Danh Sách Món Trà Sữa</h2>
                <table>
                    <tr>
                        <th>Ảnh</th>
                        <th>Tên Món</th>
                        <th>Giá</th>
                        <th>Mô Tả</th>
                        <th>Hành động</th>
                    </tr>
                    <?php foreach($products as $p): ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($p['image']) ?>" width="50" height="50" style="object-fit:cover; border-radius:4px;"></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= number_format($p['price']) ?> đ</td>
                        <td><?= htmlspecialchars($p['description']) ?></td>
                        <td>
                            <!-- Form sửa nhanh nhỏ gọn -->
                            <button onclick="document.getElementById('edit-<?= $p['id'] ?>').style.display='block'" class="btn">Sửa</button>
                            <a href="admin_dashboard.php?delete_product=<?= $p['id'] ?>" class="btn btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa?')">Xóa</a>

                            <!-- Modal Sửa Món -->
                            <div id="edit-<?= $p['id'] ?>" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999;">
                                <div style="background:white; width:400px; margin: 100px auto; padding:20px; border-radius:6px;">
                                    <h3>Sửa Món: <?= htmlspecialchars($p['name']) ?></h3>
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <label>Tên món</label>
                                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($p['name']) ?>" required>
                                        <label>Giá</label>
                                        <input type="number" name="price" class="form-control" value="<?= $p['price'] ?>" required>
                                        <label>Link ảnh</label>
                                        <input type="text" name="image" class="form-control" value="<?= htmlspecialchars($p['image']) ?>">
                                        <label>Mô tả</label>
                                        <textarea name="description" class="form-control"><?= htmlspecialchars($p['description']) ?></textarea>
                                        <button type="submit" name="edit_product" class="btn">Lưu thay đổi</button>
                                        <button type="button" onclick="document.getElementById('edit-<?= $p['id'] ?>').style.display='none'" class="btn btn-danger">Đóng</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

        <?php elseif($tab == 'stats'): ?>
            <div class="card">
                <h2>Thống Kê Doanh Thu</h2>
                <div style="margin-bottom: 15px;">
                    <a href="admin_dashboard.php?tab=stats&stats=week" class="btn" style="background: <?= $stats_type=='week'?'#e84118':'#4cd137' ?>;">Theo Tuần (7 ngày qua)</a>
                    <a href="admin_dashboard.php?tab=stats&stats=month" class="btn" style="background: <?= $stats_type=='month'?'#e84118':'#4cd137' ?>;">Theo Tháng</a>
                </div>
                <table>
                    <tr>
                        <th>Thời Gian (<?= $stats_type=='week'?'Ngày':'Tháng' ?>)</th>
                        <th>Tổng Số Đơn Hàng</th>
                        <th>Tổng Doanh Thu</th>
                    </tr>
                    <?php 
                    $grand_total = 0;
                    foreach($stats_data as $row): 
                        $grand_total += $row['revenue'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['period']) ?></td>
                        <td><?= $row['total_orders'] ?> đơn</td>
                        <td><strong><?= number_format($row['revenue']) ?> đ</strong></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <h3 style="margin-top: 15px; color: #e84118;">Tổng doanh thu toàn bộ kỳ: <?= number_format($grand_total) ?> đ</h3>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>