<?php
/**
 * Diagnosa kesiapan OCR KK lokal. Tidak membaca dokumen pengguna.
 * Jalankan di server sebagai user web (mis. Apache/PHP-FPM):
 *   php tools/kk-check.php
 *   sudo -u apache php tools/kk-check.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Hanya dapat dijalankan dari CLI.\n");
}

define('BASEPATH', __DIR__);
define('FCPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

require FCPATH . 'application/config/kk_scan.php';
require FCPATH . 'application/libraries/Kk_local_engine.php';

function kk_run(array $args)
{
    if (!is_callable('proc_open')) {
        return array('exit' => -1, 'output' => 'proc_open tidak tersedia');
    }
    $pipes = array();
    $process = @proc_open($args, array(
        0 => array('pipe', 'r'),
        1 => array('pipe', 'w'),
        2 => array('pipe', 'w'),
    ), $pipes, NULL, NULL, array('bypass_shell' => TRUE));
    if (!is_resource($process)) {
        return array('exit' => -1, 'output' => 'gagal menjalankan proses');
    }
    fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]);
    $err = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $code = proc_close($process);
    $lines = preg_split('/\r?\n/', trim($out !== '' ? $out : $err));
    return array('exit' => $code, 'output' => $lines[0] ?? '');
}

function kk_binary($label, $path, $versionArg)
{
    $info = array(
        'label' => $label,
        'path' => $path,
        'exists' => is_file($path),
        'executable' => is_file($path) && (DIRECTORY_SEPARATOR === '\\' || is_executable($path)),
    );
    if ($info['executable']) {
        $info['version'] = kk_run(array($path, $versionArg));
    }
    return $info;
}

$engine = new Kk_local_engine();
$converter = new ReflectionMethod($engine, 'converter');
$converter->setAccessible(TRUE);
$magick = $converter->invoke($engine, $config['kk_magick']);

$ind = array();
foreach (array(
    '/usr/share/tesseract/tessdata/ind.traineddata',
    '/usr/share/tesseract-ocr/4.00/tessdata/ind.traineddata',
    '/usr/share/tesseract-ocr/5/tessdata/ind.traineddata',
    '/usr/share/tessdata/ind.traineddata',
) as $path) {
    $ind[$path] = is_file($path);
}

$temp = sys_get_temp_dir();
$probe = @tempnam($temp, 'kkcheck_');
$tempWritable = $probe !== FALSE && is_file($probe) && @unlink($probe);

$report = array(
    'php' => array(
        'version' => PHP_VERSION,
        'sapi' => PHP_SAPI,
        'proc_open' => is_callable('proc_open'),
        'open_basedir' => ini_get('open_basedir'),
        'disable_functions' => ini_get('disable_functions'),
        'temp_dir' => $temp,
        'temp_writable' => $tempWritable,
        'path_env' => getenv('PATH'),
    ),
    'config' => array(
        'kk_tesseract' => $config['kk_tesseract'],
        'kk_pdftoppm' => $config['kk_pdftoppm'],
        'kk_magick' => $config['kk_magick'],
        'KK_OCR_ENDPOINT' => getenv('KK_OCR_ENDPOINT') ?: '',
    ),
    'resolved' => array(
        'imagemagick' => $magick,
        'tesseract' => is_file($config['kk_tesseract']) ? $config['kk_tesseract'] : '',
        'pdftoppm' => is_file($config['kk_pdftoppm']) ? $config['kk_pdftoppm'] : '',
    ),
    'binaries' => array(
        kk_binary('imagemagick', $magick !== '' ? $magick : $config['kk_magick'], '--version'),
        kk_binary('tesseract', $config['kk_tesseract'], '--version'),
        kk_binary('pdftoppm', $config['kk_pdftoppm'], '-v'),
    ),
    'tesseract_ind_traineddata' => $ind,
    'selinux' => is_file('/sys/fs/selinux/enforce') ? trim((string) @file_get_contents('/sys/fs/selinux/enforce')) : 'tidak aktif',
);

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
