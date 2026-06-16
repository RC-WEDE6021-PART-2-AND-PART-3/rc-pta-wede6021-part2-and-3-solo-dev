-- ClothingStore Database Setup
-- Run this SQL in your MySQL/phpMyAdmin to create all required tables

CREATE DATABASE IF NOT EXISTS ClothingStore;
USE ClothingStore;

-- Users Table (Customers, Sellers, Admins)
CREATE TABLE IF NOT EXISTS tblUser (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer','seller','admin') DEFAULT 'customer',
    isVerified TINYINT(1) DEFAULT 0,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products / Clothing Table
CREATE TABLE IF NOT EXISTS tblProduct (
    productID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    description TEXT,
    brand VARCHAR(100),
    category VARCHAR(50),
    stock INT DEFAULT 0,
    sellerID INT,
    isApproved TINYINT(1) DEFAULT 0,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sellerID) REFERENCES tblUser(userID) ON DELETE SET NULL
);

-- Cart Table
CREATE TABLE IF NOT EXISTS tblCart (
    cartID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    productID INT NOT NULL,
    quantity INT DEFAULT 1,
    addedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES tblUser(userID) ON DELETE CASCADE,
    FOREIGN KEY (productID) REFERENCES tblProduct(productID) ON DELETE CASCADE
);

-- Orders Table
CREATE TABLE IF NOT EXISTS tblOrder (
    orderID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    totalAmount DECIMAL(10,2),
    status ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES tblUser(userID) ON DELETE CASCADE
);

-- Order Items
CREATE TABLE IF NOT EXISTS tblOrderItem (
    itemID INT AUTO_INCREMENT PRIMARY KEY,
    orderID INT NOT NULL,
    productID INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (orderID) REFERENCES tblOrder(orderID) ON DELETE CASCADE,
    FOREIGN KEY (productID) REFERENCES tblProduct(productID) ON DELETE CASCADE
);

-- Messages (Admin <-> Sellers/Buyers)
CREATE TABLE IF NOT EXISTS tblMessage (
    messageID INT AUTO_INCREMENT PRIMARY KEY,
    senderID INT NOT NULL,
    receiverID INT NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    isRead TINYINT(1) DEFAULT 0,
    sentAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (senderID) REFERENCES tblUser(userID) ON DELETE CASCADE,
    FOREIGN KEY (receiverID) REFERENCES tblUser(userID) ON DELETE CASCADE
);

-- Seller Requests
CREATE TABLE IF NOT EXISTS tblSellerRequest (
    requestID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    reason TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    submittedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES tblUser(userID) ON DELETE CASCADE
);

-- Insert default admin
INSERT IGNORE INTO tblUser (username, email, password, role, isVerified)
VALUES ('admin', 'admin@store.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);
-- Default admin password is: password

-- Sample approved products
INSERT IGNORE INTO tblProduct (name, price, image, description, brand, category, stock, isApproved)
VALUES 
('Classic White Tee', 199.99, '', 'Premium cotton white t-shirt', 'UrbanWear', 'Tops', 50, 1),
('Slim Fit Jeans', 599.99, '', 'Dark wash slim fit denim', 'DenimCo', 'Bottoms', 30, 1),
('Leather Jacket', 1299.99, '', 'Genuine leather biker jacket', 'RockStyle', 'Outerwear', 15, 1);
