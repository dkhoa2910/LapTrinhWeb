-- =========================================================
-- THESECOND V1
-- DATABASE SCHEMA
-- E-commerce website for used electronic devices
-- MySQL 8.x
-- =========================================================

DROP DATABASE IF EXISTS thesecond;

CREATE DATABASE thesecond
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE thesecond;

-- =========================================================
-- 1. USERS
-- =========================================================

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    full_name VARCHAR(100) NOT NULL,

    email VARCHAR(150) NOT NULL UNIQUE,

    password VARCHAR(255) NOT NULL,

    phone VARCHAR(20) NULL,

    avatar VARCHAR(500) NULL,

    role ENUM('user', 'seller', 'admin') NOT NULL DEFAULT 'user',

    status ENUM('active', 'inactive', 'blocked') NOT NULL DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_users_role (role),
    INDEX idx_users_status (status)
) ENGINE=InnoDB;


-- =========================================================
-- 2. CATEGORIES
-- =========================================================

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL,

    slug VARCHAR(120) NOT NULL UNIQUE,

    description TEXT NULL,

    image VARCHAR(500) NULL,

    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- =========================================================
-- 3. PRODUCTS
-- =========================================================

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    seller_id INT UNSIGNED NULL,

    category_id INT UNSIGNED NOT NULL,

    sku VARCHAR(50) NOT NULL UNIQUE,

    name VARCHAR(255) NOT NULL,

    brand VARCHAR(100) NULL,

    model VARCHAR(150) NULL,

    description TEXT NULL,

    price DECIMAL(15,2) NOT NULL,

    original_price DECIMAL(15,2) NULL,

    condition_type ENUM(
        'like_new',
        'excellent',
        'good',
        'fair',
        'poor'
    ) NOT NULL DEFAULT 'good',

    condition_score DECIMAL(3,1) NULL,

    usage_duration VARCHAR(100) NULL,

    serial_number VARCHAR(150) NULL UNIQUE,

    imei VARCHAR(50) NULL UNIQUE,

    battery_health TINYINT UNSIGNED NULL,

    warranty VARCHAR(255) NULL,

    accessories TEXT NULL,

    defects TEXT NULL,

    stock INT UNSIGNED NOT NULL DEFAULT 1,

    status ENUM(
        'pending',
        'approved',
        'rejected',
        'sold',
        'hidden'
    ) NOT NULL DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_products_seller
        FOREIGN KEY (seller_id)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    INDEX idx_products_category (category_id),
    INDEX idx_products_seller (seller_id),
    INDEX idx_products_status (status),
    INDEX idx_products_brand (brand),
    INDEX idx_products_price (price)
) ENGINE=InnoDB;


-- =========================================================
-- 4. PRODUCT IMAGES
-- =========================================================

CREATE TABLE product_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    product_id INT UNSIGNED NOT NULL,

    image_url VARCHAR(500) NOT NULL,

    is_primary BOOLEAN NOT NULL DEFAULT FALSE,

    sort_order INT UNSIGNED NOT NULL DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_product_images_product
        FOREIGN KEY (product_id)
        REFERENCES products(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    INDEX idx_product_images_product (product_id)
) ENGINE=InnoDB;


-- =========================================================
-- 5. ADDRESSES
-- =========================================================

CREATE TABLE addresses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,

    receiver_name VARCHAR(100) NOT NULL,

    receiver_phone VARCHAR(20) NOT NULL,

    address_line TEXT NOT NULL,

    ward VARCHAR(100) NULL,

    district VARCHAR(100) NULL,

    city VARCHAR(100) NOT NULL,

    is_default BOOLEAN NOT NULL DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_addresses_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    INDEX idx_addresses_user (user_id)
) ENGINE=InnoDB;


-- =========================================================
-- 6. CARTS
-- =========================================================

CREATE TABLE carts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL UNIQUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_carts_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 7. CART ITEMS
-- =========================================================

