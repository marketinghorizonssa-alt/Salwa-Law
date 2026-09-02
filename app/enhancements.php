<?php
declare(strict_types=1);

function visual_v4_authority_html(string $path): string {
    $items = [
        'moj' => '<div class="authority-v4 authority-v4-moj"><div class="authority-mark" aria-hidden="true">⚖</div><div class="authority-copy"><strong>وزارة العدل</strong><small>المرجع العدلي الرسمي</small></div></div>',
        'sba' => '<div class="authority-v4 authority-v4-sba"><div class="authority-logo-box authority-logo-dark"><img src="https://sba.gov.sa/wp-content/uploads/2022/03/whitelogo-2.png" width="132" height="64" loading="lazy" decoding="async" alt="شعار الهيئة السعودية للمحامين" onerror="this.style.display=\'none\'"></div><div class="authority-copy"><strong>الهيئة السعودية للمحامين</strong><small>الجهة المهنية ذات الصلة</small></div></div>',
        'hrsd' => '<div class="authority-v4 authority-v4-hrsd"><div class="authority-logo-box"><img src="https://www.hrsd.gov.sa/themes/custom/hrsd_saud/logo.svg" width="144" height="60" loading="lazy" decoding="async" alt="شعار وزارة الموارد البشرية والتنمية الاجتماعية" onerror="this.style.display=\'none\'"></div><div class="authority-copy"><strong>وزارة الموارد البشرية والتنمية الاجتماعية</strong><small>مرجع أنظمة وعلاقات العمل</small></div></div>',
        'saip' => '<div class="authority-v4 authority-v4-saip"><div class="authority-logo-box"><img src="https://www.saip.gov.sa/_next/image?q=75&amp;url=%2Fimages%2Fsaip-logo-color.svg&amp;w=3840" width="144" height="60" loading="lazy" decoding="async" alt="شعار الهيئة السعودية للملكية الفكرية" onerror="this.style.display=\'none\'"></div><div class="authority-copy"><strong>الهيئة السعودية للملكية الفكرية</strong><small>الجهة المختصة بالملكية الفكرية</small></div></div>',
    ];

    $keys = ['moj','sba'];
    if ($path === '/') $keys = ['moj','sba','hrsd','saip'];
    if ($path === '/محامي-قضايا-عمالية/') $keys = ['moj','sba','hrsd'];
    if ($path === '/ملكية-فكرية/') $keys = ['moj','sba','saip'];

    $cards = '';
    foreach ($keys as $key) $cards .= $items[$key];

    return '<section class="authority-band authority-band-v4" aria-label="جهات نظامية ذات صلة"><div class="wrap"><div class="authority-v4-head"><div><p class="eyebrow">المنظومة النظامية</p><h2>جهات رسمية مرتبطة بنطاق الخدمة</h2></div><p>تُعرض كمرجع نظامي ومهني بحسب نوع الخدمة، ولا يعني ظهورها وجود شراكة أو اعتماد إضافي.</p></div><div class="authority-v4-grid">'.$cards.'</div></div></section>';
}

function enhance_site_html(string $html, string $path): string {
    if ($html === '' || stripos($html, '<html') === false) return $html;

    $html = str_replace('<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">', '', $html);
    $assets = '<link rel="icon" type="image/svg+xml" href="/assets/favicon.svg">'
        . '<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;600;700&display=swap" rel="stylesheet">'
        . '<link rel="stylesheet" href="/assets/visual-v4.css?v=20260902-2">'
        . '<link rel="stylesheet" href="/assets/visual-v5.css?v=20260902-2">';
    $html = str_replace('</head>', $assets.'</head>', $html);
    $html = str_replace('<body class="', '<body class="visual-v4 ', $html);
    if (str_contains($html, '<body>')) $html = str_replace('<body>', '<body class="visual-v4">', $html);

    $html = str_replace('/assets/logo-white.svg', '/assets/logo-header.svg', $html);

    if (str_contains($html, 'class="authority-band"')) {
        $html = preg_replace('#<section class="authority-band".*?</section>#s', visual_v4_authority_html($path), $html, 1) ?? $html;
    }
    return $html;
}
