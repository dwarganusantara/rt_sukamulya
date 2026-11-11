<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Format ringkas dari toolbar form halaman: **tebal**, *miring*, [teks](https://...), "- " dan "1. ".
function page_body_inline($line)
{
    $line = html_escape($line);
    $line = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $line);
    $line = preg_replace('/(?<![\*\w])\*(?!\s)(.+?)(?<!\s)\*(?![\*\w])/', '<em>$1</em>', $line);
    return preg_replace('/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $line);
}

function page_body_html($body)
{
    $html = '';
    $open = '';
    foreach (preg_split('/\r\n|\r|\n/', (string) $body) as $line) {
        $tag = preg_match('/^\s*-\s+(.*)$/', $line, $match) ? 'ul' : (preg_match('/^\s*\d+\.\s+(.*)$/', $line, $match) ? 'ol' : '');
        if ($open && $open !== $tag) {
            $html .= '</' . $open . '>';
            $open = '';
        }
        if ($tag) {
            if (!$open) $html .= '<' . $tag . '>';
            $open = $tag;
            $html .= '<li>' . page_body_inline($match[1]) . '</li>';
        } else {
            $html .= page_body_inline($line) . "<br />\n";
        }
    }
    if ($open) $html .= '</' . $open . '>';
    return preg_replace('/(<br \/>\n)$/', '', $html);
}
