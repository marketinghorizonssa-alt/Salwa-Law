<?php
declare(strict_types=1);

function base_head(string $title, string $description, string $path): string {
    $cfg = site_config();
    $canonical = absolute_url($path);
    $gtm = $cfg['gtm_id'] !== '' ? "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . esc($cfg['gtm_id']) . "');</script>" : '';
    return '<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.esc($title).'</title><meta name="description" content="'.esc($description).'"><link rel="canonical" href="'.esc($canonical).'"><meta property="og:type" content="website"><meta property="og:locale" content="ar_SA"><meta property="og:title" content="'.esc($title).'"><meta property="og:description" content="'.esc($description).'"><meta property="og:url" content="'.esc($canonical).'"><meta property="og:image" content="'.esc($cfg['site_url']).'/assets/logo-dark.svg"><meta name="theme-color" content="#06283A"><link rel="stylesheet" href="/assets/site.css">'.$gtm.'</head>';
}

function schema_json(array $page): string {
    $cfg = site_config();
    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Person',
                '@id' => $cfg['site_url'] . '/#person',
                'name' => 'سلوى أحمد',
                'jobTitle' => 'محامية',
                'url' => $cfg['site_url'],
                'sameAs' => [$cfg['tiktok'], $cfg['linkedin']],
            ],
            [
                '@type' => 'LegalService',
                '@id' => $cfg['site_url'] . '/#legalservice',
                'name' => BRAND_NAME_AR,
                'url' => $cfg['site_url'],
                'telephone' => $cfg['phone_e164'],
                'areaServed' => ['@type' => 'Country', 'name' => 'Saudi Arabia'],
                'provider' => ['@id' => $cfg['site_url'] . '/#person'],
            ],
            [
                '@type' => 'WebPage',
                '@id' => absolute_url($page['slug']) . '#webpage',
                'url' => absolute_url($page['slug']),
                'name' => $page['title'],
                'description' => $page['description'],
                'inLanguage' => 'ar-SA',
                'about' => ['@id' => $cfg['site_url'] . '/#legalservice'],
            ]
        ]
    ];
    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}

function nav_html(): string {
    return '<header class="site-header"><div class="wrap nav"><a class="brand" href="/" aria-label="الرئيسية"><img src="/assets/logo-white.svg" width="160" height="160" alt="سلوى أحمد"></a><nav aria-label="التنقل الرئيسي"><a href="/استشارات-قانونية/">الاستشارات</a><a href="/محامي-عقود/">العقود</a><a href="/محامي-قضايا-عمالية/">العمل</a><a href="/محامي-احوال-شخصية/">الأحوال الشخصية</a><a href="/ملكية-فكرية/">الملكية الفكرية</a></nav><a class="nav-cta" href="#lead-form">اطلب تواصلًا</a></div></header>';
}

function form_html(array $page): string {
    $opts = service_options();
    $allowed = array_flip($page['service_keys']);
    $html = '<form class="lead-form" id="lead-form" data-page-id="'.esc($page['id']).'" novalidate><div class="form-head"><span>تواصل أولي</span><h2>'.esc($page['form_title']).'</h2><p>بيانات مختصرة تساعد على فهم نوع الطلب قبل التواصل.</p></div>';
    $html .= '<label>الاسم<input name="name" type="text" autocomplete="name" maxlength="100" required placeholder="الاسم الكامل"></label>';
    $html .= '<label>رقم الجوال<input name="phone" type="tel" inputmode="tel" autocomplete="tel" maxlength="24" required placeholder="05xxxxxxxx"></label>';
    $html .= '<label>نوع الخدمة<select name="service" required><option value="">اختر الخدمة</option>';
    foreach ($opts as $key => $label) if (isset($allowed[$key])) $html .= '<option value="'.esc($key).'"'.($key === $page['default_service'] ? ' selected' : '').'>'.esc($label).'</option>';
    $html .= '</select></label>';
    $html .= '<label>ملخص مختصر <span class="optional">اختياري</span><textarea name="message" rows="3" maxlength="1600" placeholder="اكتب باختصار موضوعك والهدف من الاستشارة"></textarea></label>';
    $html .= '<label class="consent"><input name="consent" type="checkbox" value="1" required><span>أوافق على استخدام بياناتي للرد على طلبي وفق <a href="/سياسة-الخصوصية/" target="_blank" rel="noopener">سياسة الخصوصية</a>.</span></label>';
    $html .= '<div class="form-error" role="alert" aria-live="polite"></div><button type="submit">'.esc($page['form_cta']).'</button><p class="form-note">إرسال الطلب لا يعني قبول القضية أو ضمان أي نتيجة.</p></form>';
    return $html;
}

