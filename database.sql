CREATE DATABASE IF NOT EXISTS trasua_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE trasua_shop;

-- Bảng Quản trị viên
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

INSERT INTO admins (username, password) VALUES ('admin', '123') 
ON DUPLICATE KEY UPDATE username=username;

-- Bảng Sản phẩm (Đồ uống)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NOT NULL
);

INSERT INTO products (name, price, image) VALUES 
('Trà sữa truyền thống', 25000, 'https://images.unsplash.com/photo-1541658016709-82535e94bc69?w=500'),
('Trà sữa Matcha', 30000, 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=500'),
('Trà sữa khoai môn', 28000, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=500'),
('Hồng trà kem cheese', 35000, 'https://images.unsplash.com/photo-1525385133512-2f3bdd039a54?w=500')
ON DUPLICATE KEY UPDATE name=name;

-- Bảng Đơn hàng
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Chờ xác nhận',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng Chi tiết đơn hàng
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);