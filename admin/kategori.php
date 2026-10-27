<?php require '../config/koneksi.php'; need(['admin']);
if($_SERVER['REQUEST_METHOD']=='POST'){ csrf_check(); $id=(int)($_POST['id']??0);
 if($_POST['aksi']=='simpan' && trim($_POST['nama'])!==''){
  if($id) $pdo->prepare("UPDATE kategori SET nama_kategori=? WHERE id=?")->execute([trim($_POST['nama']),$id]);
  else $pdo->prepare("INSERT INTO kategori(nama_kategori) VALUES(?)")->execute([trim($_POST['nama'])]);
  flash('Kategori disimpan');
 } elseif($_POST['aksi']=='hapus'){
  try{ $pdo->prepare("DELETE FROM kategori WHERE id=?")->execute([$id]); flash('Kategori dihapus'); }
  catch(PDOException $e){ flash('Kategori masih dipakai produk, tidak bisa dihapus','danger'); } }
 header('Location: kategori.php'); exit; }
$edit=null; if(isset($_GET['edit'])){ $s=$pdo->prepare("SELECT * FROM kategori WHERE id=?"); $s->execute([(int)$_GET['edit']]); $edit=$s->fetch(); }
$list=$pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
head('Kategori'); ?>
<div class="section-heading mt-2"><div><span class="eyebrow">Pengelompokan</span><h2>Kelola kategori</h2></div><p>Buat kategori agar kue mudah ditemukan.</p></div>
<div class="row g-4"><div class="col-md-4"><div class="card p-3"><h5><?=$edit?'Edit':'Tambah'?> kategori</h5>
<form method="post"><?=csrf_field()?><input type="hidden" name="aksi" value="simpan"><input type="hidden" name="id" value="<?=$edit['id']??0?>">
<input name="nama" class="form-control mb-2" placeholder="Nama kategori" value="<?=e($edit['nama_kategori']??'')?>" required>
<button class="btn btn-primary">Simpan kategori</button> <?php if($edit): ?><a href="kategori.php" class="btn btn-link">Batal</a><?php endif; ?></form></div></div>
<div class="col-md-8"><div class="card"><table class="table mb-0"><tr><th>Kategori</th><th width="160"></th></tr>
<?php foreach($list as $k): ?><tr><td><?=e($k['nama_kategori'])?></td><td class="d-flex gap-1"><a class="btn btn-sm btn-outline-primary" href="?edit=<?=$k['id']?>">Edit</a>
<form method="post" onsubmit="return confirm('Hapus kategori ini?')"><?=csrf_field()?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?=$k['id']?>"><button class="btn btn-sm btn-outline-danger">Hapus</button></form></td></tr>
<?php endforeach; ?></table></div></div></div>
<?php foot();
