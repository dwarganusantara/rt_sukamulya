<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Penulis spreadsheet tanpa dependensi: CSV, XLS (BIFF8 / Excel 97-2003 asli dalam OLE2) dan XLSX (OOXML via ZipArchive).
 * Satu lembar, baris pertama = header (tebal). Nilai int/float ditulis sebagai angka; string sebagai teks.
 *
 *   $bin = $this->spreadsheet_export->build('xlsx', 'Rekap', $headers, $rows);
 */
class Spreadsheet_export
{
    public static $mimes = array(
        'csv' => 'text/csv; charset=utf-8',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    );

    /** @return string isi berkas biner */
    public function build($format, $sheet, array $headers, array $rows)
    {
        $rows = array_map('array_values', $rows);
        $sheet = $this->sheet_name($sheet);
        if ($format === 'xlsx') return $this->xlsx($sheet, $headers, $rows);
        if ($format === 'xls') return $this->xls($sheet, $headers, $rows);
        return $this->csv($headers, $rows);
    }

    /** Kirim berkas sebagai unduhan lewat CI Output. */
    public function send($format, $basename, $sheet, array $headers, array $rows)
    {
        if (!isset(self::$mimes[$format])) $format = 'csv';
        $bin = $this->build($format, $sheet, $headers, $rows);
        $CI =& get_instance();
        $CI->output->set_header('Cache-Control: no-store, private')
            ->set_header('X-Content-Type-Options: nosniff')
            ->set_header('Content-Type: ' . self::$mimes[$format])
            ->set_header('Content-Disposition: attachment; filename="' . $basename . '.' . $format . '"')
            ->set_output($bin);
    }

    private function sheet_name($name)
    {
        $name = trim(str_replace(array('\\', '/', '?', '*', '[', ']', ':'), ' ', (string) $name));
        return $name === '' ? 'Sheet1' : mb_substr($name, 0, 31);
    }

    /** Teks diawali = + - @ dianggap formula oleh Excel saat membuka CSV. */
    private static function csv_safe($v)
    {
        if (is_string($v) && $v !== '' && strpos("=+-@\t\r", $v[0]) !== FALSE) return "'" . $v;
        return $v;
    }

