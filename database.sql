-- Database Toko Kue Putri (import via phpMyAdmin)
CREATE DATABASE IF NOT EXISTS toko_kue_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE toko_kue_db;

CREATE TABLE IF NOT EXISTS users(
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  nama_lengkap VARCHAR(100) NOT NULL,
  role ENUM('admin','kasir') NOT NULL DEFAULT 'kasir'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS kategori(
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS produk(
  id INT AUTO_INCREMENT PRIMARY KEY,
  kategori_id INT NOT NULL,
  nama_produk VARCHAR(150) NOT NULL,
  harga DECIMAL(10,2) NOT NULL,
  stok INT NOT NULL DEFAULT 0,
  foto VARCHAR(255) NULL,
  deskripsi TEXT NULL,
  is_promo TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY(kategori_id) REFERENCES kategori(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transaksi(
  id INT AUTO_INCREMENT PRIMARY KEY,
  no_nota VARCHAR(50) UNIQUE NOT NULL,
  user_id INT NOT NULL,
  total_harga DECIMAL(10,2) NOT NULL,
  bayar DECIMAL(10,2) NOT NULL,
  kembali DECIMAL(10,2) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS detail_transaksi(
  id INT AUTO_INCREMENT PRIMARY KEY,
  transaksi_id INT NOT NULL,
  produk_id INT NOT NULL,
  harga_satuan DECIMAL(10,2) NOT NULL,
  jumlah INT NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  FOREIGN KEY(transaksi_id) REFERENCES transaksi(id),
  FOREIGN KEY(produk_id) REFERENCES produk(id)
) ENGINE=InnoDB;

-- Data awal: admin / admin123 dan kasir / kasir123
INSERT INTO users(username,password,nama_lengkap,role) VALUES
('admin','$2y$12$wCEe.SEKdO3QGXpYsHegGuNTM6QmOzhMhSzXELzlPMlO2b.g8Q8NW','Administrator','admin'),
('kasir','$2y$12$FbhtUybqHmEHM9L2zbOryOvifIKsJpzAU3u/V1ZegOSbCSkkiMc7.','Kasir Toko','kasir');

INSERT INTO kategori(nama_kategori) VALUES ('Kue Basah'),('Kue Kering'),('Bolu'),('Pastry');

INSERT INTO produk(kategori_id,nama_produk,harga,stok,deskripsi,is_promo) VALUES
(1,'Lapis Legit',45000,20,'Lapis legit butter premium',1),
(1,'Kue Lumpur',3500,50,'Kue lumpur kentang lembut',0),
(2,'Nastar',65000,15,'Nastar nanas isi selai homemade',1),
(2,'Kastengel',70000,4,'Kastengel keju gurih',0),
(3,'Bolu Pandan',30000,12,'Bolu pandan super lembut',0),
(4,'Croissant Cokelat',18000,25,'Croissant isi cokelat leleh',0);
