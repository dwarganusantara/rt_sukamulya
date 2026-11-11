<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function family_icon($name)
{
    $paths = array(
        'back'=>'M19 12H5m6-6-6 6 6 6',
        'card'=>'M4 5h16a1 1 0 0 1 1 1v12H3V6a1 1 0 0 1 1-1zM7 9h3v3H7zM14 9h4M14 12h4M7 15h11',
        'people'=>'M9 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM3 20v-2a6 6 0 0 1 12 0v2M16 5a3 3 0 0 1 0 6m2 3a5 5 0 0 1 3 4v2',
        'person'=>'M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM4 21v-2a8 8 0 0 1 16 0v2',
        'camera'=>'M4 7h4l2-3h4l2 3h4a1 1 0 0 1 1 1v11H3V8a1 1 0 0 1 1-1zM16 13a4 4 0 1 0-8 0 4 4 0 0 0 8 0z',
        'edit'=>'m15 4 5 5M4 20l4-1L21 6l-5-5L3 14l-1 7h15',
        'clock'=>'M21 12a9 9 0 1 0-18 0 9 9 0 0 0 18 0zM12 7v5l3 2',
        'bulb'=>'M9 18h6M10 21h4M8 14a6 6 0 1 1 8 0l-1 2H9zM12 1V0M3 5 1 3M21 5l2-2M2 11H0M24 11h-2',
        'refresh'=>'M20 7V3l-3 3a8 8 0 0 0-13 4m0 7v4l3-3a8 8 0 0 0 13-4M20 3h-5M4 21h5',
        'check'=>'m5 12 4 4L19 6',
        'calendar'=>'M4 5h16v16H4zM4 10h16M8 2v5M16 2v5M8 14h2',
        'pin'=>'M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0zM14 10a2 2 0 1 0-4 0 2 2 0 0 0 4 0z',
        'home'=>'m3 11 9-8 9 8M5 10v11h5v-7h4v7h5V10',
        'map'=>'m3 5 6-2 6 2 6-2v16l-6 2-6-2-6 2zM9 3v16M15 5v16',
        'hash'=>'M5 9h14M4 15h14M10 3 6 21M18 3l-4 18',
        'mail'=>'M3 5h18v14H3zM3 6l9 7 9-7',
        'copy'=>'M9 8h11v13H9zM15 4V2H3v14h2',
        'eye'=>'M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12zM15 12a3 3 0 1 0-6 0 3 3 0 0 0 6 0z',
        'plus'=>'M12 5v14M5 12h14',
        'arrow'=>'M4 12h16m-6-6 6 6-6 6',
        'upload'=>'M12 16V3m-5 5 5-5 5 5M4 15v6h16v-6',
        'cloud'=>'M7 19a5 5 0 0 1-1-10 6 6 0 0 1 12-1 5.5 5.5 0 0 1 1 11M12 21V11m-4 4 4-4 4 4',
        'shield'=>'m12 2 9 4v6c0 5-9 10-9 10S3 17 3 12V6zM7 12l3 3 7-7',
        'sparkles'=>'m12 3 2.5 6.5L21 12l-6.5 2.5L12 21l-2.5-6.5L3 12l6.5-2.5zM20 1v4M18 3h4',
        'info'=>'M21 12a9 9 0 1 0-18 0 9 9 0 0 0 18 0zM12 11v6M12 7h.01',
        'save'=>'M4 3h13l4 4v14H3V3zM7 3v6h10V3M7 21v-8h10v8',
        'send'=>'m22 2-7 20-4-9L2 9zM11 13 22 2',
        'trash'=>'M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7M14 10v7',
        'close'=>'m6 6 12 12M6 18 18 6',
        'chevron'=>'m6 9 6 6 6-6',
        'hourglass'=>'M6 2h12v5l-6 5 6 5v5H6v-5l6-5-6-5z',
        'file'=>'M14 2H4v20h16V8zM14 2v6h6M8 12h8M8 16h8',
        'chevron-right'=>'m9 6 6 6-6 6',
        'book'=>'M2 5c3-1.5 7-1.5 10 1v14c-3-2.5-7-2.5-10-1zM22 5c-3-1.5-7-1.5-10 1v14c3-2.5 7-2.5 10-1z',
        'lock'=>'M5 11h14v10H5zM8 11V7a4 4 0 0 1 8 0v4M12 15v2',
        'chip'=>'M6 6h12v12H6zM9 2v4M15 2v4M9 18v4M15 18v4M2 9h4M2 15h4M18 9h4M18 15h4',
        'briefcase'=>'M4 8h16v12H4zM9 8V5h6v3M4 13h16',
        'phone'=>'M5 3h4l2 5-2.5 1.5a11 11 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 5a2 2 0 0 1 2-2z',
        'building'=>'M4 21V8l8-5 8 5v13M4 21h16M9 21v-5h6v5M8 10h.01M12 10h.01M16 10h.01M8 13h.01M16 13h.01',
        'search'=>'M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM21 21l-5-5',
        'grid'=>'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z',
        'bell'=>'M6 10a6 6 0 1 1 12 0c0 6 2.5 7 2.5 7h-17S6 16 6 10zM10 20.5a2.2 2.2 0 0 0 4 0',
        'more'=>'M12 5.5v.01M12 12v.01M12 18.5v.01',
        'heart'=>'M20 5a5 5 0 0 0-8 1 5 5 0 0 0-8-1c-4 4 1 9 8 15 7-6 12-11 8-15z',
    );
    return '<svg class="fui-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="'.($paths[$name]??$paths['card']).'"/></svg>';
}

function family_document_art()
{
    return '<svg class="fui-document" viewBox="0 0 340 190" fill="none" aria-hidden="true"><defs><linearGradient id="kk-paper" x1="40" y1="10" x2="270" y2="180" gradientUnits="userSpaceOnUse"><stop stop-color="#effdff"/><stop offset="1" stop-color="#c9e7fa"/></linearGradient></defs><rect x="24" y="20" width="292" height="153" rx="10" fill="url(#kk-paper)" stroke="#8abacb"/><path d="m51 40 8 6 8-6-3 11h-10z" fill="#819fab"/><text x="175" y="48" text-anchor="middle" fill="#10254e" font-family="Arial,sans-serif" font-size="16" font-weight="800">KARTU KELUARGA</text><path d="M44 65h67m18 0h81M44 73h105m18 0h47" stroke="#98b8cd" stroke-width="3"/><rect x="42" y="86" width="256" height="67" rx="2" fill="#e8f6ff" stroke="#96b5ca"/><path d="M42 99h256M42 111h256M42 125h256M42 139h256M71 86v67M140 86v67M207 86v67M257 86v67" stroke="#96b5ca"/><path d="M49 93h14m17 0h49m19 0h46m22 0h32m16 0h27M49 106h14m17 0h39m29 0h35m33 0h25m23 0h23M49 119h14m17 0h45m23 0h39m29 0h25m23 0h23M49 132h14m17 0h40m28 0h35m33 0h25m23 0h23M49 146h14m17 0h40m28 0h35m33 0h25m23 0h23" stroke="#adc8d9" stroke-width="2"/><path d="M12 45V22a10 10 0 0 1 10-10h23m250 0h23a10 10 0 0 1 10 10v23M12 145v23a10 10 0 0 0 10 10h23m250 0h23a10 10 0 0 0 10-10v-23" stroke="#0baa35" stroke-width="6" stroke-linecap="round"/></svg>';
}