function footer_html(): string {
    $cfg = site_config();
    return '<footer><div class="wrap footer-grid"><div><img class="footer-logo" src="/assets/logo-white.svg" width="180" height="180" alt="سلوى أحمد"><p>خدمات واستشارات قانونية عن بُعد داخل المملكة العربية السعودية.</p></div><div><h3>روابط</h3><a href="/سياسة-الخصوصية/">سياسة الخصوصية</a><a href="'.$cfg['tiktok'].'" target="_blank" rel="noopener">TikTok</a><a href="'.$cfg['linkedin'].'" target="_blank" rel="noopener">LinkedIn</a></div><div><h3>تواصل</h3><a href="tel:'.$cfg['phone_e164'].'"><bdi dir="ltr">'.$cfg['phone_display'].'</bdi></a><a href="https://wa.me/'.$cfg['whatsapp'].'" data-event="click_whatsapp" target="_blank" rel="noopener">واتساب</a></div></div><div class="copyright">© '.date('Y').' سلوى أحمد. جميع الحقوق محفوظة.</div></footer>';
}

function floating_ctas(): string {
    $cfg = site_config();
    return '<div class="floating-ctas"><a class="float-call" href="tel:'.$cfg['phone_e164'].'" data-event="click_call" aria-label="اتصال">اتصال</a><a class="float-wa" href="https://wa.me/'.$cfg['whatsapp'].'" data-event="click_whatsapp" target="_blank" rel="noopener" aria-label="واتساب">واتساب</a></div>';
}

function page_html(array $page): string {
    $head = base_head($page['title'], $page['description'], $page['slug']);
    $chips = ''.implode('', array_map(fn($c) => '<span>'.esc($c).'</span>', $page['chips']));
    $cards = ''.implode('', array_map(fn($c) => '<article><h3>'.esc($c[0]).'</h3><p>'.esc($c[1]).'</p></article>', $page['scope_cards']));
    $faq = ''.implode('', array_map(fn($f) => '<details><summary>'.esc($f[0]).'</summary><p>'.esc($f[1]).'</p></details>', $page['faq']));
    return $head.'<body>'.schema_json($page).nav_html().'<main><section class="hero"><div class="hero-glow"></div><div class="wrap hero-grid"><div class="hero-copy"><p class="eyebrow">'.esc($page['eyebrow']).'</p><h1>'.esc($page['h1']).'</h1><p class="hero-intro">'.esc($page['intro']).'</p><div class="chips">'.$chips.'</div><div class="hero-links"><a class="ghost" href="https://wa.me/'.site_config()['whatsapp'].'" data-event="click_whatsapp" target="_blank" rel="noopener">تواصل عبر واتساب</a><a class="text-link" href="tel:'.site_config()['phone_e164'].'" data-event="click_call">اتصال مباشر</a></div></div><div class="form-wrap">'.form_html($page).'</div></div></section><section class="trust-band"><div class="wrap trust-grid"><div><strong>ترخيص مزاولة المهنة</strong><span>وزارة العدل — المملكة العربية السعودية</span></div><div><strong>اعتماد مهني</strong><span>الهيئة السعودية للمحامين</span></div><div><strong>التواصل</strong><span>عن بُعد داخل المملكة</span></div></div></section><section class="section"><div class="wrap"><div class="section-head"><p class="eyebrow">نطاق الخدمة</p><h2>'.esc($page['section_title']).'</h2><p>'.esc($page['section_intro']).'</p></div><div class="cards">'.$cards.'</div></div></section><section class="process"><div class="wrap"><div class="section-head"><p class="eyebrow">طريقة العمل</p><h2>خطوات واضحة من أول تواصل</h2></div><ol><li><span>01</span><div><h3>أرسل بيانات مختصرة</h3><p>الاسم، الجوال، ونوع الخدمة مع ملخص اختياري.</p></div></li><li><span>02</span><div><h3>فهم الحالة مبدئيًا</h3><p>يتم تحديد طبيعة الطلب وما يحتاجه من مستندات أو معلومات إضافية.</p></div></li><li><span>03</span><div><h3>تحديد نطاق المتابعة</h3><p>استشارة أو مراجعة أو تمثيل بحسب الحالة ونطاق التكليف المتفق عليه.</p></div></li></ol></div></section><section class="faq"><div class="wrap"><div class="section-head"><p class="eyebrow">أسئلة شائعة</p><h2>قبل إرسال الطلب</h2></div><div class="faq-list">'.$faq.'</div></div></section><section class="bottom-cta"><div class="wrap bottom-grid"><div><p class="eyebrow">ابدأ بخطوة واضحة</p><h2>إذا عندك مستند أو موقف يحتاج قراءة قانونية، ابدأ بطلب تواصل.</h2></div><a href="#lead-form">أرسل طلبك الآن</a></div></section></main>'.floating_ctas().footer_html().'<script src="/assets/site.js" defer></script></body></html>';
}