CREATE TABLE cart_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    cart_id INT UNSIGNED NOT NULL,

    product_id INT UNSIGNED NOT NULL,

    quantity INT UNSIGNED NOT NULL DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_cart_items_cart
        FOREIGN KEY (cart_id)
        REFERENCES carts(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_cart_items_product
        FOREIGN KEY (product_id)
        REFERENCES products(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    UNIQUE KEY unique_cart_product (cart_id, product_id),

    INDEX idx_cart_items_cart (cart_id),
    INDEX idx_cart_items_product (product_id)
) ENGINE=InnoDB;


-- =========================================================
-- 8. ORDERS
-- =========================================================

CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,

    order_code VARCHAR(30) NOT NULL UNIQUE,

    receiver_name VARCHAR(100) NOT NULL,

    receiver_phone VARCHAR(20) NOT NULL,

    shipping_address TEXT NOT NULL,

    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,

    shipping_fee DECIMAL(15,2) NOT NULL DEFAULT 0,

    discount DECIMAL(15,2) NOT NULL DEFAULT 0,

    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,

    payment_method ENUM(
        'cod',
        'bank_transfer',
        'momo',
        'vnpay'
    ) NOT NULL DEFAULT 'cod',

    payment_status ENUM(
        'pending',
        'paid',
        'failed',
        'refunded'
    ) NOT NULL DEFAULT 'pending',

    order_status ENUM(
        'pending',
        'confirmed',
        'processing',
        'shipping',
        'delivered',
        'cancelled',
        'returned'
    ) NOT NULL DEFAULT 'pending',

    note TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    INDEX idx_orders_user (user_id),
    INDEX idx_orders_status (order_status),
    INDEX idx_orders_payment_status (payment_status),
    INDEX idx_orders_created_at (created_at)
) ENGINE=InnoDB;


-- =========================================================
-- 9. ORDER ITEMS
-- =========================================================

CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    order_id INT UNSIGNED NOT NULL,

    product_id INT UNSIGNED NULL,

    product_name VARCHAR(255) NOT NULL,

    sku VARCHAR(50) NULL,

    quantity INT UNSIGNED NOT NULL DEFAULT 1,

    unit_price DECIMAL(15,2) NOT NULL,

    subtotal DECIMAL(15,2) NOT NULL,

    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id)
        REFERENCES orders(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id)
        REFERENCES products(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    INDEX idx_order_items_order (order_id),
    INDEX idx_order_items_product (product_id)
) ENGINE=InnoDB;


-- =========================================================
-- 10. PAYMENTS
-- =========================================================

CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    order_id INT UNSIGNED NOT NULL,

    transaction_code VARCHAR(100) NULL UNIQUE,

    payment_method ENUM(
        'cod',
        'bank_transfer',
        'momo',
        'vnpay'
    ) NOT NULL,

    amount DECIMAL(15,2) NOT NULL,

    status ENUM(
        'pending',
        'success',
        'failed',
        'refunded'
    ) NOT NULL DEFAULT 'pending',

    paid_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_payments_order
        FOREIGN KEY (order_id)
        REFERENCES orders(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    INDEX idx_payments_order (order_id),
    INDEX idx_payments_status (status)
) ENGINE=InnoDB;


-- =========================================================
-- 11. REVIEWS
-- =========================================================

CREATE TABLE product_reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    product_id INT UNSIGNED NOT NULL,

    user_id INT UNSIGNED NOT NULL,

    order_id INT UNSIGNED NULL,

    rating TINYINT UNSIGNED NOT NULL,

    comment TEXT NULL,

    status ENUM(
        'visible',
        'hidden'
    ) NOT NULL DEFAULT 'visible',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_reviews_product
        FOREIGN KEY (product_id)
        REFERENCES products(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_reviews_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_reviews_order
        FOREIGN KEY (order_id)
        REFERENCES orders(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    INDEX idx_reviews_product (product_id),
    INDEX idx_reviews_user (user_id)
) ENGINE=InnoDB;


-- =========================================================
-- 12. WISHLISTS
-- =========================================================

CREATE TABLE wishlists (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,

    product_id INT UNSIGNED NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_wishlists_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_wishlists_product
        FOREIGN KEY (product_id)
        REFERENCES products(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    UNIQUE KEY unique_wishlist_product (user_id, product_id)
) ENGINE=InnoDB;


-- =========================================================
-- 13. SEED CATEGORIES
-- =========================================================

INSERT INTO categories
    (name, slug, description)
VALUES
    (
        'Điện thoại',
        'dien-thoai',
        'Điện thoại thông minh đã qua sử dụng'
    ),
    (
        'Laptop',
        'laptop',
        'Laptop đã qua sử dụng'
    ),
    (
        'Tablet',
        'tablet',
        'Máy tính bảng đã qua sử dụng'
    ),
    (
        'Đồng hồ thông minh',
        'dong-ho-thong-minh',
        'Smartwatch đã qua sử dụng'
    ),
    (
        'Tai nghe',
        'tai-nghe',
        'Tai nghe và thiết bị âm thanh đã qua sử dụng'
    ),
    (
        'Phụ kiện',
        'phu-kien',
        'Các loại phụ kiện điện tử'
    );


-- =========================================================
-- FINISHED
-- =========================================================