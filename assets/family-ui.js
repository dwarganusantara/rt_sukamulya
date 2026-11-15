(() => {
  const toast = document.getElementById('fui-toast');
  let timer;
  const notify = message => {
    // Modal dialogs sit in the top layer; host the toast inside the open one so it stays visible.
    (document.querySelector('dialog.fui-dialog[open]') || document.querySelector('.fui-page')).appendChild(toast);
    toast.textContent = message;
    toast.hidden = false; clearTimeout(timer);
    timer = setTimeout(() => toast.hidden = true, 3000);
  };
  document.querySelectorAll('[data-copy]').forEach(button => button.addEventListener('click', async event => {
    event.stopPropagation();
    const label = button.dataset.copyLabel || 'Nomor KK';
    try {
      await navigator.clipboard.writeText(button.dataset.copy);
      notify(`${label} berhasil disalin`);
    } catch {
      notify(`${label} tidak dapat disalin di browser ini.`);
    }
  }));
  const open = id => {
    const dialog = document.getElementById(id);
    if (dialog && !dialog.open) dialog.showModal();
  };
  document.querySelectorAll('[data-dialog]').forEach(trigger => trigger.addEventListener('click', event => {
    // Row and its own button share the same target; open once.
    if (trigger.tagName === 'TR' && event.target.closest('button,a')) return;
    open(trigger.dataset.dialog);
  }));
  document.querySelectorAll('dialog.fui-dialog').forEach(dialog => {
    dialog.addEventListener('click', event => {
      // Click on the backdrop area closes the dialog.
      if (event.target !== dialog) return;
      const r = dialog.getBoundingClientRect();
      const inside = event.clientX >= r.left && event.clientX <= r.right && event.clientY >= r.top && event.clientY <= r.bottom;
      if (!inside) dialog.close();
    });
  });
  document.querySelectorAll('[data-close-dialog]').forEach(button => button.addEventListener('click', () => {
    button.closest('dialog').close();
  }));
})();
// Tautan riwayat juga membuka panel ketika diakses langsung.
(() => {
  const history = document.querySelector('.fui-family-overview #riwayat');
  const reveal = () => { if (history && location.hash === '#riwayat') history.open = true; };
  reveal();
  window.addEventListener('hashchange', reveal);
  document.querySelectorAll('a[href="#riwayat"]').forEach(link => link.addEventListener('click', () => { if (history) history.open = true; }));
})();
document.querySelectorAll('.fui-info-toggle').forEach(button => button.addEventListener('click', () => {
 const expanded = button.getAttribute('aria-expanded') !== 'true';
 document.getElementById(button.getAttribute('aria-controls')).classList.toggle('is-expanded', expanded);
 button.setAttribute('aria-expanded', String(expanded));
 button.textContent = expanded ? 'Tutup detail alamat' : 'Lihat detail alamat';
}));
