<?php require '../config/koneksi.php'; need(['admin']);
if($_SERVER['REQUEST_METHOD']=='POST'){ csrf_check(); $id=(int)($_POST['id']??0);
 if($_POST['aksi']=='simpan'){
  $f=null;
  if(!empty($_FILES['foto']['name']) && $_FILES['foto']['error']===0){
   $ext=strtolower(pathinfo($_FILES['foto']['name'],PATHINFO_EXTENSION));
   if(in_array($ext,['jpg','jpeg','png','webp']) && @getimagesize($_FILES['foto']['tmp_name']) && $_FILES['foto']['size']<2097152){
    $f=bin2hex(random_bytes(8)).'.'.$ext; move_uploaded_file($_FILES['foto']['tmp_name'],'../assets/uploads/'.$f);
   } else flash('Foto ditolak (harus jpg/png/webp, maks 2 MB). Data lain tetap disimpan.','warning'); }
  $v=[(int)$_POST['kategori_id'],trim($_POST['nama']),(float)$_POST['harga'],(int)$_POST['stok'],trim($_POST['deskripsi']),isset($_POST['is_promo'])?1:0];
  if($id){ $sql="UPDATE produk SET kategori_id=?,nama_produk=?,harga=?,stok=?,deskripsi=?,is_promo=?".($f?",foto=?":"")." WHERE id=?";
   if($f)$v[]=$f; $v[]=$id; }
  else { $sql="INSERT INTO produk(kategori_id,nama_produk,harga,stok,deskripsi,is_promo".($f?",foto":"").") VALUES(?,?,?,?,?,?".($f?",?":"").")"; if($f)$v[]=$f; }
  $pdo->prepare($sql)->execute($v); if(empty($_SESSION['flash'])) flash('Produk disimpan');
 } elseif($_POST['aksi']=='hapus'){
  try{ $pdo->prepare("DELETE FROM produk WHERE id=?")->execute([$id]); flash('Produk dihapus'); }
  catch(PDOException $e){ flash('Produk sudah punya riwayat transaksi, tidak bisa dihapus. Set stok 0 saja.','danger'); } }
 header('Location: produk.php'); exit; }
$edit=null; if(isset($_GET['edit'])){ $s=$pdo->prepare("SELECT * FROM produk WHERE id=?"); $s->execute([(int)$_GET['edit']]); $edit=$s->fetch(); }
$kat=$pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
$list=$pdo->query("SELECT p.*,k.nama_kategori FROM produk p JOIN kategori k ON k.id=p.kategori_id ORDER BY p.nama_produk")->fetchAll();
head('Produk'); ?>
<div class="section-heading mt-2"><div><span class="eyebrow">Katalog toko</span><h2>Kelola produk</h2></div><p>Atur harga, stok, dan tampilan kue.</p></div>
<div class="row g-4"><div class="col-lg-4"><div class="card p-3"><h5><?=$edit?'Edit':'Tambah'?> produk</h5>
<form method="post" enctype="multipart/form-data"><?=csrf_field()?><input type="hidden" name="aksi" value="simpan"><input type="hidden" name="id" value="<?=$edit['id']??0?>">
<input name="nama" class="form-control mb-2" placeholder="Nama kue" value="<?=e($edit['nama_produk']??'')?>" required>
<select name="kategori_id" class="form-select mb-2" required><option value="">Pilih kategori</option>
<?php foreach($kat as $k): ?><option value="<?=$k['id']?>" <?=($edit['kategori_id']??0)==$k['id']?'selected':''?>><?=e($k['nama_kategori'])?></option><?php endforeach; ?></select>
<input name="harga" type="number" min="0" step="100" class="form-control mb-2" placeholder="Harga" value="<?=e($edit['harga']??'')?>" required>
<input name="stok" type="number" min="0" class="form-control mb-2" placeholder="Stok" value="<?=e($edit['stok']??'')?>" required>
<textarea name="deskripsi" class="form-control mb-2" rows="2" placeholder="Deskripsi"><?=e($edit['deskripsi']??'')?></textarea>
<input type="file" name="foto" accept="image/*" class="form-control mb-2">
<div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_promo" id="pr" <?=!empty($edit['is_promo'])?'checked':''?>><label for="pr" class="form-check-label">Tampilkan di promo</label></div>
<button class="btn btn-primary">Simpan produk</button> <?php if($edit): ?><a href="produk.php" class="btn btn-link">Batal</a><?php endif; ?></form></div></div>
<div class="col-lg-8"><div class="card table-responsive"><table class="table align-middle mb-0"><tr><th></th><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th></th></tr>
<?php foreach($list as $p): ?><tr><td><?php if($p['foto']): ?><img src="../assets/uploads/<?=e($p['foto'])?>" width="44" height="44" style="object-fit:cover;border-radius:6px"><?php endif; ?></td>
<td><?=e($p['nama_produk'])?> <?=$p['is_promo']?'<span class="badge bg-danger">Promo</span>':''?></td><td><?=e($p['nama_kategori'])?></td><td><?=rp($p['harga'])?></td>
<td><span class="badge <?=$p['stok']<STOK_MIN?'bg-danger':'bg-secondary'?>"><?=$p['stok']?></span></td>
<td class="d-flex gap-1"><a class="btn btn-sm btn-outline-primary" href="?edit=<?=$p['id']?>">Edit</a>
<form method="post" onsubmit="return confirm('Hapus produk ini?')"><?=csrf_field()?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="btn btn-sm btn-outline-danger">Hapus</button></form></td></tr>
<?php endforeach; ?></table></div></div></div>
<?php foot();
