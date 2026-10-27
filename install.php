<?php
require __DIR__.'/config/config.php';
try { $pdo=new PDO("mysql:host={$DB['host']};charset=utf8mb4",$DB['user'],$DB['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]); }
catch(PDOException $e){ die('Tidak bisa terhubung ke MySQL: '.htmlspecialchars($e->getMessage())); }
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$DB['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `{$DB['name']}`");
$sql=[
"CREATE TABLE IF NOT EXISTS users(id INT AUTO_INCREMENT PRIMARY KEY,username VARCHAR(50) UNIQUE NOT NULL,password VARCHAR(255) NOT NULL,nama_lengkap VARCHAR(100) NOT NULL,role ENUM('admin','kasir') NOT NULL DEFAULT 'kasir')ENGINE=InnoDB",
"CREATE TABLE IF NOT EXISTS kategori(id INT AUTO_INCREMENT PRIMARY KEY,nama_kategori VARCHAR(100) NOT NULL)ENGINE=InnoDB",
"CREATE TABLE IF NOT EXISTS produk(id INT AUTO_INCREMENT PRIMARY KEY,kategori_id INT NOT NULL,nama_produk VARCHAR(150) NOT NULL,harga DECIMAL(10,2) NOT NULL,stok INT NOT NULL DEFAULT 0,foto VARCHAR(255) NULL,deskripsi TEXT NULL,is_promo TINYINT(1) NOT NULL DEFAULT 0,FOREIGN KEY(kategori_id) REFERENCES kategori(id))ENGINE=InnoDB",
"CREATE TABLE IF NOT EXISTS transaksi(id INT AUTO_INCREMENT PRIMARY KEY,no_nota VARCHAR(50) UNIQUE NOT NULL,user_id INT NOT NULL,total_harga DECIMAL(10,2) NOT NULL,bayar DECIMAL(10,2) NOT NULL,kembali DECIMAL(10,2) NOT NULL,created_at DATETIME DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id))ENGINE=InnoDB",
"CREATE TABLE IF NOT EXISTS detail_transaksi(id INT AUTO_INCREMENT PRIMARY KEY,transaksi_id INT NOT NULL,produk_id INT NOT NULL,harga_satuan DECIMAL(10,2) NOT NULL,jumlah INT NOT NULL,subtotal DECIMAL(10,2) NOT NULL,FOREIGN KEY(transaksi_id) REFERENCES transaksi(id),FOREIGN KEY(produk_id) REFERENCES produk(id))ENGINE=InnoDB",
];
foreach($sql as $q) $pdo->exec($q);
if($pdo->query("SELECT COUNT(*) FROM users")->fetchColumn()>0){ echo '<p>Sudah terinstal. <a href="login.php">Ke halaman login</a>. Hapus file install.php demi keamanan.</p>'; exit; }
$ins=$pdo->prepare("INSERT INTO users(username,password,nama_lengkap,role) VALUES(?,?,?,?)");
$ins->execute(['admin',password_hash('admin123',PASSWORD_DEFAULT),'Administrator','admin']);
$ins->execute(['kasir',password_hash('kasir123',PASSWORD_DEFAULT),'Kasir Toko','kasir']);
foreach(['Kue Basah','Kue Kering','Bolu','Pastry'] as $k) $pdo->prepare("INSERT INTO kategori(nama_kategori) VALUES(?)")->execute([$k]);
$p=$pdo->prepare("INSERT INTO produk(kategori_id,nama_produk,harga,stok,deskripsi,is_promo) VALUES(?,?,?,?,?,?)");
foreach([[1,'Lapis Legit',45000,20,'Lapis legit butter premium',1],[1,'Kue Lumpur',3500,50,'Kue lumpur kentang lembut',0],[2,'Nastar',65000,15,'Nastar nanas isi selai homemade',1],[2,'Kastengel',70000,4,'Kastengel keju gurih',0],[3,'Bolu Pandan',30000,12,'Bolu pandan super lembut',0],[4,'Croissant Cokelat',18000,25,'Croissant isi cokelat leleh',0]] as $r) $p->execute($r);
echo '<h3>Instalasi berhasil ✅</h3><p>Admin: <b>admin / admin123</b><br>Kasir: <b>kasir / kasir123</b></p><p><a href="login.php">Masuk sekarang</a>. Hapus file install.php setelah ini.</p>';
