
DROP DATABASE IF EXISTS dien_tu_cu_db;
CREATE DATABASE dien_tu_cu_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE dien_tu_cu_db;

-- =====================================================================
-- 1. TÀI KHOẢN KHÁCH HÀNG / NGƯỜI MUA (UC01, UC02, UC13)
-- =====================================================================
CREATE TABLE users (
    user_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ho_ten          VARCHAR(100)    NOT NULL,
    email           VARCHAR(150)    NOT NULL,
    so_dien_thoai   VARCHAR(15)     NULL,
    mat_khau        VARCHAR(255)    NOT NULL,          -- lưu hash (bcrypt...)
    trang_thai      ENUM('Active','Locked') NOT NULL DEFAULT 'Active',  -- UC13: khóa/mở khóa tài khoản
    dia_chi         VARCHAR(255)    NULL,
    ngay_tao        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                     ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_users_email UNIQUE (email)
) ENGINE=InnoDB;

-- =====================================================================
-- 1b. TÀI KHOẢN QUẢN TRỊ VIÊN - ADMIN (bảng riêng, tách biệt với users)
-- =====================================================================
CREATE TABLE admin (
    admin_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ho_ten          VARCHAR(100)    NOT NULL,
    email           VARCHAR(150)    NOT NULL,
    so_dien_thoai   VARCHAR(15)     NULL,
    mat_khau        VARCHAR(255)    NOT NULL,          -- lưu hash (bcrypt...)
    chuc_vu         VARCHAR(100)    NULL,              -- vd: Quản trị hệ thống, Nhân viên quản lý đơn hàng...
    trang_thai      ENUM('Active','Locked') NOT NULL DEFAULT 'Active',
    ngay_tao        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                     ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_admin_email UNIQUE (email)
) ENGINE=InnoDB;


