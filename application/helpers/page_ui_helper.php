<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Komponen halaman admin v4 (lihat assets/admin-ui-v4.css). */

if (!function_exists('pg_crumb')) {
    /** @param array $items [[label, uri|null], ...]; item terakhir = halaman aktif. */
    function pg_crumb(array $items)
    {
        $h = '<nav aria-label="Breadcrumb"><ol class="pg-crumb"><li><a href="' . html_escape(site_url('admin')) . '" aria-label="Dashboard">' . official_svg('home') . '</a></li>';
        $last = count($items) - 1;
        foreach (array_values($items) as $i => $it) {
            $label = html_escape($it[0]);
            if ($i === $last) $h .= '<li><span aria-current="page">' . $label . '</span></li>';
            elseif (!empty($it[1])) $h .= '<li><a href="' . html_escape(site_url($it[1])) . '">' . $label . '</a></li>';
            else $h .= '<li><span>' . $label . '</span></li>';
        }
        return $h . '</ol></nav>';
    }
}

if (!function_exists('pg_pager')) {
    /**
     * Pager « 1 2 3 » dengan jendela halaman.
     * @param callable $url fn(int $page): string URL lengkap
     */
    function pg_pager($page, $pages, callable $url)
    {
        $page = max(1, (int) $page); $pages = max(1, (int) $pages);
        $h = '<nav class="pg-pager" aria-label="Halaman">';
        $h .= $page > 1 ? '<a href="' . html_escape($url($page - 1)) . '" aria-label="Halaman sebelumnya">&laquo;</a>' : '<span class="off" aria-hidden="true">&laquo;</span>';
        $from = max(1, $page - 2); $to = min($pages, $page + 2);
        if ($from > 1) $h .= '<a href="' . html_escape($url(1)) . '">1</a>' . ($from > 2 ? '<span class="off" aria-hidden="true">…</span>' : '');
        for ($i = $from; $i <= $to; $i++) $h .= $i === $page ? '<span aria-current="page">' . $i . '</span>' : '<a href="' . html_escape($url($i)) . '">' . $i . '</a>';
        if ($to < $pages) $h .= ($to < $pages - 1 ? '<span class="off" aria-hidden="true">…</span>' : '') . '<a href="' . html_escape($url($pages)) . '">' . $pages . '</a>';
        $h .= $page < $pages ? '<a href="' . html_escape($url($page + 1)) . '" aria-label="Halaman berikutnya">&raquo;</a>' : '<span class="off" aria-hidden="true">&raquo;</span>';
        return $h . '</nav>';
    }
}
