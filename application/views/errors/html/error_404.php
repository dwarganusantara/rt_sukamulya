<?php defined('BASEPATH') OR exit('No direct script access allowed');
// Teks bawaan CI diganti bahasa Indonesia; tidak menampilkan detail teknis.
$heading = 'Halaman tidak ditemukan';
$message = '<p>Alamat yang Anda buka tidak tersedia, sudah dipindahkan, atau Anda tidak memiliki akses ke data tersebut.</p>';
$status_code = 404; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?php echo htmlspecialchars(strip_tags($heading), ENT_QUOTES, 'UTF-8'); ?></title>
<style>
:root{--navy:#082654;--muted:#5f708a;--orange:#f0501a;--line:#edf0f4}
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:grid;place-items:center;padding:16px;background:#fbf8f3;color:var(--navy);font:15px/1.55 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}
main{width:100%;max-width:520px;padding:28px 24px;border:1px solid var(--line);border-radius:18px;background:#fff;box-shadow:0 10px 30px #26344c14;text-align:center}
.code{display:inline-block;margin-bottom:10px;padding:4px 12px;border-radius:999px;background:#fff1e8;color:var(--orange);font-weight:800;font-size:13px;letter-spacing:.06em}
h1{margin:0 0 8px;font-size:22px;line-height:1.25}
.msg{color:var(--muted)}.msg p{margin:0 0 6px}
nav{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-top:18px}
nav a{display:inline-flex;align-items:center;min-height:40px;padding:0 16px;border:1px solid var(--orange);border-radius:10px;color:var(--orange);font-weight:700;text-decoration:none}
nav a.primary{background:var(--orange);color:#fff}
</style>
</head>
<body>
<main>
 <span class="code"><?php echo isset($status_code) ? (int) $status_code : 500; ?></span>
 <h1><?php echo htmlspecialchars(strip_tags($heading), ENT_QUOTES, 'UTF-8'); ?></h1>
 <div class="msg"><?php echo $message; ?></div>
 <nav><a class="primary" href="javascript:history.back()">← Kembali</a><a href="<?php echo htmlspecialchars(function_exists('base_url') ? base_url() : '/', ENT_QUOTES, 'UTF-8'); ?>">Beranda</a></nav>
</main>
</body>
</html>
