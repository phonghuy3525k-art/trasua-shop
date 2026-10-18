<?php
include 'config.php';
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_POST['add_to_cart'])) {
    $id = $_POST['product_id'];
    $name = $_POST['product_name'];
    $price = $_POST['product_price'];
    $qty = (int)$_POST['quantity'];

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity'] += $qty;
    } else {
        $_SESSION['cart'][$id] = [
            'name' => $name,
            'price' => $price,
            'quantity' => $qty
        ];
    }
    header("Location: index.php");
    exit;
}

if (isset($_GET['remove'])) {
    $id = $_GET['remove'];
    unset($_SESSION['cart'][$id]);
    header("Location: index.php");
    exit;
}

$order_success = "";
if (isset($_POST['checkout'])) {
    $customer_name = trim($_POST['customer_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if (!empty($customer_name) && !empty($phone) && !empty($address) && count($_SESSION['cart']) > 0) {
        $total_price = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total_price += $item['price'] * $item['quantity'];
        }

        $stmt = $pdo->prepare("INSERT INTO orders (customer_name, phone, address, total_price, status) VALUES (?, ?, ?, ?, 'Chờ xác nhận')");
        $stmt->execute([$customer_name, $phone, $address, $total_price]);
        $order_id = $pdo->lastInsertId();

        $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_name, price, quantity) VALUES (?, ?, ?, ?)");
        foreach ($_SESSION['cart'] as $item) {
            $stmt_item->execute([$order_id, $item['name'], $item['price'], $item['quantity']]);
        }

        $_SESSION['cart'] = [];
        $order_success = "Đặt hàng thành công! Mã đơn hàng của bạn là #$order_id. Bạn có thể dùng số điện thoại để tra cứu.";
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ?");
    $stmt->execute(["%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM products");
}
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tiệm Trà Sữa Nhỏ</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #fdfdfd; color: #333; }
        header { background: #ff7675; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        header a { color: white; text-decoration: none; margin-left: 15px; font-weight: bold; }
        .container { max-width: 1200px; margin: 20px auto; padding: 0 15px; display: flex; gap: 20px; }
        .main-content { flex: 2; }
        .sidebar { flex: 1; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .search-box { margin-bottom: 20px; display: flex; gap: 10px; }
        .search-box input { flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .search-box button, .btn { background: #ff7675; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 15px; }
        .product-card { background: white; border: 1px solid #eee; border-radius: 8px; padding: 15px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .product-card img { width: 100%; height: 140px; object-fit: cover; border-radius: 6px; }
        .product-card h3 { margin: 10px 0 5px; font-size: 16px; }
        .product-card p { color: #e84393; font-weight: bold; margin-bottom: 10px; }
        .product-card small { color: #666; display: block; margin-bottom: 10px; height: 35px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 14px; }
        .alert { background: #55efc4; color: #00b894; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>
    <header>
        <h2>🧋 Tiệm Trà Sữa Nhỏ</h2>
        <div>
            <a href="index.php">Trang Chủ</a>
            <a href="track.php">Tra Cứu Đơn Hàng</a>
            <a href="admin_login.php">Quản Trị</a>
        </div>
    </header>

    <div class="container">
        <div class="main-content">
            <form method="GET" class="search-box">
                <input type="text" name="search" placeholder="Tìm tên món trà sữa..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Tìm kiếm</button>
            </form>

            <?php if($order_success): ?>
                <div class="alert"><?= $order_success ?></div>
            <?php endif; ?>

            <h2>Menu Đồ Uống</h2>
            <div class="product-grid">
                <?php foreach($products as $p): ?>
                    <div class="product-card">
                        <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                        <h3><?= htmlspecialchars($p['name']) ?></h3>
                        <small><?= htmlspecialchars($p['description']) ?></small>
                        <p><?= number_format($p['price']) ?> đ</p>
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="product_name" value="<?= htmlspecialchars($p['name']) ?>">
                            <input type="hidden" name="product_price" value="<?= $p['price'] ?>">
                            <input type="number" name="quantity" value="1" min="1" style="width: 40px; text-align: center;">
                            <button type="submit" name="add_to_cart" class="btn">Thêm</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="sidebar">
            <h3>Giỏ Hàng Của Bạn</h3>
            <?php if(empty($_SESSION['cart'])): ?>
                <p>Giỏ hàng trống.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Tên</th>
                        <th>SL</th>
                        <th>Giá</th>
                        <th></th>
                    </tr>
                    <?php 
                    $total = 0;
                    foreach($_SESSION['cart'] as $id => $item): 
                        $sub = $item['price'] * $item['quantity'];
                        $total += $sub;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= number_format($sub) ?></td>
                        <td><a href="index.php?remove=<?= $id ?>" style="color:red; text-decoration:none;">X</a></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <p><strong>Tổng cộng: <?= number_format($total) ?> đ</strong></p>

                <h3 style="margin-top:20px;">Thông Tin Nhận Hàng</h3>
                <form method="POST">
                    <div style="margin-bottom: 10px;">
                        <input type="text" name="customer_name" placeholder="Họ tên" required style="width:100%; padding: 6px; box-sizing: border-box;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <input type="text" name="phone" placeholder="Số điện thoại" required style="width:100%; padding: 6px; box-sizing: border-box;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <textarea name="address" placeholder="Địa chỉ nhận hàng" required style="width:100%; padding: 6px; box-sizing: border-box;"></textarea>
                    </div>
                    <button type="submit" name="checkout" class="btn" style="width:100%; padding: 10px;">Xác Nhận Đặt Hàng</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>