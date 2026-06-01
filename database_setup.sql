-- ShopNest Database Setup
-- Run this SQL in your MySQL/phpMyAdmin to create the database and tables.

CREATE DATABASE IF NOT EXISTS shopnest CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shopnest;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    category VARCHAR(100) DEFAULT 'General',
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart Table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Sample Admin User (password: admin123)
INSERT INTO users (username, email, password, role) VALUES
('Admin', 'admin@shopnest.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Sample Products
INSERT INTO products (name, price, description, category, image) VALUES
('Wireless Headphones', 49.99, 'Premium sound quality with noise cancellation and 20-hour battery life.', 'Electronics', 'product1.jpg'),
('Running Shoes', 79.99, 'Lightweight and breathable shoes perfect for everyday running.', 'Footwear', 'product2.jpg'),
('Leather Wallet', 29.99, 'Slim genuine leather wallet with RFID blocking technology.', 'Accessories', 'product3.jpg'),
('Stainless Water Bottle', 24.99, 'Keep drinks cold for 24 hours or hot for 12 hours. BPA-free.', 'Lifestyle', 'product4.jpg'),
('Backpack', 59.99, 'Durable 30L backpack with laptop compartment and USB charging port.', 'Accessories', 'product5.png');
