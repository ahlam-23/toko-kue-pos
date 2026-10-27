<?php require '../config/koneksi.php'; need(['admin']);
$h=$pdo->query("SELECT COALESCE(SUM(total_harga),0) t,COUNT(*) n FROM transaksi WHERE DATE(created_at)=CURDATE()")->fetch();
$b=$pdo->query("SELECT COALESCE(SUM(total_harga),0) t,COUNT(*) n FROM transaksi WHERE DATE_FORMAT(created_at,'%Y-%m')=DATE_FORMAT(CURDATE(),'%Y-%m')")->fetch();
$low=$pdo->prepare("SELECT nama_produk,stok FROM produk WHERE stok<? ORDER BY stok"); $low->execute([STOK_MIN]); $low=$low->fetchAll();
head('Dashboard'); ?>
<div class="section-heading mt-2"><div><span class="eyebrow">Ringkasan toko</span><h2>Dashboard</h2></div><p>Pantau aktivitas dan kondisi toko hari ini.</p></div>
<div class="row g-3 mb-4">
<?php foreach([['☀️','Pemasukan hari ini',rp($h['t'])],['🧾','Transaksi hari ini',$h['n']],['📈','Pemasukan bulan ini',rp($b['t'])],['🛍️','Transaksi bulan ini',$b['n']]] as $c): ?>
<div class="col-6 col-lg-3"><div class="card stat-card"><span class="stat-icon"><?=$c[0]?></span><small><?=$c[1]?></small><div class="fs-4 fw-bold"><?=$c[2]?></div></div></div><?php endforeach; ?></div>
<div class="card"><div class="card-header">Stok menipis (kurang dari <?=STOK_MIN?>)</div><ul class="list-group list-group-flush">
<?php foreach($low as $l): ?><li class="list-group-item d-flex justify-content-between"><?=e($l['nama_produk'])?><span class="badge bg-danger">Sisa <?=$l['stok']?></span></li>
<?php endforeach; if(!$low): ?><li class="list-group-item text-muted">Semua stok aman.</li><?php endif; ?></ul></div>
<?php foot();
