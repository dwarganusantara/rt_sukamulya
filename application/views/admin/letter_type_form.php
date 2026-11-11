<?php
$v = function ($k, $d = '') use ($item) { return $item[$k] ?? $d; };
$editing = (string) $this->uri->segment(3) !== ''; // admin/letter_type/{kode} = ubah
$categories = array('Kependudukan', 'Keterangan', 'Kegiatan', 'Lainnya');
$placeholders = array('nik_warga', 'nama_warga', 'alamat_domisili', 'jenis_kelamin', 'tanggal_lahir');
$more = array('tempat_tanggal_lahir', 'agama', 'pekerjaan', 'alamat_ktp', 'keperluan_surat', 'nama_ketua_rt', 'jabatan_ketua_rt', 'wilayah_rt', 'nomor_rt', 'nomor_rw');
$svg = function ($d, $w = 20) {return '<svg width="' . $w . '" height="' . $w . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $d . '"/></svg>';};
$i = array(
 'doc' => 'M6 3h9l4 4v14H6zM14 3v5h5M9 12h7M9 16h7', 'tag' => 'M3 12V4h8l10 10-8 8zM7.5 8h.01', 'layers' => 'm12 3 10 5-10 5L2 8zM2 12l10 5 10-5M2 17l10 5 10-5',
 'sort' => 'M4 6h10M4 12h7M4 18h4M17 4v16M14 17l3 3 3-3', 'image' => 'M3 5h18v14H3zM3 16l5-5 4 4 3-3 6 6M15 9h.01', 'trash' => 'M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13M10 11v6M14 11v6',
 'info' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM12 11v6M12 7.5h.01', 'back' => 'M19 12H5M11 6l-6 6 6 6', 'save' => 'M5 3h11l3 3v15H5zM8 3v6h8V3M8 21v-7h8v7', 'x' => 'M6 6l12 12M18 6 6 18',
 'bulb' => 'M9 18h6M10 21h4M12 3a6 6 0 0 0-3.5 10.9c.6.5 1 1.2 1 2.1h5c0-.9.4-1.6 1-2.1A6 6 0 0 0 12 3z');
