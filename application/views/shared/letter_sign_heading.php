<?php
$months = array(1=>'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
$today = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
$environment = trim($settings['letter_environment'] ?? 'Suka Mulya IV');
$environment = preg_replace('/\bIv\b/', 'IV', ucwords(strtolower($environment)));
$title = trim($settings['letter_signer_title'] ?? 'Ketua RT 009 / RW 008');
?>
<p class="letter-sign-heading"><?= html_escape($environment) ?>, <?= $today->format('d').' '.$months[(int)$today->format('n')].' '.$today->format('Y') ?><br><?= html_escape($title) ?></p>
