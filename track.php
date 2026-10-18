<?php
include 'config.php';
$phone = isset($_GET['phone']) ? trim($_GET['phone']) : '';
$orders = [];

if ($phone) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE phone = ? ORDER BY created_at DESC");
    $stmt->execute([$phone]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tra Cứu Đơn Hàng</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #fdfdfd; color: #333; }
        header { background: #ff7675; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        header a { color: white; text-decoration: none; margin-left: 15px; font-weight: bold; }
        .container { max-width: 800px; margin: 30px auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .search-box { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-box input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .search-box button { background: #ff7675; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table th, table td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 14px; }
        table th { background: #f8f9fa; }
    </style>
</head>
<body>
    <header>
        <h2>🧋 Tiệm Trà Sữa Nhỏ</h2>
        <div>
            <a href="index.php">Trang Chủ</a>
            <a href="track.php">Tra Cứu Đơn Hàng</a>
        </div>
    </header>

    <div class="container">
        <h2>Tra Cứu Đơn Hàng Theo Số Điện Thoại</h2>
        <form method="GET" class="search-box">
            <input type="text" name="phone" placeholder="Nhập số điện thoại của bạn..." value="<?= htmlspecialchars($phone) ?>" required>
            <button type="submit">Tra Cứu</button>
        </form>

        <?php if($phone): ?>
            <h3>Kết quả cho SĐT: <?= htmlspecialchars($phone) ?></h3>
            <?php if(empty($orders)): ?>
                <p>Không tìm thấy đơn hàng nào với số điện thoại này.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Mã ĐH</th>
                        <th>Ngày Đặt</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Chi Tiết Món</th>
                    </tr>
                    <?php foreach($orders as $o): ?>
                    <tr>
                        <td>#<?= $o['id'] ?></td>
                        <td><?= $o['created_at'] ?></td>
                        <td><?= number_format($o['total_price']) ?> đ</td>
                        <td><strong><?= htmlspecialchars($o['status']) ?></strong></td>
                        <td>
                            <?php 
                                $stmt_i = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                                $stmt_i->execute([$o['id']]);
                                $items = $stmt_i->fetchAll(PDO::FETCH_ASSOC);
                                foreach($items as $i) {
                                    echo "- " . htmlspecialchars($i['product_name']) . " (SL: {$i['quantity']})<br>";
                                }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>