$icon_url = $v('icon_path') ? base_url(html_escape($v('icon_path'))) : '';
?>
<link rel="stylesheet" href="<?= base_url('assets/letter-type-form.css') ?>?v=<?= filemtime(FCPATH . 'assets/letter-type-form.css') ?>">
<div class="lt">
 <header class="lt-hero">
  <a class="lt-back" href="<?= site_url('admin/letter_types') ?>" aria-label="Kembali ke daftar jenis surat"><?= $svg($i['back'], 24) ?></a>
  <span class="lt-hero-ic" aria-hidden="true"><?= $svg($i['doc'], 30) ?></span>
  <div><h1><?= $editing ? 'Edit' : 'Tambah' ?> Jenis Surat</h1><p>Perubahan akan langsung tampil pada katalog warga dan hasil surat.</p></div>
  <svg class="lt-hero-art" viewBox="0 0 260 120" aria-hidden="true"><ellipse cx="120" cy="112" rx="110" ry="8" fill="#ffe2c2"/><path d="M40 112c0-30 20-48 42-52-8 18-6 34 6 52z" fill="#8fd16a"/><path d="M60 112c4-28 26-44 46-44-12 14-12 30-2 44z" fill="#5fb848"/><g transform="rotate(-12 160 60)"><rect x="118" y="14" width="78" height="96" rx="8" fill="#fff" stroke="#c9d6ee" stroke-width="2"/><rect x="128" y="30" width="56" height="5" rx="2.5" fill="#7aa5ef"/><rect x="128" y="44" width="48" height="5" rx="2.5" fill="#c3d4f3"/><rect x="128" y="58" width="52" height="5" rx="2.5" fill="#c3d4f3"/><rect x="128" y="72" width="40" height="5" rx="2.5" fill="#c3d4f3"/><path d="M196 14l-14 0 14 14z" fill="#e8eefb"/></g><rect x="176" y="22" width="34" height="40" rx="5" fill="#4b7be5" transform="rotate(10 193 42)"/><path d="M210 10l6-8M220 18l9-3M204 6l-1-6" stroke="#ff9a1f" stroke-width="3.5" stroke-linecap="round"/><circle cx="212" cy="84" r="22" fill="#22a447"/><path d="M212 74v20M202 84h20" stroke="#fff" stroke-width="5" stroke-linecap="round"/></svg>
 </header>
 <?php if ($error): ?><div class="error lt-error" role="alert"><?= html_escape($error) ?></div><?php endif; ?>

 <div class="lt-grid">
  <form class="lt-card" method="post" enctype="multipart/form-data" novalidate data-lt-form>
   <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
   <h2><span class="lt-h-ic orange"><?= $svg($i['doc'], 20) ?></span>Informasi Jenis Surat</h2>
   <div class="lt-two">
    <div class="lt-field">
     <label for="lt-key">Kode Jenis Surat <b>*</b> <span class="lt-tip" tabindex="0" role="note" aria-label="Kode dipakai sistem dan tautan; tidak dapat diubah setelah disimpan." data-tip="Kode dipakai sistem & tautan; tidak dapat diubah setelah disimpan."><?= $svg($i['info'], 16) ?></span></label>
     <small>Huruf kecil, angka, garis bawah; tidak bisa diubah setelah disimpan.</small>
     <span class="lt-input"><?= $svg($i['tag']) ?><input id="lt-key" name="type_key" required pattern="[a-z0-9_]{3,40}" maxlength="40" placeholder="Contoh: surat_domisili" value="<?= html_escape($v('type_key')) ?>"<?= $editing ? ' readonly' : '' ?> data-lt-key></span>
    </div>
    <div class="lt-field lt-field-down">
     <label for="lt-title">Nama Jenis Surat <b>*</b></label>
     <span class="lt-input"><?= $svg($i['doc']) ?><input id="lt-title" name="title" required minlength="3" maxlength="160" placeholder="Contoh: Surat Keterangan Domisili" value="<?= html_escape($v('title')) ?>"></span>
    </div>
    <div class="lt-field">
     <label for="lt-cat">Kategori <b>*</b></label>
     <span class="lt-input"><?= $svg($i['layers']) ?><select id="lt-cat" name="category" required><?php foreach ($categories as $c): ?><option value="<?= $c ?>"<?= $v('category', 'Kependudukan') === $c ? ' selected' : '' ?>><?= $c ?></option><?php endforeach; ?></select></span>
    </div>
    <div class="lt-field">
     <label for="lt-sort">Urutan <b>*</b></label>
     <span class="lt-input lt-num"><span class="lt-num-ic"><?= $svg($i['sort']) ?></span><input id="lt-sort" type="number" name="sort_order" min="0" max="999" required value="<?= (int) $v('sort_order', 99) ?>"></span>
    </div>
   </div>
   <div class="lt-field">
    <label for="lt-desc">Deskripsi</label>
    <span class="lt-input lt-area"><?= $svg($i['doc']) ?><textarea id="lt-desc" name="description" maxlength="250" rows="3" placeholder="Tuliskan deskripsi singkat jenis surat..." data-lt-count="lt-desc-count"><?= html_escape($v('description')) ?></textarea></span>
    <small class="lt-counter" id="lt-desc-count" aria-live="polite"><?= mb_strlen((string) $v('description')) ?>/250</small>
   </div>
   <div class="lt-field">
    <label>Ikon Ilustrasi <?= $editing ? '' : '<b>*</b>' ?> <small class="inline">JPG, PNG, WebP, GIF, BMP, atau AVIF.<?= $editing ? 'Kosongkan bila tidak diganti.' : 'Wajib untuk jenis baru.' ?></small></label>
    <div class="lt-file">
     <label class="lt-file-box"><?= $svg($i['image']) ?><span class="lt-file-btn">Pilih File</span><span class="lt-file-name" data-lt-file-name><?= $editing && $icon_url ? 'Ikon saat ini dipakai' : 'Belum ada file dipilih' ?></span><input type="file" name="icon_image" accept="<?= image_accept() ?>" class="sr-only" data-lt-file<?= $editing ? '' : ' required' ?>></label>
     <span class="lt-preview" data-lt-preview data-initial="<?= $icon_url ?>"><?php if ($icon_url): ?><img src="<?= $icon_url ?>" alt="Pratinjau ikon"><?php else: ?><?= $svg($i['doc'], 30) ?><?php endif; ?></span>
     <button type="button" class="lt-clear" data-lt-clear aria-label="Batalkan file yang dipilih" title="Batalkan file yang dipilih"><?= $svg($i['trash'], 22) ?></button>
    </div>
   </div>

   <div class="lt-template">
    <h2><span class="lt-h-ic green"><?= $svg($i['doc'], 20) ?></span>Template Isi Surat</h2>
    <p>Kosongkan untuk menggunakan isi surat bawaan. Gunakan placeholder untuk data otomatis, seperti:</p>
    <div class="lt-chips" data-lt-chips>
     <?php foreach ($placeholders as $p): ?><button type="button" data-lt-insert="{{<?= $p ?>}}" title="Sisipkan ke template">{{<?= $p ?>}}</button><?php endforeach; ?>
     <?php foreach ($more as $p): ?><button type="button" data-lt-insert="{{<?= $p ?>}}" class="lt-more-chip" hidden title="Sisipkan ke template">{{<?= $p ?>}}</button><?php endforeach; ?>
     <button type="button" class="lt-chip-more" data-lt-more aria-expanded="false" aria-label="Tampilkan placeholder lainnya">…</button>
    </div>
    <div class="lt-field">
     <label for="lt-body">Isi Template</label>
     <span class="lt-input lt-area"><?= $svg($i['doc']) ?><textarea id="lt-body" name="body_template" rows="4" maxlength="5000" placeholder="Masukkan template isi surat atau biarkan kosong..." data-lt-body><?= html_escape($v('body_template')) ?></textarea></span>
     <small>Kop, judul, nomor surat, tanggal, tanda tangan, stempel, dan nama Ketua RT ditambahkan otomatis.</small>
    </div>
   </div>

   <label class="lt-check"><input type="checkbox" name="is_active" value="1"<?= !isset($item['is_active']) || $item['is_active'] ? ' checked' : '' ?>><span>Tampilkan kepada warga</span><span class="lt-tip" tabindex="0" role="note" aria-label="Bila tidak dicentang, jenis surat disembunyikan dari katalog warga." data-tip="Bila tidak dicentang, jenis surat disembunyikan dari katalog warga."><?= $svg($i['info'], 16) ?></span></label>
   <div class="lt-actions"><button class="lt-btn" type="submit"><?= $svg($i['save']) ?>Simpan Jenis Surat</button><a class="lt-btn ghost" href="<?= site_url('admin/letter_types') ?>"><?= $svg($i['x']) ?>Batal</a></div>
  </form>

  <aside class="lt-side">
   <div class="lt-art-card" aria-hidden="true">
    <svg viewBox="0 0 320 260"><ellipse cx="160" cy="248" rx="130" ry="9" fill="#e6f3df"/><path d="M40 240c-6-60 30-96 70-104-20 34-18 70 4 104z" fill="#7cc35a"/><path d="M28 210c10-40 40-58 66-56-22 18-28 40-24 62z" fill="#5fb848"/><path d="M286 240c8-62-26-100-66-110 18 36 14 74-8 110z" fill="#7cc35a"/><path d="M300 200c-8-38-36-56-60-56 20 18 24 40 20 60z" fill="#5fb848"/>
     <rect x="92" y="38" width="140" height="190" rx="14" fill="#ff9a2e"/><rect x="102" y="52" width="120" height="168" rx="8" fill="#fff"/><rect x="132" y="26" width="60" height="22" rx="8" fill="#ff8a1a"/><circle cx="162" cy="24" r="8" fill="#ff8a1a"/>
     <rect x="114" y="66" width="96" height="24" rx="6" fill="#2fa84f"/><text x="162" y="83" text-anchor="middle" font-family="Arial,sans-serif" font-weight="800" font-size="12" fill="#fff">JENIS SURAT</text>
     <g stroke-linecap="round"><path d="M118 112l7 7 12-14M118 148l7 7 12-14M118 184l7 7 12-14" stroke="#2fa84f" stroke-width="5" fill="none"/><path d="M146 112h52M146 122h40M146 148h52M146 158h40M146 184h52M146 194h36" stroke="#c9d4e6" stroke-width="5"/></g>
     <g transform="rotate(35 230 170)"><rect x="220" y="110" width="20" height="90" rx="4" fill="#ffc229"/><rect x="220" y="104" width="20" height="14" rx="3" fill="#2b3a55"/><path d="M220 200l10 18 10-18z" fill="#f2c79a"/><path d="M227 212l3 6 3-6z" fill="#2b3a55"/></g></svg>
   </div>
   <section class="lt-tips">
    <h2><span class="lt-tip-bulb" aria-hidden="true"><?= $svg($i['bulb'], 24) ?></span>Tips Pengisian</h2>
    <div class="lt-tip-item"><span class="orange"><?= $svg($i['tag'], 24) ?></span><div><strong>Kode Jenis Surat</strong><p>Gunakan format singkat, huruf kecil, dan unik.</p></div></div>
    <div class="lt-tip-item"><span class="blue"><?= $svg($i['layers'], 24) ?></span><div><strong>Kategori</strong><p>Kelompokkan sesuai jenis layanan (kependudukan, keterangan, kegiatan, dll).</p></div></div>
    <div class="lt-tip-item"><span class="green"><?= $svg($i['doc'], 24) ?></span><div><strong>Template Isi Surat</strong><p>Gunakan placeholder (mis. {{nama_warga}}) untuk otomatisasi data warga.</p></div></div>
   </section>
  </aside>
 </div>