    private function csv(array $headers, array $rows)
    {
        $out = fopen('php://temp', 'w+');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers);
        foreach ($rows as $r) fputcsv($out, array_map(array(__CLASS__, 'csv_safe'), $r));
        rewind($out); $csv = stream_get_contents($out); fclose($out);
        return $csv;
    }

    /** Hapus karakter kontrol yang tidak sah di XML/Excel; pastikan UTF-8. */
    private static function clean($s)
    {
        $s = (string) $s;
        if (!mb_check_encoding($s, 'UTF-8')) $s = mb_convert_encoding($s, 'UTF-8', 'UTF-8');
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $s);
    }

    private static function col_widths(array $headers, array $rows)
    {
        $w = array();
        foreach (array_merge(array($headers), array_slice($rows, 0, 500)) as $r) {
            foreach (array_values($r) as $i => $v) {
                $len = is_float($v) || is_int($v) ? strlen(number_format((float) $v)) + 2 : mb_strlen((string) $v);
                $w[$i] = max($w[$i] ?? 8, min(60, $len + 2));
            }
        }
        return $w;
    }

    // ------------------------------------------------------------------ XLSX

    private function xlsx($sheet, array $headers, array $rows)
    {
        if (!class_exists('ZipArchive')) throw new RuntimeException('Ekstensi PHP zip tidak aktif; format XLSX tidak tersedia.');
        $x = function ($s) { return htmlspecialchars(self::clean($s), ENT_QUOTES | ENT_XML1, 'UTF-8'); };
        $ref = function ($col, $row) {
            $s = ''; for ($n = $col + 1; $n > 0; $n = intdiv($n - 1, 26)) $s = chr(65 + ($n - 1) % 26) . $s;
            return $s . $row;
        };
        $cols = '';
        foreach (self::col_widths($headers, $rows) as $i => $w) $cols .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $w . '" customWidth="1"/>';
        $data = '';
        foreach (array_merge(array($headers), $rows) as $ri => $r) {
            $data .= '<row r="' . ($ri + 1) . '">';
            foreach (array_values($r) as $ci => $v) {
                $cell = $ref($ci, $ri + 1);
                if ($ri > 0 && (is_int($v) || is_float($v)) && is_finite((float) $v)) {
                    $data .= '<c r="' . $cell . '" s="2"><v>' . (is_int($v) ? $v : rtrim(rtrim(sprintf('%.10F', $v), '0'), '.')) . '</v></c>';
                } elseif ($v !== NULL && $v !== '') {
                    $data .= '<c r="' . $cell . '" t="inlineStr"' . ($ri === 0 ? ' s="1"' : '') . '><is><t xml:space="preserve">' . $x($v) . '</t></is></c>';
                }
            }
            $data .= '</row>';
        }
        $last = $ref(max(0, count($headers) - 1), max(1, count($rows) + 1));
        $parts = array(
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="' . $x($sheet) . '" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            'xl/styles.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFE8EEF7"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/><xf numFmtId="3" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>',
            'xl/worksheets/sheet1.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><dimension ref="A1:' . $last . '"/><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols>' . $cols . '</cols><sheetData>' . $data . '</sheetData><autoFilter ref="A1:' . $last . '"/></worksheet>',
        );
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        try {
            $zip = new ZipArchive();
            if ($zip->open($tmp, ZipArchive::OVERWRITE) !== TRUE) throw new RuntimeException('Berkas XLSX sementara tidak dapat dibuat.');
            foreach ($parts as $name => $xml) $zip->addFromString($name, $xml);
            $zip->close();
            return file_get_contents($tmp);
        } finally {
            @unlink($tmp);
        }
    }

    // ------------------------------------------------------------------- XLS

    private static function rec($type, $data = '')
    {
        return pack('vv', $type, strlen($data)) . $data;
    }

    private static function u16($s)
    {
        return mb_convert_encoding($s, 'UTF-16LE', 'UTF-8');
    }

    /** Workbook BIFF8 (Excel 97-2003) dibungkus OLE2 Compound File. */
    private function xls($sheet, array $headers, array $rows)
    {
        // --- Tabel string bersama (SST)
        $sst = array(); $sst_index = array(); $refs = 0;
        $str_id = function ($s) use (&$sst, &$sst_index, &$refs) {
            $s = mb_substr(self::clean($s), 0, 4000); // ≤ 1 rekaman CONTINUE per string
            $refs++;
            if (!isset($sst_index[$s])) { $sst_index[$s] = count($sst); $sst[] = $s; }
            return $sst_index[$s];
        };

        // --- Sheet substream (offset BOF diisi kemudian)
        $all = array_merge(array($headers), $rows);
        $ncols = 0; foreach ($all as $r) $ncols = max($ncols, count($r));
        $cells = '';
        foreach ($all as $ri => $r) {
            foreach (array_values($r) as $ci => $v) {
                if ($ri > 0 && (is_int($v) || is_float($v)) && is_finite((float) $v)) {
                    $cells .= self::rec(0x0203, pack('vvv', $ri, $ci, 17) . pack('e', (float) $v)); // NUMBER
                } elseif ($v !== NULL && $v !== '') {
                    $cells .= self::rec(0x00FD, pack('vvvV', $ri, $ci, $ri === 0 ? 16 : 15, $str_id($v))); // LABELSST
                }
            }
        }
        $ws = self::rec(0x0809, pack('vvvvVV', 0x0600, 0x0010, 0x0DBB, 0x07CC, 0, 0x06));
        foreach (self::col_widths($headers, $rows) as $i => $w) $ws .= self::rec(0x007D, pack('vvvvvv', $i, $i, min(255, $w) * 256, 15, 0, 0)); // COLINFO
        $ws .= self::rec(0x0200, pack('VVvvv', 0, count($all), 0, $ncols, 0)); // DIMENSIONS
        $ws .= $cells;
        $ws .= self::rec(0x023E, pack('vvvvvvvV', 0x06B6, 0, 0, 0x40, 0, 0, 0, 0)); // WINDOW2
        $ws .= self::rec(0x000A); // EOF

        // --- Globals
        $font = function ($bold) { return self::rec(0x0031, pack('vvvvvCCCC', 200, 0, 0x7FFF, $bold ? 700 : 400, 0, 0, 0, 0, 0) . pack('CC', 5, 0) . 'Arial'); };
        $xf = function ($fnt, $fmt, $style) {
            $used = $style ? 0 : (($fmt ? 1 : 0) << 2 | ($fnt ? 1 : 0) << 3);
            return self::rec(0x00E0, pack('vvvC', $fnt, $fmt, $style ? 0xFFF5 : 0x0001, 0x20) . pack('CCC', 0, 0, $used) . pack('VVv', 0, 0, 0x20C0));
        };
        $g = self::rec(0x0809, pack('vvvvVV', 0x0600, 0x0005, 0x0DBB, 0x07CC, 0, 0x06)); // BOF
        $g .= self::rec(0x0042, pack('v', 1200)); // CODEPAGE UTF-16
        $g .= self::rec(0x003D, pack('vvvvvvvvv', 0, 0, 0x4000, 0x2000, 0x0038, 0, 0, 1, 0x0258)); // WINDOW1
        $g .= $font(FALSE) . $font(FALSE) . $font(FALSE) . $font(FALSE) . $font(TRUE); // indeks 0-3, 5 (=tebal)
        for ($i = 0; $i < 15; $i++) $g .= $xf(0, 0, TRUE);
        $g .= $xf(0, 0, FALSE);   // 15: sel biasa
        $g .= $xf(5, 0, FALSE);   // 16: header tebal
        $g .= $xf(0, 3, FALSE);   // 17: angka #,##0
        $g .= self::rec(0x0293, pack('vCC', 0x8000, 0, 0xFF)); // STYLE Normal
        $bs_pos = strlen($g);
        $name16 = self::u16($sheet);
        $g .= self::rec(0x0085, pack('VCC', 0, 0, 0) . pack('CC', strlen($name16) >> 1, 1) . $name16); // BOUNDSHEET

        // SST + CONTINUE (string tidak dipecah; string baru mulai di rekaman berikutnya bila tidak muat)
        $sst_pos = strlen($g);
        $chunks = array(pack('VV', $refs, count($sst))); $marks = array();
        foreach ($sst as $k => $s) {
            $u = self::u16($s); // cch = jumlah unit UTF-16 (emoji = 2), bukan code point
            $bin = pack('vC', strlen($u) >> 1, 1) . $u;
            if (strlen(end($chunks)) + strlen($bin) > 8224) $chunks[] = '';
            if ($k % 8 === 0) $marks[] = array(count($chunks) - 1, strlen(end($chunks)));
            $chunks[count($chunks) - 1] .= $bin;
        }
        $starts = array(); $pos = $sst_pos;
        foreach ($chunks as $i => $c) { $starts[$i] = $pos; $g .= self::rec($i === 0 ? 0x00FC : 0x003C, $c); $pos += 4 + strlen($c); }
        $ext = pack('v', 8);
        foreach ($marks as $m) $ext .= pack('Vvv', $starts[$m[0]] + 4 + $m[1], 4 + $m[1], 0);
        $g .= self::rec(0x00FF, $ext); // EXTSST
        $g .= self::rec(0x000A); // EOF

        // posisi absolut BOF sheet → BOUNDSHEET.lbPlyPos
        $g = substr_replace($g, pack('V', strlen($g)), $bs_pos + 4, 4);
        $stream = $g . $ws;
        if (strlen($stream) < 4096) $stream .= str_repeat("\0", 4096 - strlen($stream)); // hindari mini stream
        return $this->ole2($stream);
    }

    /** OLE2 Compound File v3 (sektor 512 byte) berisi satu stream "Workbook". */
    private function ole2($stream)
    {
        $size = strlen($stream);
        $wsec = (int) ceil($size / 512);
        $fat_n = 1; while ($fat_n * 128 < $wsec + 1 + $fat_n) $fat_n++;
        if ($fat_n > 109) throw new RuntimeException('Data terlalu besar untuk format XLS; gunakan XLSX.');
        $dir_sec = $wsec;
        $fat = array();
        for ($i = 0; $i < $wsec - 1; $i++) $fat[] = $i + 1;
        $fat[] = 0xFFFFFFFE;                       // akhir rantai Workbook
        $fat[] = 0xFFFFFFFE;                       // sektor direktori
        for ($i = 0; $i < $fat_n; $i++) $fat[] = 0xFFFFFFFD; // sektor FAT
        while (count($fat) < $fat_n * 128) $fat[] = 0xFFFFFFFF;

        $difat = array(); for ($i = 0; $i < 109; $i++) $difat[] = $i < $fat_n ? $dir_sec + 1 + $i : 0xFFFFFFFF;
        $h = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1" . str_repeat("\0", 16)
            . pack('vvvvv', 0x003E, 0x0003, 0xFFFE, 9, 6) . str_repeat("\0", 6)
            . pack('VVVVVVVVV', 0, $fat_n, $dir_sec, 0, 4096, 0xFFFFFFFE, 0, 0xFFFFFFFE, 0)
            . pack('V*', ...$difat);

        $entry = function ($name, $type, $child, $start, $len) {
            $n = $name === '' ? '' : self::u16($name) . "\0\0";
            return str_pad($n, 64, "\0") . pack('vCC', strlen($n), $type, 1)
                . pack('VVV', 0xFFFFFFFF, 0xFFFFFFFF, $child) . str_repeat("\0", 16 + 4 + 16)
                . pack('VVV', $start, $len, 0);
        };
        $dir = $entry('Root Entry', 5, 1, 0xFFFFFFFE, 0) . $entry('Workbook', 2, 0xFFFFFFFF, 0, $size)
            . $entry('', 0, 0xFFFFFFFF, 0, 0) . $entry('', 0, 0xFFFFFFFF, 0, 0);

        return $h . str_pad($stream, $wsec * 512, "\0") . $dir . pack('V*', ...$fat);
    }
}