function privacy_html(): string {
    $page = ['slug' => '/سياسة-الخصوصية/','title' => 'سياسة الخصوصية | سلوى أحمد','description' => 'سياسة خصوصية بيانات التواصل وطلبات الخدمات القانونية.'];
    return base_head($page['title'],$page['description'],$page['slug']).'<body>'.nav_html().'<main class="legal-page"><div class="wrap narrow"><p class="eyebrow">الإصدار '.PRIVACY_VERSION.'</p><h1>سياسة الخصوصية</h1><p>توضح هذه السياسة كيفية التعامل مع البيانات التي تقدمها عند طلب التواصل أو استخدام الموقع.</p><h2>البيانات التي نجمعها</h2><p>قد تشمل الاسم، رقم الجوال، نوع الخدمة، ملخص الطلب، وبيانات الإحالة والزيارات مثل UTM ومعرفات النقر الإعلانية عند توفرها.</p><h2>الغرض من الاستخدام</h2><p>تستخدم البيانات لفهم طلبك، التواصل معك، إدارة الطلبات، تحسين الموقع وقياس أداء القنوات التسويقية بما يتوافق مع الغرض من جمعها.</p><h2>المشاركة والمعالجة</h2><p>قد تُعالج البيانات عبر مزودي استضافة أو أنظمة إدارة علاقات العملاء أو أدوات قياس معتمدة عند تفعيلها. لا تُنشر بياناتك للعامة.</p><h2>الاحتفاظ والأمان</h2><p>نحتفظ بالبيانات للمدة اللازمة لأغراض التواصل والخدمة والالتزامات النظامية، مع تطبيق ضوابط وصول مناسبة وتقليل الوصول إلى الحد اللازم.</p><h2>حقوقك والتواصل</h2><p>لطلب الاستفسار عن بياناتك أو تصحيحها أو حذفها عندما يكون ذلك متاحًا نظامًا، تواصل عبر رقم الجوال أو واتساب الظاهر في الموقع.</p><h2>التتبع</h2><p>قد تُستخدم معرفات الحملات وملفات تقنية لازمة للقياس ومنع تكرار إرسال الطلبات. لا يتم وضع بيانات شخصية في روابط صفحة الشكر.</p><h2>تحديث السياسة</h2><p>قد يتم تحديث هذه السياسة عند تغير الأنظمة أو مزودي الخدمة، ويظهر رقم الإصدار أعلى الصفحة.</p></div></main>'.floating_ctas().footer_html().'</body></html>';
}

function thank_you_html(): string {
    $cfg = site_config();
    return base_head('تم استلام طلبك | سلوى أحمد','تم استلام طلب التواصل.','/شكرا/').'<body><main class="thank-you"><div class="thank-card"><img src="/assets/logo-dark.svg" width="140" height="140" alt="سلوى أحمد"><p class="eyebrow">تم الاستلام</p><h1>شكرًا، وصل طلبك بنجاح.</h1><p>يمكنك متابعة التواصل مباشرة عبر واتساب وذكر رقم المرجع الظاهر لديك.</p><a class="primary" href="https://wa.me/'.$cfg['whatsapp'].'" data-event="whatsapp_after_form" target="_blank" rel="noopener">متابعة عبر واتساب</a><a class="back" href="/">العودة للرئيسية</a></div></main><script src="/assets/site.js" defer></script></body></html>';
}

function not_found_html(): string {
    return base_head('الصفحة غير موجودة | سلوى أحمد','الصفحة المطلوبة غير موجودة.','/404/').'<body><main class="thank-you"><div class="thank-card"><p class="eyebrow">404</p><h1>الصفحة غير موجودة</h1><p>قد يكون الرابط غير صحيح أو تم تغييره.</p><a class="primary" href="/">العودة للرئيسية</a></div></main></body></html>';
}