</div>
<script>
(()=>{const f=document.querySelector('[data-lt-form]');if(!f)return;
const desc=f.querySelector('[data-lt-count]'),cnt=document.getElementById(desc?.dataset.ltCount);const upd=()=>{if(cnt)cnt.textContent=desc.value.length+'/'+desc.maxLength;};desc?.addEventListener('input',upd);upd();
const key=f.querySelector('[data-lt-key]');if(key&&!key.readOnly)key.addEventListener('input',()=>{const p=key.selectionStart;key.value=key.value.toLowerCase().replace(/[\s-]+/g,'_').replace(/[^a-z0-9_]/g,'');key.setSelectionRange(p,p);});
const file=f.querySelector('[data-lt-file]'),name=f.querySelector('[data-lt-file-name]'),prev=f.querySelector('[data-lt-preview]'),initial=prev.innerHTML,initName=name.textContent;
file.addEventListener('change',()=>{const x=file.files[0];if(!x){name.textContent=initName;prev.innerHTML=initial;return;}name.textContent=x.name;if(/^image\//.test(x.type)){const img=new Image();img.alt='Pratinjau ikon';img.src=URL.createObjectURL(x);prev.replaceChildren(img);}});
f.querySelector('[data-lt-clear]').addEventListener('click',()=>{file.value='';name.textContent=initName;prev.innerHTML=initial;});
const body=f.querySelector('[data-lt-body]');f.querySelectorAll('[data-lt-insert]').forEach(b=>b.addEventListener('click',()=>{const t=b.dataset.ltInsert,s=body.selectionStart??body.value.length,e=body.selectionEnd??s;body.setRangeText(t,s,e,'end');body.focus();}));
const more=f.querySelector('[data-lt-more]');more.addEventListener('click',()=>{const open=more.getAttribute('aria-expanded')!=='true';f.querySelectorAll('.lt-more-chip').forEach(c=>c.hidden=!open);more.setAttribute('aria-expanded',open);more.textContent=open?'Sembunyikan':'…';});
f.addEventListener('submit',e=>{if(!f.checkValidity()){e.preventDefault();f.reportValidity();}});})();
</script>
