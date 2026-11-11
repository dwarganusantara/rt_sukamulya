<?php
$login_settings = $this->Content_model->settings();
$custom_logo = $login_settings['logo'] ?? '';
$login_logo = (!$custom_logo || $custom_logo === 'assets/mockup/logo.png') ? 'assets/mockup/login-logo.png' : $custom_logo;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Masuk Admin &middot; <?= html_escape($login_settings['brand_label'] ?? 'RT 009/008') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/login-community.css') ?>?v=<?= filemtime(FCPATH.'assets/login-community.css') ?>">
<?php $this->load->helper('theme'); echo theme_style(isset($settings) && is_array($settings) ? $settings : $this->Content_model->settings()); ?></head>
<body class="community-login">
<main class="login-stage">
    <form class="community-card" method="post" action="<?= site_url('admin/login') ?>">
        <div class="card-content">
            <div class="login-brand">
                <img src="<?= base_url(html_escape($login_logo)) ?>" alt="Logo RT 009/008">
                <strong><?= html_escape($login_settings['brand_label'] ?? 'RT 009/008') ?></strong>
                <span><?= html_escape(strtoupper($login_settings['brand_subtitle'] ?? 'Suka Mulya IV')) ?></span>
                <div class="administrasi"><i></i>ADMINISTRASI<i></i></div>
            </div>
            <h1>Masuk Admin</h1>
            <p class="login-intro">Kelola informasi dan layanan RT 009/008<br>Suka Mulya IV dengan mudah dan aman.</p>
            <?php if ($error): ?><div class="login-error" role="alert"><?= html_escape($error) ?></div><?php endif; ?>
            <div class="login-fields">
                <label class="input-row">
                    <span class="sr-only">Username</span>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="7.5" r="4"/><path d="M4 21c0-4.2 3.2-7 8-7s8 2.8 8 7z"/></svg>
                    <input type="text" name="username" placeholder="Username" autocomplete="username" required autofocus>
                </label>
                <label class="input-row">
                    <span class="sr-only">Password</span>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                    <input type="password" id="admin-password" name="password" placeholder="Password" autocomplete="current-password" required>
                    <button class="show-password" type="button" aria-label="Tampilkan password" aria-pressed="false" onclick="var input=document.getElementById('admin-password');var open=input.type==='password';input.type=open?'text':'password';this.setAttribute('aria-pressed',open?'true':'false');this.setAttribute('aria-label',open?'Sembunyikan password':'Tampilkan password');this.textContent=String.fromCharCode(open?9673:9678)">&#9678;</button>
                </label>
            </div>
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <button class="login-submit" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13 4h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-6M3 12h13m-4-4 4 4-4 4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>Masuk</button>
            <div class="login-or"><span></span>atau<span></span></div>
            <a class="back-site" href="<?= site_url() ?>"><span aria-hidden="true">&larr;</span> Kembali ke website</a>
            <a class="back-site" href="<?= site_url('platform/login') ?>" style="margin-top:10px;font-size:12px">Login pengelola platform</a>
        </div>
        <div class="card-waves" aria-hidden="true"><div></div></div>
    </form>
</main>
</body>
</html>
