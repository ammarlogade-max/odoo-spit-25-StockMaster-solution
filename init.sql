-- StockMaster Database Initialization Script
-- Run this script to setup the complete database

-- Create Database
CREATE DATABASE IF NOT EXISTS stockmaster_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stockmaster_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB;

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) UNIQUE NOT NULL,
    category VARCHAR(100) DEFAULT 'General',
    quantity INT DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0.00,
    low_stock_threshold INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sku (sku),
    INDEX idx_category (category),
    INDEX idx_low_stock (quantity, low_stock_threshold)
) ENGINE=InnoDB;

-- Stock Movements Table
CREATE TABLE IF NOT EXISTS stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    quantity INT NOT NULL,
    reference VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_type (type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Insert Admin User (password: password123)
INSERT INTO users (username, email, password_hash) VALUES 
('admin', 'admin@stockmaster.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE username=username;

-- Insert Sample Products
INSERT INTO products (name, sku, category, quantity, price, low_stock_threshold) VALUES
('Laptop Dell XPS 13', 'ELEC-001', 'Electronics', 25, 1299.99, 5),
('Wireless Mouse Logitech', 'ELEC-002', 'Electronics', 150, 29.99, 20),
('Mechanical Keyboard', 'ELEC-003', 'Electronics', 80, 89.99, 15),
('Office Chair Ergonomic', 'FURN-001', 'Furniture', 12, 249.99, 5),
('Standing Desk Adjustable', 'FURN-002', 'Furniture', 8, 399.99, 3),
('File Cabinet 4-Drawer', 'FURN-003', 'Furniture', 5, 179.99, 2),
('Notebooks A4 Pack of 5', 'STAT-001', 'Stationery', 200, 12.99, 50),
('Ballpoint Pens Box 50', 'STAT-002', 'Stationery', 500, 15.99, 100),
('Stapler Heavy Duty', 'STAT-003', 'Stationery', 45, 24.99, 10),
('Coffee Beans Premium 1kg', 'FOOD-001', 'Food', 30, 18.99, 10),
('Green Tea Bags 100pk', 'FOOD-002', 'Food', 100, 9.99, 20),
('Snack Mix Variety Pack', 'FOOD-003', 'Food', 75, 22.99, 15),
('Cotton T-Shirts Pack 3', 'CLTH-001', 'Clothing', 50, 29.99, 10),
('Winter Jackets Unisex', 'CLTH-002', 'Clothing', 15, 89.99, 5),
('Baseball Caps Branded', 'CLTH-003', 'Clothing', 40, 19.99, 10)
ON DUPLICATE KEY UPDATE sku=sku;

-- Insert Sample Stock Movements
INSERT INTO stock_movements (product_id, type, quantity, reference) VALUES
(1, 'receipt', 25, 'PO-2024-001'),
(2, 'receipt', 150, 'PO-2024-002'),
(3, 'receipt', 100, 'PO-2024-003'),
(1, 'delivery', 5, 'SO-2024-001'),
(2, 'delivery', 20, 'SO-2024-002');
