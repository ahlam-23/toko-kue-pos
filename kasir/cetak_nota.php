<?php require '../config/koneksi.php'; need(['admin','kasir']);
$s=$pdo->prepare("SELECT t.*,u.nama_lengkap FROM transaksi t JOIN users u ON u.id=t.user_id WHERE t.id=?"); $s->execute([(int)($_GET['id']??0)]); $t=$s->fetch();
if(!$t) die('Nota tidak ditemukan');
$s=$pdo->prepare("SELECT d.*,p.nama_produk FROM detail_transaksi d JOIN produk p ON p.id=d.produk_id WHERE d.transaksi_id=?"); $s->execute([$t['id']]); $items=$s->fetchAll(); ?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><title>Nota <?=e($t['no_nota'])?></title>
<style>body{font-family:monospace;font-size:12px;width:280px;margin:10px auto}h3,p{margin:2px 0;text-align:center}table{width:100%;border-collapse:collapse}
td{padding:2px 0;vertical-align:top}.r{text-align:right}hr{border:0;border-top:1px dashed #000}.no{margin-top:15px;text-align:center}
@media print{.no{display:none}@page{margin:5mm}}</style></head><body onload="window.print()">
<h3><?=e(NAMA_TOKO)?></h3><p>Nota: <?=e($t['no_nota'])?></p><p><?=e($t['created_at'])?> | <?=e($t['nama_lengkap'])?></p><hr>
<table><?php foreach($items as $i): ?>
<tr><td colspan="2"><?=e($i['nama_produk'])?></td></tr>
<tr><td><?=$i['jumlah']?> x <?=rp($i['harga_satuan'])?></td><td class="r"><?=rp($i['subtotal'])?></td></tr><?php endforeach; ?></table><hr>
<table><tr><td>Total</td><td class="r"><b><?=rp($t['total_harga'])?></b></td></tr>
<tr><td>Bayar</td><td class="r"><?=rp($t['bayar'])?></td></tr><tr><td>Kembali</td><td class="r"><?=rp($t['kembali'])?></td></tr></table><hr>
<p>Terima kasih 🍰</p>
<div class="no"><button onclick="print()">Cetak ulang</button> <a href="index.php">Transaksi baru</a></div></body></html>
