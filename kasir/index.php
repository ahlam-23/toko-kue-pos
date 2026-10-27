<?php require '../config/koneksi.php'; need(['admin','kasir']);
$kat=$pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
$prod=$pdo->query("SELECT id,kategori_id k,nama_produk n,harga h,stok s,foto f FROM produk ORDER BY nama_produk")->fetchAll();
head('Kasir'); ?>
<div class="section-heading mt-2"><div><span class="eyebrow">Kasir toko</span><h2>Buat transaksi</h2></div><p>Pilih kue untuk ditambahkan ke keranjang.</p></div>
<div class="row g-3">
<div class="col-lg-8">
 <div class="row g-2 mb-3"><div class="col-md-8"><input id="q" class="form-control" placeholder="Cari nama kue..."></div>
 <div class="col-md-4"><select id="k" class="form-select"><option value="">Semua kategori</option>
 <?php foreach($kat as $k): ?><option value="<?=$k['id']?>"><?=e($k['nama_kategori'])?></option><?php endforeach; ?></select></div></div>
 <div id="grid" class="row g-2"></div></div>
<div class="col-lg-4"><div class="card sticky-top" style="top:10px"><div class="card-header fw-bold">Keranjang</div><div class="card-body">
 <div id="cart" class="mb-2"></div><hr>
 <div class="d-flex justify-content-between fs-5"><b>Total</b><b id="total">Rp 0</b></div>
 <input id="bayar" type="number" min="0" class="form-control my-2" placeholder="Uang dibayar">
 <div class="d-flex justify-content-between"><span>Kembalian</span><b id="kembali">Rp 0</b></div>
 <button id="btnBayar" class="btn btn-success w-100 mt-3">Proses &amp; cetak nota</button>
 <button id="btnReset" class="btn btn-outline-secondary w-100 mt-2">Kosongkan</button>
</div></div></div></div>
<script>
const P=<?=json_encode($prod)?>, T=<?=json_encode($_SESSION['csrf'])?>, IMG='<?=BASE?>assets/uploads/'; let C={};
const rp=n=>'Rp '+Math.round(n).toLocaleString('id-ID');
const esc=s=>String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const $=id=>document.getElementById(id);
function grid(){const q=$('q').value.toLowerCase(),k=$('k').value;
 $('grid').innerHTML=P.filter(p=>p.n.toLowerCase().includes(q)&&(!k||p.k==k)).map(p=>`
 <div class="col-6 col-md-4 col-xl-3"><div class="card pcard h-100" onclick="add(${p.id})">
 ${p.f?`<img src="${IMG+esc(p.f)}">`:'<div class="ph">🧁</div>'}
 <div class="card-body p-2"><div class="small fw-bold">${esc(p.n)}</div><div>${rp(p.h)}</div>
 <span class="badge ${p.s<5?'bg-danger':'bg-secondary'}">Stok ${p.s}</span></div></div></div>`).join('')||'<p class="text-muted">Kue tidak ditemukan.</p>';}
function add(id){const p=P.find(x=>x.id==id);if(p.s<=(C[id]||0))return alert('Stok '+p.n+' tidak cukup');C[id]=(C[id]||0)+1;draw();}
function chg(id,d){const p=P.find(x=>x.id==id),n=(C[id]||0)+d;if(n>p.s)return alert('Stok tidak cukup');if(n<=0)delete C[id];else C[id]=n;draw();}
function total(){return Object.entries(C).reduce((s,[id,q])=>s+q*parseFloat(P.find(x=>x.id==id).h),0);}
function draw(){const e=Object.entries(C);
 $('cart').innerHTML=e.length?e.map(([id,q])=>{const p=P.find(x=>x.id==id);return `<div class="d-flex justify-content-between align-items-center mb-2">
 <div><div class="small fw-bold">${esc(p.n)}</div><div class="small text-muted">${rp(p.h)} × ${q} = ${rp(p.h*q)}</div></div>
 <div class="btn-group btn-group-sm"><button class="btn btn-outline-secondary" onclick="chg(${id},-1)">−</button><button class="btn btn-outline-secondary" onclick="chg(${id},1)">+</button></div></div>`}).join(''):'<div class="cart-empty"><span>🧺</span>Klik kue untuk mulai mengisi keranjang.</div>';
 $('total').textContent=rp(total());hitung();}
function hitung(){const k=parseFloat($('bayar').value||0)-total();$('kembali').textContent=rp(k>0?k:0);}
$('bayar').oninput=hitung;$('q').oninput=grid;$('k').onchange=grid;
$('btnReset').onclick=()=>{C={};$('bayar').value='';draw();};
$('btnBayar').onclick=async()=>{
 const items=Object.entries(C).map(([id,qty])=>({id:+id,qty})),bayar=parseFloat($('bayar').value||0);
 if(!items.length)return alert('Keranjang masih kosong');
 if(bayar<total())return alert('Uang dibayar kurang dari total');
 $('btnBayar').disabled=true;
 try{const r=await fetch('simpan_trx.php',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF':T},body:JSON.stringify({items,bayar})});
  const d=await r.json();if(d.ok)location.href='cetak_nota.php?id='+d.id;else{alert(d.msg);$('btnBayar').disabled=false;}}
 catch(e){alert('Gagal menyimpan transaksi');$('btnBayar').disabled=false;}};
grid();draw();
</script>
<?php foot();
