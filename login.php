<?php require 'config/koneksi.php';
if(!empty($_SESSION['user'])){ header('Location: '.($_SESSION['user']['role']=='admin'?'admin/':'kasir/')); exit; }
$err='';
if($_SERVER['REQUEST_METHOD']=='POST'){ csrf_check();
  $s=$pdo->prepare("SELECT * FROM users WHERE username=?"); $s->execute([trim($_POST['username'])]); $u=$s->fetch();
  if($u && password_verify($_POST['password'],$u['password'])){ session_regenerate_id(true); unset($u['password']); $_SESSION['user']=$u;
    header('Location: '.($u['role']=='admin'?'admin/':'kasir/')); exit; }
  $err='Username atau password salah.'; }
head('Masuk'); ?>
<div class="login-wrap"><section class="card login-card">
<div class="login-aside"><div><span class="brand-mark">🍰</span><h1>Senang melihatmu kembali.</h1>
<p>Kelola toko dengan mudah, dan terus hadirkan momen manis untuk pelanggan.</p></div><span class="small text-white-50"><?=e(NAMA_TOKO)?></span></div>
<div class="login-form"><span class="eyebrow">Area staf</span><h2>Masuk ke akun</h2><p>Silakan masukkan username dan password Anda.</p>
<?php if($err): ?><div class="alert alert-danger"><?=e($err)?></div><?php endif; ?>
<form method="post"><?=csrf_field()?>
<label for="username" class="form-label">Username</label><input id="username" name="username" class="form-control mb-3" placeholder="Masukkan username" required autofocus>
<label for="password" class="form-label">Password</label><input id="password" name="password" type="password" class="form-control mb-4" placeholder="Masukkan password" required>
<button class="btn btn-primary w-100 py-2">Masuk</button></form>
<a href="<?=BASE?>" class="small d-inline-block mt-3 text-muted text-decoration-none">← Kembali ke katalog</a></div></section></div>
<?php foot();
