<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/runtime.php';
require_once dirname(__DIR__) . '/app/config.php';
require_once dirname(__DIR__) . '/app/helpers.php';
require_once dirname(__DIR__) . '/app/leads.php';
require_once dirname(__DIR__) . '/app/views.php';
require_once dirname(__DIR__) . '/app/enhancements.php';

$cfg = site_config();
header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https://sba.gov.sa https://www.hrsd.gov.sa https://saip.gov.sa; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; script-src 'self' https://www.googletagmanager.com 'unsafe-inline'; connect-src 'self' https://www.google-analytics.com https://region1.google-analytics.com https://www.googleadservices.com https://px.ads.linkedin.com; frame-src https://www.googletagmanager.com; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");
if ($cfg['review_mode']) header('X-Robots-Tag: noindex, nofollow');

$rawPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
$path = normalize_path($rawPath);

if ($path === '/api/lead/') handle_lead_submission();
if ($path === '/api/leads.csv/' || $path === '/api/leads-feed/') handle_lead_feed();
if ($path === '/healthz/') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    $deployedCommit = is_file(dirname(__DIR__) . '/.deployed-commit') ? trim((string)file_get_contents(dirname(__DIR__) . '/.deployed-commit')) : '';
    echo json_encode([
        'ok'=>true,
        'service'=>'salwa-law-site',
        'build'=>BUILD_ID,
        'git_commit'=>$deployedCommit,
        'review_mode'=>$cfg['review_mode'],
        'gtm_configured'=>$cfg['gtm_id'] !== '',
        'feed_configured'=>$cfg['feed_token'] !== '',
        'visual'=>'v4',
    ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    exit;
}
if ($path === '/robots.txt/') {
    header('Content-Type: text/plain; charset=utf-8');
    if ($cfg['review_mode']) echo "User-agent: *\nDisallow: /\n";
    else echo "User-agent: Google-InspectionTool\nDisallow:\n\nUser-agent: Googlebot\nDisallow:\n\nUser-agent: *\nDisallow:\n\nSitemap: {$cfg['site_url']}/sitemap.xml\n";
    exit;
}
if ($path === '/sitemap.xml/') {
    header('Content-Type: application/xml; charset=utf-8');
    $urls = array_keys(page_catalog()); $urls[] = '/سياسة-الخصوصية/';
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach ($urls as $u) echo '  <url><loc>'.htmlspecialchars(absolute_url($u),ENT_XML1|ENT_QUOTES,'UTF-8').'</loc><lastmod>2026-09-02</lastmod></url>' . "\n";
    echo '</urlset>'; exit;
}
if ($path === '/سياسة-الخصوصية/') { echo enhance_site_html(privacy_html(), $path); exit; }
if ($path === '/شكرا/') { echo enhance_site_html(thank_you_html(), $path); exit; }
$catalog = page_catalog();
if (isset($catalog[$path])) { echo enhance_site_html(page_html($catalog[$path]), $path); exit; }
http_response_code(404); echo enhance_site_html(not_found_html(), $path);
