<?php require '../config/koneksi.php'; need(['admin']);
$dari=$_GET['dari']??date('Y-m-01'); $sampai=$_GET['sampai']??date('Y-m-d'); $r=[$dari,$sampai];
$q=function($sql) use($pdo,$r){ $s=$pdo->prepare($sql); $s->execute($r); return $s->fetchAll(); };
$sum=$q("SELECT COALESCE(SUM(total_harga),0) t,COUNT(*) n FROM transaksi WHERE DATE(created_at) BETWEEN ? AND ?")[0];
$harian=$q("SELECT DATE(created_at) k,COUNT(*) n,SUM(total_harga) t FROM transaksi WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY k ORDER BY k DESC");
$bulanan=$q("SELECT DATE_FORMAT(created_at,'%Y-%m') k,COUNT(*) n,SUM(total_harga) t FROM transaksi WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY k ORDER BY k DESC");
$trx=$q("SELECT t.*,u.nama_lengkap FROM transaksi t JOIN users u ON u.id=t.user_id WHERE DATE(t.created_at) BETWEEN ? AND ? ORDER BY t.created_at DESC");
$det=null; if(isset($_GET['detail'])){ $s=$pdo->prepare("SELECT d.*,p.nama_produk,t.no_nota FROM detail_transaksi d JOIN produk p ON p.id=d.produk_id JOIN transaksi t ON t.id=d.transaksi_id WHERE d.transaksi_id=?"); $s->execute([(int)$_GET['detail']]); $det=$s->fetchAll(); }
function tbl($title,$rows){ ?><div class="card mb-3"><div class="card-header"><?=$title?></div><table class="table mb-0"><tr><th>Periode</th><th>Jumlah transaksi</th><th>Pemasukan</th></tr>
<?php foreach($rows as $x): ?><tr><td><?=e($x['k'])?></td><td><?=$x['n']?></td><td><?=rp($x['t'])?></td></tr><?php endforeach; if(!$rows): ?><tr><td colspan="3" class="text-muted">Belum ada data.</td></tr><?php endif; ?></table></div><?php }
head('Laporan'); ?>
<div class="section-heading mt-2"><div><span class="eyebrow">Performa penjualan</span><h2>Laporan transaksi</h2></div><p>Pilih rentang tanggal untuk melihat ringkasan penjualan.</p></div>
<form class="row g-2 mb-3"><div class="col-auto"><input type="date" name="dari" value="<?=e($dari)?>" class="form-control"></div>
<div class="col-auto"><input type="date" name="sampai" value="<?=e($sampai)?>" class="form-control"></div><div class="col-auto"><button class="btn btn-primary">Tampilkan laporan</button></div></form>
<div class="row g-3 mb-3"><div class="col-md-3"><div class="card p-3"><small>Total pemasukan</small><div class="fs-4 fw-bold"><?=rp($sum['t'])?></div></div></div>
<div class="col-md-3"><div class="card p-3"><small>Jumlah transaksi</small><div class="fs-4 fw-bold"><?=$sum['n']?></div></div></div></div>
<div class="row"><div class="col-lg-6"><?php tbl('Rekap harian',$harian); ?></div><div class="col-lg-6"><?php tbl('Rekap bulanan',$bulanan); ?></div></div>
<?php if($det!==null): ?><div class="card mb-3 border-primary"><div class="card-header">Detail nota <?=e($det[0]['no_nota']??'')?></div><table class="table mb-0"><tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr>
<?php foreach($det as $d): ?><tr><td><?=e($d['nama_produk'])?></td><td><?=rp($d['harga_satuan'])?></td><td><?=$d['jumlah']?></td><td><?=rp($d['subtotal'])?></td></tr><?php endforeach; ?></table></div><?php endif; ?>
<div class="card mb-5"><div class="card-header">Riwayat transaksi</div><div class="table-responsive"><table class="table mb-0"><tr><th>No. nota</th><th>Waktu</th><th>Kasir</th><th>Total</th><th></th></tr>
<?php foreach($trx as $t): ?><tr><td><?=e($t['no_nota'])?></td><td><?=e($t['created_at'])?></td><td><?=e($t['nama_lengkap'])?></td><td><?=rp($t['total_harga'])?></td>
<td><a class="btn btn-sm btn-outline-primary" href="?dari=<?=e($dari)?>&sampai=<?=e($sampai)?>&detail=<?=$t['id']?>">Detail</a> <a class="btn btn-sm btn-outline-secondary" target="_blank" href="../kasir/cetak_nota.php?id=<?=$t['id']?>">Nota</a></td></tr>
<?php endforeach; if(!$trx): ?><tr><td colspan="5" class="text-muted">Tidak ada transaksi pada rentang ini.</td></tr><?php endif; ?></table></div></div>
<?php foot();
