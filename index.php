<?php require 'config/koneksi.php';
$kat=$pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
$prod=$pdo->query("SELECT p.*,k.nama_kategori FROM produk p JOIN kategori k ON k.id=p.kategori_id ORDER BY p.nama_produk")->fetchAll();
$promo=array_filter($prod,fn($p)=>$p['is_promo']);
function card($p){ ?>
<div class="col-6 col-md-4 col-lg-3 item" data-k="<?=$p['kategori_id']?>"><div class="card h-100">
<?php if($p['foto']): ?><img src="<?=BASE?>assets/uploads/<?=e($p['foto'])?>" alt="<?=e($p['nama_produk'])?>"><?php else: ?><div class="ph">🧁</div><?php endif; ?>
<div class="card-body d-flex flex-column"><small class="text-muted"><?=e($p['nama_kategori'])?></small>
<h6><?=e($p['nama_produk'])?> <?php if($p['is_promo']): ?><span class="badge bg-danger">Promo</span><?php endif; ?></h6>
<p class="small text-muted mb-1"><?=e($p['deskripsi'])?></p><b class="mb-2"><?=rp($p['harga'])?></b>
<?php if($p['stok']>0): ?>
<div class="mt-auto input-group input-group-sm"><input type="number" min="1" max="<?=$p['stok']?>" value="1" class="form-control qty" aria-label="Jumlah <?=e($p['nama_produk'])?>">
<button type="button" class="btn btn-primary cart-add" data-id="<?=$p['id']?>">Tambah</button></div>
<?php else: ?><span class="badge bg-secondary mt-auto">Stok habis</span><?php endif; ?></div></div></div>
<?php }
head(NAMA_TOKO); ?>
<section class="hero mb-4"><span class="eyebrow">Dibuat dengan sepenuh hati</span><h1>Kue hangat, momen yang lebih manis.</h1>
<p class="lead">Temukan kue favorit yang dibuat segar setiap hari. Pilih dari katalog dan pesan langsung lewat WhatsApp.</p>
<div class="hero-actions"><a href="#katalog" class="btn btn-light">Jelajahi katalog <span aria-hidden="true">→</span></a>
<a href="login.php" class="btn btn-outline-light">Masuk staf</a></div></section>
<?php if($promo): ?><div class="section-heading"><div><span class="eyebrow">Pilihan istimewa</span><h2>Promo hari ini</h2></div><p>Jangan lewatkan kue favorit dengan penawaran spesial.</p></div><div class="row g-3 mb-5"><?php foreach($promo as $p) card($p); ?></div><?php endif; ?>
<div id="katalog" class="section-heading"><div><span class="eyebrow">Dari dapur kami</span><h2>Katalog kue</h2></div><p>Pilih yang paling menggugah selera.</p></div>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
<button class="btn btn-sm btn-primary filt" data-k="">Semua</button>
<?php foreach($kat as $k): ?><button class="btn btn-sm btn-outline-secondary filt" data-k="<?=$k['id']?>"><?=e($k['nama_kategori'])?></button><?php endforeach; ?></div>
<div class="row g-3 mb-5" id="kat"><?php foreach($prod as $p) card($p); ?></div>
<button class="btn btn-primary cart-fab" type="button" data-bs-toggle="offcanvas" data-bs-target="#shoppingCart" aria-controls="shoppingCart">
<span aria-hidden="true">🛒</span> Keranjang <span class="cart-count" id="cartCount" aria-live="polite">0</span>
</button>
<div class="offcanvas offcanvas-end shopping-cart" tabindex="-1" id="shoppingCart" aria-labelledby="shoppingCartTitle">
<div class="offcanvas-header"><div><span class="eyebrow">Pesanan Anda</span><h2 class="offcanvas-title" id="shoppingCartTitle">Keranjang</h2></div>
<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button></div>
<div class="offcanvas-body d-flex flex-column">
<div id="cartItems" class="cart-items flex-grow-1"></div>
<div class="cart-summary"><div class="d-flex justify-content-between align-items-center mb-3"><span>Total pesanan</span><strong id="cartTotal">Rp 0</strong></div>
<a id="checkoutButton" class="btn btn-primary w-100 py-2" href="#" target="_blank" rel="noopener" aria-disabled="true">Lanjut pesan via WhatsApp</a>
<p class="small text-muted text-center mt-2 mb-0">Total belum termasuk ongkos kirim.</p></div>
</div></div>
<script>
const WA=<?=json_encode(WA_NUMBER,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT)?>;
const SHOP=<?=json_encode(NAMA_TOKO,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT)?>;
const PRODUCTS=<?=json_encode(array_column(array_map(fn($p)=>['id'=>(int)$p['id'],'name'=>$p['nama_produk'],'price'=>(float)$p['harga'],'stock'=>(int)$p['stok']],$prod),null,'id'),JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT)?>;
const cart={};
const money=new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0});
const escapeHtml=s=>String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const cartItems=document.getElementById('cartItems');
function renderCart(){
 const lines=Object.entries(cart).filter(([,qty])=>qty>0);
 const count=lines.reduce((sum,[,qty])=>sum+qty,0);
 const total=lines.reduce((sum,[id,qty])=>sum+PRODUCTS[id].price*qty,0);
 document.getElementById('cartCount').textContent=count;
 document.getElementById('cartTotal').textContent=money.format(total);
 document.querySelectorAll('.cart-add').forEach(button=>{
  const product=PRODUCTS[button.dataset.id],remaining=product.stock-(cart[button.dataset.id]||0);
  const input=button.closest('.item').querySelector('.qty');
  input.max=Math.max(1,remaining);
  button.disabled=remaining<=0;
  button.textContent=remaining<=0?'Stok penuh':'Tambah';
 });
 if(!lines.length){
  cartItems.innerHTML='<div class="cart-empty"><span>🧺</span>Keranjang masih kosong.<br>Yuk, pilih kue favoritmu!</div>';
  const checkout=document.getElementById('checkoutButton');
  checkout.href='#';checkout.setAttribute('aria-disabled','true');checkout.classList.add('disabled');
  return;
 }
 cartItems.innerHTML=lines.map(([id,qty])=>{
  const p=PRODUCTS[id];
  return `<div class="cart-line"><div class="cart-line-info"><strong>${escapeHtml(p.name)}</strong><span>${money.format(p.price)} × ${qty}</span></div>
  <div class="cart-line-actions"><div class="btn-group btn-group-sm" role="group" aria-label="Jumlah ${escapeHtml(p.name)}">
  <button type="button" class="btn btn-outline-secondary cart-change" data-id="${id}" data-step="-1" aria-label="Kurangi">−</button>
  <span class="cart-qty">${qty}</span>
  <button type="button" class="btn btn-outline-secondary cart-change" data-id="${id}" data-step="1" aria-label="Tambah">+</button>
  </div><strong>${money.format(p.price*qty)}</strong></div></div>`;
 }).join('');
 const message=['Halo '+SHOP+', saya ingin memesan:','',...lines.map(([id,qty])=>`${PRODUCTS[id].name} x ${qty} = ${money.format(PRODUCTS[id].price*qty)}`),'','Total: '+money.format(total)].join('\n');
 const checkout=document.getElementById('checkoutButton');
 checkout.href='https://wa.me/'+WA+'?text='+encodeURIComponent(message);
 checkout.removeAttribute('aria-disabled');checkout.classList.remove('disabled');
}
document.querySelectorAll('.cart-add').forEach(button=>button.addEventListener('click',()=>{
 const item=button.closest('.item'),id=button.dataset.id,product=PRODUCTS[id];
 const input=item.querySelector('.qty');
 const requested=Math.max(1,Math.min(product.stock,parseInt(input.value,10)||1));
 const current=cart[id]||0;
 if(current+requested>product.stock){
  input.value=product.stock-current;
  button.textContent=current>=product.stock?'Stok penuh':'Stok tidak cukup';
  button.disabled=current>=product.stock;
  setTimeout(()=>{button.textContent='Tambah';button.disabled=false;},1300);
  return;
 }
 cart[id]=current+requested;
 input.value=1;
 renderCart();
}));
document.getElementById('cartItems').addEventListener('click',event=>{
 const button=event.target.closest('.cart-change');
 if(!button)return;
 const id=button.dataset.id,next=(cart[id]||0)+Number(button.dataset.step);
 if(next<=0)delete cart[id];else cart[id]=Math.min(PRODUCTS[id].stock,next);
 renderCart();
});
document.querySelectorAll('.filt').forEach(b=>b.onclick=()=>{document.querySelectorAll('.filt').forEach(x=>x.className='btn btn-sm filt btn-outline-secondary');b.className='btn btn-sm filt btn-primary';
 document.querySelectorAll('#kat .item').forEach(i=>i.style.display=(!b.dataset.k||i.dataset.k==b.dataset.k)?'':'none');});
renderCart();
</script>
<?php foot();
