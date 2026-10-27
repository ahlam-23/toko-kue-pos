<?php require '../config/koneksi.php'; need(['admin']);
if($_SERVER['REQUEST_METHOD']=='POST'){ csrf_check(); $id=(int)($_POST['id']??0);
 if($_POST['aksi']=='simpan'){ $un=trim($_POST['username']); $nm=trim($_POST['nama']); $ro=$_POST['role']=='admin'?'admin':'kasir'; $pw=$_POST['password'];
  try{
   if($id){ $pdo->prepare("UPDATE users SET username=?,nama_lengkap=?,role=? WHERE id=?")->execute([$un,$nm,$ro,$id]);
    if($pw!=='') $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($pw,PASSWORD_DEFAULT),$id]); }
   else { if($pw==='') throw new Exception('Password wajib diisi'); $pdo->prepare("INSERT INTO users(username,password,nama_lengkap,role) VALUES(?,?,?,?)")->execute([$un,password_hash($pw,PASSWORD_DEFAULT),$nm,$ro]); }
   flash('User disimpan');
  }catch(Exception $e){ flash($e instanceof PDOException?'Username sudah dipakai':$e->getMessage(),'danger'); }
 } elseif($_POST['aksi']=='hapus'){
  if($id==$_SESSION['user']['id']) flash('Tidak bisa menghapus akun sendiri','danger');
  else try{ $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]); flash('User dihapus'); }catch(PDOException $e){ flash('User sudah punya riwayat transaksi, tidak bisa dihapus','danger'); } }
 header('Location: users.php'); exit; }
$edit=null; if(isset($_GET['edit'])){ $s=$pdo->prepare("SELECT * FROM users WHERE id=?"); $s->execute([(int)$_GET['edit']]); $edit=$s->fetch(); }
$list=$pdo->query("SELECT * FROM users ORDER BY role,username")->fetchAll();
head('User'); ?>
<div class="section-heading mt-2"><div><span class="eyebrow">Akses staf</span><h2>Kelola pengguna</h2></div><p>Atur akun dan peran untuk tim toko.</p></div>
<div class="row g-4"><div class="col-md-4"><div class="card p-3"><h5><?=$edit?'Edit':'Tambah'?> user</h5>
<form method="post"><?=csrf_field()?><input type="hidden" name="aksi" value="simpan"><input type="hidden" name="id" value="<?=$edit['id']??0?>">
<input name="nama" class="form-control mb-2" placeholder="Nama lengkap" value="<?=e($edit['nama_lengkap']??'')?>" required>
<input name="username" class="form-control mb-2" placeholder="Username" value="<?=e($edit['username']??'')?>" required>
<input name="password" type="password" class="form-control mb-2" placeholder="<?=$edit?'Password baru (kosongkan bila tidak diubah)':'Password'?>">
<select name="role" class="form-select mb-3"><option value="kasir">Kasir</option><option value="admin" <?=($edit['role']??'')=='admin'?'selected':''?>>Admin</option></select>
<button class="btn btn-primary">Simpan user</button> <?php if($edit): ?><a href="users.php" class="btn btn-link">Batal</a><?php endif; ?></form></div></div>
<div class="col-md-8"><div class="card"><table class="table mb-0"><tr><th>Nama</th><th>Username</th><th>Peran</th><th></th></tr>
<?php foreach($list as $u): ?><tr><td><?=e($u['nama_lengkap'])?></td><td><?=e($u['username'])?></td><td><?=e($u['role'])?></td><td class="d-flex gap-1"><a class="btn btn-sm btn-outline-primary" href="?edit=<?=$u['id']?>">Edit</a>
<form method="post" onsubmit="return confirm('Hapus user ini?')"><?=csrf_field()?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?=$u['id']?>"><button class="btn btn-sm btn-outline-danger">Hapus</button></form></td></tr>
<?php endforeach; ?></table></div></div></div>
<?php foot();