CREATE TABLE danh_muc (
    danh_muc_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ten_danh_muc    VARCHAR(100)    NOT NULL,
    mo_ta           VARCHAR(255)    NULL,
    ngay_tao        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================================
-- 3. SẢN PHẨM (UC03, UC04, UC10)
-- =====================================================================
CREATE TABLE san_pham (
    san_pham_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    danh_muc_id     INT UNSIGNED    NOT NULL,
    admin_id        INT UNSIGNED    NULL,               -- admin đăng/quản lý sản phẩm (UC10)
    ten_san_pham    VARCHAR(150)    NOT NULL,
    mo_ta           TEXT            NULL,
    gia             DECIMAL(12,2)   NOT NULL,
    tinh_trang      ENUM('Mới','Như mới','Đã qua sử dụng','Cũ') NOT NULL DEFAULT 'Đã qua sử dụng',
    so_luong_ton    INT UNSIGNED    NOT NULL DEFAULT 0,
    con_hang        BOOLEAN         NOT NULL DEFAULT TRUE,   -- tình trạng còn hàng
    hinh_anh_dai_dien VARCHAR(255)  NULL,
    ngay_dang       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                     ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_sanpham_danhmuc FOREIGN KEY (danh_muc_id)
        REFERENCES danh_muc(danh_muc_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_sanpham_admin FOREIGN KEY (admin_id)
        REFERENCES admin(admin_id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_sanpham_ten (ten_san_pham),
    INDEX idx_sanpham_gia (gia)
) ENGINE=InnoDB;

-- Nhiều hình ảnh cho 1 sản phẩm (bổ sung cho UC03: hiển thị hình ảnh)
CREATE TABLE san_pham_hinh_anh (
    hinh_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    san_pham_id     INT UNSIGNED    NOT NULL,
    duong_dan       VARCHAR(255)    NOT NULL,
    CONSTRAINT fk_hinh_sanpham FOREIGN KEY (san_pham_id)
        REFERENCES san_pham(san_pham_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- 4. GIỎ HÀNG (UC05)
-- =====================================================================
CREATE TABLE gio_hang (
    gio_hang_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED    NOT NULL,
    ngay_tao        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_giohang_user FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT uq_giohang_user UNIQUE (user_id)   -- mỗi user 1 giỏ hàng đang hoạt động
) ENGINE=InnoDB;

CREATE TABLE gio_hang_chi_tiet (
    gio_hang_id     INT UNSIGNED    NOT NULL,
    san_pham_id     INT UNSIGNED    NOT NULL,
    so_luong        INT UNSIGNED    NOT NULL DEFAULT 1,
    ngay_them       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (gio_hang_id, san_pham_id),
    CONSTRAINT fk_ghct_giohang FOREIGN KEY (gio_hang_id)
        REFERENCES gio_hang(gio_hang_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_ghct_sanpham FOREIGN KEY (san_pham_id)
        REFERENCES san_pham(san_pham_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_ghct_soluong CHECK (so_luong > 0)
) ENGINE=InnoDB;

-- =====================================================================
-- 5. ĐƠN HÀNG (UC06, UC08, UC12)
-- =====================================================================
CREATE TABLE don_hang (
    don_hang_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED    NOT NULL,
    admin_id        INT UNSIGNED    NULL,               -- admin xác nhận/xử lý đơn (UC08, UC12)
    ngay_dat        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    dia_chi_giao    VARCHAR(255)    NOT NULL,
    nguoi_nhan      VARCHAR(100)    NOT NULL,
    sdt_nguoi_nhan  VARCHAR(15)     NOT NULL,
    tong_tien       DECIMAL(12,2)   NOT NULL DEFAULT 0,
    trang_thai      ENUM('Chờ xác nhận','Đang giao','Đã giao','Đã hủy')
                                    NOT NULL DEFAULT 'Chờ xác nhận',   -- UC08
    ghi_chu         VARCHAR(255)    NULL,
    CONSTRAINT fk_donhang_user FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_donhang_admin FOREIGN KEY (admin_id)
        REFERENCES admin(admin_id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_donhang_trangthai (trang_thai),
    INDEX idx_donhang_ngaydat (ngay_dat)
) ENGINE=InnoDB;

CREATE TABLE don_hang_chi_tiet (
    don_hang_id     INT UNSIGNED    NOT NULL,
    san_pham_id     INT UNSIGNED    NOT NULL,
    so_luong        INT UNSIGNED    NOT NULL,
    don_gia         DECIMAL(12,2)   NOT NULL,      -- giá tại thời điểm đặt hàng
    thanh_tien      DECIMAL(12,2)   GENERATED ALWAYS AS (so_luong * don_gia) STORED,
    PRIMARY KEY (don_hang_id, san_pham_id),
    CONSTRAINT fk_dhct_donhang FOREIGN KEY (don_hang_id)
        REFERENCES don_hang(don_hang_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_dhct_sanpham FOREIGN KEY (san_pham_id)
        REFERENCES san_pham(san_pham_id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =====================================================================
-- 6. THANH TOÁN (UC07)
-- =====================================================================
CREATE TABLE thanh_toan (
    thanh_toan_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    don_hang_id     INT UNSIGNED    NOT NULL,
    phuong_thuc     ENUM('COD','Chuyển khoản','Ví điện tử') NOT NULL,
    trang_thai      ENUM('Chờ thanh toán','Đã thanh toán','Thất bại')
                                    NOT NULL DEFAULT 'Chờ thanh toán',
    so_tien         DECIMAL(12,2)   NOT NULL,
    ngay_thanh_toan DATETIME        NULL,
    CONSTRAINT fk_thanhtoan_donhang FOREIGN KEY (don_hang_id)
        REFERENCES don_hang(don_hang_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT uq_thanhtoan_donhang UNIQUE (don_hang_id)
) ENGINE=InnoDB;

-- =====================================================================
-- 7. ĐÁNH GIÁ SẢN PHẨM (UC09)
-- =====================================================================
CREATE TABLE danh_gia (
    danh_gia_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED    NOT NULL,
    san_pham_id     INT UNSIGNED    NOT NULL,
    don_hang_id     INT UNSIGNED    NOT NULL,   -- xác định đơn hàng đã hoàn thành
    so_sao          TINYINT UNSIGNED NOT NULL,
    nhan_xet        TEXT            NULL,
    ngay_danh_gia   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_danhgia_user FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_danhgia_sanpham FOREIGN KEY (san_pham_id)
        REFERENCES san_pham(san_pham_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_danhgia_donhang FOREIGN KEY (don_hang_id)
        REFERENCES don_hang(don_hang_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_danhgia_sosao CHECK (so_sao BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- =====================================================================
-- UC14: Xem báo cáo thống kê
-- Thêm index để tối ưu truy vấn "sản phẩm bán chạy" và "doanh thu theo thời gian"
-- =====================================================================

-- Index trên san_pham_id để nhóm/tính sản phẩm bán chạy nhanh hơn
ALTER TABLE don_hang_chi_tiet
    ADD INDEX idx_dhct_sanpham (san_pham_id);

-- Index trên ngay_thanh_toan để thống kê doanh thu theo ngày/tháng nhanh hơn
ALTER TABLE thanh_toan
    ADD INDEX idx_thanhtoan_ngaythanhtoan (ngay_thanh_toan);

-- =====================================================================
-- UC11: Quản lý danh mục
-- Thêm cột admin_id (biết admin nào tạo/quản lý danh mục) và ngay_cap_nhat
-- =====================================================================

ALTER TABLE danh_muc
    ADD COLUMN admin_id INT UNSIGNED NULL AFTER ten_danh_muc,
    ADD COLUMN ngay_cap_nhat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP AFTER ngay_tao;

ALTER TABLE danh_muc
    ADD CONSTRAINT fk_danhmuc_admin FOREIGN KEY (admin_id)
        REFERENCES admin(admin_id)
        ON UPDATE CASCADE ON DELETE SET NULL;

INSERT INTO admin (ho_ten, email, so_dien_thoai, mat_khau, chuc_vu) VALUES
('Quản Trị Viên', 'admin@dientucu.vn', '0900000000', '$2y$10$hashedpassword', 'Quản trị hệ thống');

INSERT INTO users (ho_ten, email, so_dien_thoai, mat_khau) VALUES
('Nguyễn Văn A', 'vana@gmail.com', '0901111111', '$2y$10$hashedpassword');

INSERT INTO danh_muc (ten_danh_muc, mo_ta) VALUES
('Điện thoại', 'Điện thoại di động cũ, đã qua sử dụng'),
('Laptop', 'Máy tính xách tay cũ'),
('Phụ kiện', 'Phụ kiện điện tử: tai nghe, sạc, chuột...');

INSERT INTO san_pham (danh_muc_id, admin_id, ten_san_pham, mo_ta, gia, tinh_trang, so_luong_ton) VALUES
(1, 1, 'iPhone 11 128GB', 'Máy đẹp 95%, pin 88%', 6500000, 'Đã qua sử dụng', 5),
(2, 1, 'Laptop Dell Latitude 7480', 'Core i5, 8GB RAM, 256GB SSD', 7200000, 'Như mới', 3),
(1, 1, 'iPhone 12 128GB', 'Máy đẹp 97%, pin 90%, fullbox', 9500000, 'Như mới', 4),
(1, 1, 'Samsung Galaxy S21', 'Máy đẹp 90%, pin 85%, có trầy nhẹ viền', 7800000, 'Đã qua sử dụng', 6),
(1, 1, 'Xiaomi Redmi Note 11', 'Máy zin đẹp, pin 92%, tặng kèm ốp lưng', 3200000, 'Như mới', 8),
(1, 1, 'iPhone XR 64GB', 'Máy cũ, pin 78%, còn sử dụng tốt', 4200000, 'Cũ', 3),
(2, 1, 'MacBook Air M1 2020', '8GB RAM, 256GB SSD, pin 95%', 15500000, 'Như mới', 2),
(2, 1, 'Laptop Asus Vivobook 15', 'Core i5, 8GB RAM, 512GB SSD', 8900000, 'Đã qua sử dụng', 5),
(2, 1, 'Laptop HP Pavilion 14', 'Core i3, 4GB RAM, 256GB SSD', 5600000, 'Cũ', 4),
(3, 1, 'Tai nghe Bluetooth JBL', 'Còn 90% pin, đầy đủ phụ kiện', 650000, 'Như mới', 15),
(3, 1, 'Sạc nhanh Samsung 25W', 'Sạc zin theo máy, đã qua sử dụng', 250000, 'Đã qua sử dụng', 20),
(3, 1, 'Chuột không dây Logitech M331', 'Hoạt động tốt, êm ái', 320000, 'Như mới', 12);

