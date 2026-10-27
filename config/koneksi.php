<?php
session_start();
require __DIR__.'/config.php';
try {
  $pdo = new PDO("mysql:host={$DB['host']};dbname={$DB['name']};charset=utf8mb4",$DB['user'],$DB['pass'],
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
} catch (PDOException $e) { die('Koneksi database gagal. Pastikan MySQL aktif dan sudah menjalankan install.php. '.htmlspecialchars($e->getMessage())); }

define('BASE', rtrim(str_replace('\\','/',substr(str_replace('\\','/',dirname(__DIR__)), strlen(str_replace('\\','/',realpath($_SERVER['DOCUMENT_ROOT']))))),'/').'/');
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rp($n){ return 'Rp '.number_format((float)$n,0,',','.'); }
function flash($m,$t='success'){ $_SESSION['flash']=[$m,$t]; }
function csrf_field(){ return '<input type="hidden" name="csrf" value="'.$_SESSION['csrf'].'">'; }
function csrf_check(){ if(!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) die('Token tidak valid. Muat ulang halaman.'); }
function need($roles){ if(empty($_SESSION['user']) || !in_array($_SESSION['user']['role'],$roles)){ header('Location: '.BASE.'login.php'); exit; } }

function head($t){ $u=$_SESSION['user']??null; ?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($t)?> - <?=NAMA_TOKO?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?=BASE?>assets/css/style.css" rel="stylesheet"></head><body>
<nav class="navbar navbar-expand-lg site-nav"><div class="container-fluid px-4">
<a class="navbar-brand" href="<?=BASE?>"><span class="brand-mark">🍰</span><?=NAMA_TOKO?></a>
<button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nv"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="nv"><div class="navbar-nav me-auto">
<?php if($u): ?>
<?php if($u['role']=='admin'): ?>
<a class="nav-link" href="<?=BASE?>admin/">Dashboard</a><a class="nav-link" href="<?=BASE?>admin/produk.php">Produk</a>
<a class="nav-link" href="<?=BASE?>admin/kategori.php">Kategori</a><a class="nav-link" href="<?=BASE?>admin/laporan.php">Laporan</a>
<a class="nav-link" href="<?=BASE?>admin/users.php">User</a>
<?php endif; ?>
<a class="nav-link" href="<?=BASE?>kasir/">Kasir</a></div>
<div class="d-flex align-items-center gap-3"><span class="nav-user"><?=e($u['nama_lengkap'])?> · <?=e($u['role'])?></span>
<a class="btn btn-sm btn-outline-primary" href="<?=BASE?>logout.php">Keluar</a></div>
<?php else: ?>
<div class="navbar-nav ms-auto"><a class="nav-link" href="<?=BASE?>">Beranda</a><a class="btn btn-sm btn-primary ms-lg-2" href="<?=BASE?>login.php">Masuk staf</a></div>
<?php endif; ?></div></div></nav>
<main class="app-main">
<?php if(!empty($_SESSION['flash'])): [$m,$tp]=$_SESSION['flash']; unset($_SESSION['flash']); ?>
<div class="alert alert-<?=$tp?> alert-dismissible"><?=e($m)?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif;
}
function foot(){ ?></main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html><?php }
