<?php
$labels = array('pending' => 'Antre', 'sending' => 'Mengirim', 'sent' => 'Terkirim', 'failed' => 'Gagal');
$mask = function ($email) {$p = explode('@', (string) $email, 2); return count($p) === 2 ? mb_substr($p[0], 0, 2) . '•••@' . $p[1] : '•••';};
$bulan = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
$fmt = function ($dt) use ($bulan) {$t = strtotime($dt); return date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y, H:i', $t);};
$base = site_url('admin/antrean-email');
$server = $smtp_source === 'server';
?>
<div class="ops ops-outbox eq3">
<?= ops_hero('Pengaturan Sistem', 'Antrean Notifikasi Email', 'Kelola antrean pengiriman email notifikasi ke warga (verifikasi, pemberitahuan, dan lainnya).', array(), 'mail') ?>
<?= ops_flash() ?>

<?php if (!$smtp_ready || !$enabled): ?>
<section class="eq3-notice" role="status"><span class="eq3-notice-ic" aria-hidden="true"><?= official_svg('alert') ?></span>
 <p><?= !$smtp_ready ? 'SMTP email belum dikonfigurasi. Notifikasi email belum dapat dikirim.' : 'Notifikasi email belum diaktifkan. Email ke warga belum diantrekan.' ?></p>
 <button class="eq3-btn outline" type="button" data-eq-open="eq-smtp"><?= official_svg('gear') ?>Atur SMTP</button>
</section>
<?php endif; ?>

<section class="eq3-stats" aria-label="Ringkasan antrean">
 <div class="eq3-stat orange"><span class="eq3-stat-ic"><?= official_svg('send') ?></span><div><strong><?= (int) ($counts['pending'] ?? 0) + (int) ($counts['sending'] ?? 0) ?></strong><small>Menunggu dikirim</small></div></div>
 <div class="eq3-stat green"><span class="eq3-stat-ic"><?= official_svg('check') ?></span><div><strong><?= (int) ($counts['sent'] ?? 0) ?></strong><small>Terkirim</small></div></div>
 <div class="eq3-stat red"><span class="eq3-stat-ic"><?= official_svg('close') ?></span><div><strong><?= (int) ($counts['failed'] ?? 0) ?></strong><small>Gagal</small></div></div>
</section>

<section class="ops-card eq3-card">
 <header class="eq3-head"><div><h2>Daftar Antrean Email</h2><p>Daftar email notifikasi yang akan dikirim ke warga. Alamat penerima disamarkan untuk menjaga privasi.</p></div>
  <div class="eq3-head-actions">
   <?php if ($smtp_ready): ?><button class="eq3-btn ghost" type="button" data-eq-open="eq-smtp"><?= official_svg('gear') ?>Atur SMTP</button><?php endif; ?>
   <?php if ($rows): ?><button class="eq3-btn outline" type="button" data-eq-open="eq-buat"><?= official_svg('mail') ?>Buat Notifikasi Email</button><?php endif; ?>
   <form method="post" action="<?= $base ?>"><?= ops_csrf() ?><input type="hidden" name="act" value="process"><button class="eq3-btn" type="submit"><?= official_svg('send') ?>Proses Antrean Sekarang</button></form>
  </div>
 </header>
 <form method="post" action="<?= $base ?>" id="eq-bulk-form"><?= ops_csrf() ?><input type="hidden" name="act" value="bulk">
  <div class="eq3-bulk" data-eq-bulk hidden><span><b data-eq-count>0</b> email dipilih</span>
   <button class="eq3-btn small outline" type="submit" name="bulk_action" value="retry"><?= official_svg('swap') ?>Coba lagi</button>
   <button class="eq3-btn small danger" type="submit" name="bulk_action" value="delete" data-eq-confirm="Hapus email terpilih dari antrean? Email yang sudah terkirim tidak ikut terhapus."><?= official_svg('trash') ?>Hapus dari antrean</button>
  </div>
  <div class="eq3-table-wrap"><table class="eq3-table"><thead><tr><th class="eq3-chk"><input type="checkbox" class="eq3-check" data-eq-all aria-label="Pilih semua email"<?= $rows ? '' : ' disabled' ?>></th><th>Waktu Dibuat</th><th>Penerima</th><th>Subjek</th><th class="num">Percobaan</th><th>Status</th><th><span class="sr-only">Aksi</span></th></tr></thead><tbody>
  <?php foreach ($rows as $r): ?>
   <tr>
    <td class="eq3-chk"><input type="checkbox" class="eq3-check" name="ids[]" value="<?= (int) $r['id'] ?>" data-eq-row aria-label="Pilih email <?= ops_e($r['subject']) ?>"></td>
    <td data-label="Waktu Dibuat"><?= ops_e($fmt($r['created_at'])) ?></td>
    <td data-label="Penerima"><?= ops_e($mask($r['recipient'])) ?></td>
    <td data-label="Subjek" class="eq3-subject"><strong><?= ops_e($r['subject']) ?></strong><?php if ($r['last_error']): ?><small><?= ops_e(mb_substr($r['last_error'], 0, 140)) ?></small><?php endif; ?></td>
    <td data-label="Percobaan" class="num"><?= (int) $r['attempts'] ?></td>
    <td data-label="Status"><span class="eq3-status <?= ops_e($r['status']) ?>"><i></i><?= ops_e($labels[$r['status']] ?? $r['status']) ?></span><?php if ($r['sent_at']): ?><small class="eq3-sent"><?= ops_e($fmt($r['sent_at'])) ?></small><?php endif; ?></td>
    <td><?php if ($r['status'] === 'failed'): ?><button class="eq3-link" type="submit" form="eq-retry-<?= (int) $r['id'] ?>">Coba lagi</button><?php endif; ?></td>
   </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="7" class="eq3-empty"><img src="<?= base_url('assets/img/admin-ops/email-empty.svg') ?>" alt="" width="140" height="108"><h3>Belum ada antrean email</h3><p>Ketika ada notifikasi yang perlu dikirim, antrean akan muncul di sini.</p><button class="eq3-btn outline" type="button" data-eq-open="eq-buat"><?= official_svg('mail') ?>Buat Notifikasi Email</button></td></tr><?php endif; ?>
  </tbody></table></div>
 </form>
 <?php foreach ($rows as $r): if ($r['status'] !== 'failed') continue; ?><form id="eq-retry-<?= (int) $r['id'] ?>" method="post" action="<?= $base ?>" hidden><?= ops_csrf() ?><input type="hidden" name="act" value="retry"><input type="hidden" name="outbox_id" value="<?= (int) $r['id'] ?>"></form><?php endforeach; ?>
</section>

<dialog class="eq3-dialog" id="eq-smtp" aria-labelledby="eq-smtp-t">
 <header><h2 id="eq-smtp-t"><?= official_svg('gear') ?>Pengaturan SMTP Email</h2><button type="button" class="eq3-x" data-eq-close aria-label="Tutup">&times;</button></header>
 <?php if ($server): ?>
  <p class="eq3-info">SMTP diatur oleh server melalui environment/config (<code>SMTP_HOST</code>). Konfigurasi hanya dapat diubah oleh pengelola server. Host: <b><?= ops_e($smtp['smtp_host']) ?></b>, pengirim: <b><?= ops_e($smtp['from_email']) ?></b>.</p>
 <?php endif; ?>
 <form method="post" action="<?= $base ?>" class="eq3-form" autocomplete="off"><?= ops_csrf() ?><input type="hidden" name="act" value="smtp_save">
  <fieldset<?= $server ? ' disabled' : '' ?>>
   <div class="eq3-grid">
    <label>Host SMTP<input name="smtp_host" value="<?= ops_e($smtp['smtp_host']) ?>" placeholder="smtp.gmail.com" maxlength="190"></label>
    <label>Port<input name="smtp_port" type="number" min="1" max="65535" value="<?= (int) ($smtp['smtp_port'] ?: 587) ?>"></label>
    <label>Enkripsi<select name="smtp_crypto"><?php foreach (array('tls' => 'TLS (587)', 'ssl' => 'SSL (465)', 'none' => 'Tanpa enkripsi') as $k => $l): ?><option value="<?= $k ?>"<?= ($smtp['smtp_crypto'] === $k || ($k === 'none' && $smtp['smtp_crypto'] === '')) ? ' selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></label>
    <label>Username<input name="smtp_user" value="<?= ops_e($smtp['smtp_user']) ?>" placeholder="akun@domain.com" maxlength="190" autocomplete="off"></label>
    <label class="wide">Password<input name="smtp_pass" type="password" placeholder="<?= $smtp['has_pass'] ? '•••••••• (tersimpan, kosongkan bila tidak diubah)' : 'Password / app password SMTP' ?>" autocomplete="new-password">
     <?php if ($smtp['has_pass']): ?><span class="eq3-check-row"><input type="checkbox" name="clear_pass" value="1"> Hapus password tersimpan</span><?php endif; ?></label>
    <label>Email pengirim<input name="from_email" type="email" value="<?= ops_e($smtp['from_email']) ?>" placeholder="noreply@domain.com" maxlength="190"></label>
    <label>Nama pengirim<input name="from_name" value="<?= ops_e($smtp['from_name']) ?>" maxlength="120"></label>
   </div>
  </fieldset>
  <label class="eq3-toggle"><input type="checkbox" name="notify_email" value="1"<?= $enabled ? ' checked' : '' ?>><span>Kirim salinan notifikasi ke email warga yang menyetujui</span></label>
  <p class="eq3-note">Password disimpan terenkripsi dan tidak pernah ditampilkan kembali. Untuk Gmail gunakan <i>App Password</i>.</p>
  <div class="eq3-dialog-actions"><button type="button" class="eq3-btn ghost" data-eq-close>Batal</button><button class="eq3-btn" type="submit"><?= official_svg('save') ?>Simpan Konfigurasi</button></div>
 </form>
 <form method="post" action="<?= $base ?>" class="eq3-test"><?= ops_csrf() ?><input type="hidden" name="act" value="smtp_test">
  <label>Kirim email uji<span><input name="test_email" type="email" required placeholder="alamat@email.com" maxlength="190"><button class="eq3-btn outline" type="submit"<?= $smtp_ready ? '' : ' disabled title="Simpan konfigurasi SMTP terlebih dahulu"' ?>><?= official_svg('send') ?>Kirim Email Uji</button></span></label>
 </form>
</dialog>

<dialog class="eq3-dialog" id="eq-buat" aria-labelledby="eq-buat-t">
 <header><h2 id="eq-buat-t"><?= official_svg('mail') ?>Buat Notifikasi Email</h2><button type="button" class="eq3-x" data-eq-close aria-label="Tutup">&times;</button></header>
 <p class="eq3-info">Dikirim ke <b><?= (int) $consenting ?> warga</b> yang mengisi email dan menyetujui notifikasi email di profilnya.<?= !$enabled ? ' <b>Notifikasi email belum diaktifkan</b> — aktifkan lewat Atur SMTP.' : '' ?></p>
 <form method="post" action="<?= $base ?>" class="eq3-form"><?= ops_csrf() ?><input type="hidden" name="act" value="broadcast">
  <label>Subjek<input name="subject" required minlength="3" maxlength="150" placeholder="Contoh: Pengumuman Kerja Bakti Minggu Ini"></label>
  <label>Isi pesan<textarea name="body" required minlength="5" maxlength="3000" rows="6" placeholder="Tulis isi pemberitahuan untuk warga..."></textarea></label>
  <p class="eq3-note">Jangan cantumkan NIK, nomor KK, atau data pribadi. Tautan portal warga dan keterangan berhenti berlangganan ditambahkan otomatis.</p>
  <div class="eq3-dialog-actions"><button type="button" class="eq3-btn ghost" data-eq-close>Batal</button><button class="eq3-btn" type="submit"<?= $enabled ? '' : ' disabled' ?>><?= official_svg('send') ?>Masukkan ke Antrean</button></div>
 </form>
</dialog>
</div>
<link rel="stylesheet" href="<?= base_url('assets/email-queue-v3.css') ?>?v=<?= filemtime(FCPATH . 'assets/email-queue-v3.css') ?>">
<script>
(()=>{const open=id=>{const d=document.getElementById(id);if(d&&!d.open)d.showModal();};
document.querySelectorAll('[data-eq-open]').forEach(b=>b.addEventListener('click',()=>open(b.dataset.eqOpen)));
document.querySelectorAll('.eq3-dialog').forEach(d=>{d.querySelectorAll('[data-eq-close]').forEach(x=>x.addEventListener('click',()=>d.close()));d.addEventListener('click',e=>{if(e.target===d)d.close();});});
const h=location.hash;if(h==='#smtp')open('eq-smtp');if(h==='#buat')open('eq-buat');
const all=document.querySelector('[data-eq-all]'),rows=[...document.querySelectorAll('[data-eq-row]')],bar=document.querySelector('[data-eq-bulk]'),cnt=document.querySelector('[data-eq-count]');
const sync=()=>{const n=rows.filter(r=>r.checked).length;if(bar)bar.hidden=!n;if(cnt)cnt.textContent=n;if(all){all.checked=n&&n===rows.length;all.indeterminate=n>0&&n<rows.length;}};
all?.addEventListener('change',()=>{rows.forEach(r=>r.checked=all.checked);sync();});rows.forEach(r=>r.addEventListener('change',sync));sync();
document.querySelectorAll('[data-eq-confirm]').forEach(b=>b.addEventListener('click',e=>{if(!confirm(b.dataset.eqConfirm))e.preventDefault();}));})();
</script>
