<?php require '../config/koneksi.php'; need(['admin','kasir']);
header('Content-Type: application/json');
function out($ok,$msg='',$id=0){ echo json_encode(['ok'=>$ok,'msg'=>$msg,'id'=>$id]); exit; }
if(!hash_equals($_SESSION['csrf'], $_SERVER['HTTP_X_CSRF'] ?? '')) out(false,'Token tidak valid');
$d=json_decode(file_get_contents('php://input'),true);
$items=$d['items']??[]; $bayar=(float)($d['bayar']??0);
if(!$items) out(false,'Keranjang kosong');
try{
 $pdo->beginTransaction();
 $rows=[];$total=0;
 foreach($items as $it){
  $qty=(int)$it['qty']; if($qty<1) throw new Exception('Jumlah tidak valid');
  $s=$pdo->prepare("SELECT id,nama_produk,harga,stok FROM produk WHERE id=? FOR UPDATE"); $s->execute([(int)$it['id']]); $p=$s->fetch();
  if(!$p) throw new Exception('Produk tidak ditemukan');
  if($p['stok']<$qty) throw new Exception("Stok {$p['nama_produk']} tidak cukup (sisa {$p['stok']})");
  $sub=$p['harga']*$qty; $total+=$sub; $rows[]=[$p['id'],$p['harga'],$qty,$sub];
 }
 if($bayar<$total) throw new Exception('Uang dibayar kurang');
 $n=$pdo->query("SELECT COUNT(*) FROM transaksi WHERE DATE(created_at)=CURDATE() FOR UPDATE")->fetchColumn()+1;
 $nota='TRX-'.date('Ymd').'-'.str_pad($n,3,'0',STR_PAD_LEFT);
 $pdo->prepare("INSERT INTO transaksi(no_nota,user_id,total_harga,bayar,kembali) VALUES(?,?,?,?,?)")->execute([$nota,$_SESSION['user']['id'],$total,$bayar,$bayar-$total]);
 $tid=$pdo->lastInsertId();
 $di=$pdo->prepare("INSERT INTO detail_transaksi(transaksi_id,produk_id,harga_satuan,jumlah,subtotal) VALUES(?,?,?,?,?)");
 $us=$pdo->prepare("UPDATE produk SET stok=stok-? WHERE id=?");
 foreach($rows as $r){ $di->execute([$tid,$r[0],$r[1],$r[2],$r[3]]); $us->execute([$r[2],$r[0]]); }
 $pdo->commit(); out(true,'',$tid);
}catch(Exception $e){ if($pdo->inTransaction()) $pdo->rollBack(); out(false,$e->getMessage()); }
