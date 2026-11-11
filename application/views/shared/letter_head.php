<?php
$region = trim($settings['letter_region'] ?? '');
$district = trim($settings['letter_district'] ?? '');
$village = trim($settings['letter_village'] ?? '');
$rtName = trim($settings['letter_rt_name'] ?? 'RUKUN TETANGGA 009 / RUKUN WARGA 008');
$environment = trim($settings['letter_environment'] ?? 'SUKA MULYA IV');
$postal = trim($settings['letter_postal'] ?? '');
$address = trim($settings['letter_address'] ?? '');
$heading = array(
    $region === '' ? '' : (preg_match('/^KOTA\b/i', $region) ? $region : 'KOTA '.$region),
    $district === '' ? '' : (preg_match('/^KECAMATAN\b/i', $district) ? $district : 'KECAMATAN '.$district),
    $village === '' ? '' : (preg_match('/^KELURAHAN\b/i', $village) ? $village : 'KELURAHAN '.$village),
);
if ($address === '') {
    $parts = array();
    if ($village !== '') $parts[] = 'Kel. '.preg_replace('/^KELURAHAN\s+/i', '', $village);
    if ($district !== '') $parts[] = 'Kec. '.preg_replace('/^KECAMATAN\s+/i', '', $district);
    if ($region !== '') $parts[] = (preg_match('/^KOTA\b/i', $region) ? '' : 'Kota ').$region;
    $address = implode(', ', $parts);
}
$address = trim($address.' '.$postal);
?>
<header class="<?= html_escape($headClass) ?>">
    <?php if (!empty($settings['letter_logo'])): ?><img src="<?= html_escape($logoUrl) ?>" alt="Logo kop surat"><?php endif; ?>
    <div class="letter-head-text">
        <?php foreach ($heading as $line): ?><?php if ($line !== ''): ?><span class="letter-head-region"><?= html_escape(strtoupper($line)) ?></span><?php endif; ?><?php endforeach; ?>
        <strong class="letter-head-rt"><?= html_escape(strtoupper($rtName)) ?></strong>
        <strong class="letter-head-environment"><?= html_escape(strtoupper($environment)) ?></strong>
        <?php if ($address !== ''): ?><span class="letter-head-address"><?= html_escape($address) ?></span><?php endif; ?>
    </div>
</header>
