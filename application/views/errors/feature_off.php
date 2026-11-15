<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Layanan sedang tidak tersedia</title>
<style>
:root{--navy:#082654;--muted:#5f708a;--orange:#f0501a;--line:#edf0f4}
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:grid;place-items:center;padding:16px;background:#fbf8f3;color:var(--navy);font:15px/1.55 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}
main{width:100%;max-width:520px;padding:28px 24px;border:1px solid var(--line);border-radius:18px;background:#fff;box-shadow:0 10px 30px #26344c14;text-align:center}
.ic{width:64px;height:64px;margin:0 auto 12px;display:grid;place-items:center;border-radius:18px;background:#fff1e6;color:var(--orange)}
h1{margin:0 0 8px;font-size:22px;line-height:1.25}
p{margin:0 0 6px;color:var(--muted)}
a{display:inline-flex;align-items:center;min-height:42px;margin-top:16px;padding:0 18px;border-radius:11px;background:var(--orange);color:#fff;font-weight:700;text-decoration:none}
</style>
</head>
<body>
<main>
 <div class="ic" aria-hidden="true"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M5.6 5.6l12.8 12.8"/></svg></div>
 <h1>Layanan sedang tidak tersedia</h1>
 <p>Layanan <strong><?= htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8') ?></strong> sedang dinonaktifkan oleh pengurus RT.</p>
 <p>Data Anda tetap tersimpan dan dapat diakses kembali saat layanan diaktifkan.</p>
 <a href="<?= htmlspecialchars((string) $back, ENT_QUOTES, 'UTF-8') ?>">← Kembali ke beranda</a>
</main>
</body>
</html>
