/*
 * Form periksa KK versi HP: akordeon (Kartu Keluarga, Anggota, Riwayat, Verifikasi) + tombol Ambil Foto / Upload File.
 * Hanya tampilan: validasi & pengiriman tetap oleh assets/kk-scan.js. Di desktop kepala akordeon disembunyikan (CSS).
 */
(() => {
  const review = document.getElementById('kk-review');
  if (!review) return;
  const sections = [...document.querySelectorAll('.kks-acc')];
  const setOpen = (sec, open) => {
    sec.classList.toggle('is-open', open);
    const head = sec.querySelector('.kks-acc-head');
    if (head) head.setAttribute('aria-expanded', open ? 'true' : 'false');
  };
  sections.forEach(sec => {
    const head = sec.querySelector('.kks-acc-head');
    if (head) head.addEventListener('click', () => setOpen(sec, !sec.classList.contains('is-open')));
  });
  // Sebelum validasi kk-scan.js berjalan (fase capture), buka semua bagian agar kolom yang salah dapat difokuskan.
  document.addEventListener('submit', e => { if (e.target === review) sections.forEach(s => setOpen(s, true)); }, true);
  review.addEventListener('invalid', e => { const sec = e.target.closest('.kks-acc'); if (sec) setOpen(sec, true); }, true);
  // Tombol "Tambah Anggota Manual" / pensil anggota: pastikan bagian anggota terbuka.
  document.getElementById('kk-add')?.addEventListener('click', () => setOpen(document.getElementById('anggota'), true));

  // Tombol "Scan Ulang" bawaan (desktop) juga harus menampilkan panel unggah.
  document.getElementById('fui-rescan')?.addEventListener('click', () => document.getElementById('fui-upload-panel')?.classList.add('kks-shown'));
  // Jumlah anggota di kepala akordeon mengikuti #kk-count (diperbarui kk-scan.js).
  const src = document.getElementById('kk-count'), dst = document.querySelector('[data-kks-count]');
  if (src && dst) new MutationObserver(() => { dst.textContent = src.textContent; }).observe(src, { childList: true, characterData: true, subtree: true });

  // Ambil Foto / Upload File dari form: tampilkan panel unggah lalu buka kamera / pemilih file yang sama.
  const panel = document.getElementById('fui-upload-panel');
  document.querySelectorAll('[data-kks-pick]').forEach(btn => btn.addEventListener('click', () => {
    if (panel) { panel.hidden = false; panel.classList.add('kks-shown'); }
    if (btn.dataset.kksPick === 'camera') document.getElementById('kk-camera')?.click();
    else document.getElementById('kk-document')?.click();
    panel?.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion:reduce)').matches ? 'auto' : 'smooth', block: 'start' });
  }));
})();
