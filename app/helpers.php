<?php
declare(strict_types=1);

function normalize_path(string $path): string {
    $path = rawurldecode($path);
    $path = preg_replace('#/+#', '/', $path) ?: '/';
    if ($path !== '/' && !str_ends_with($path, '/')) $path .= '/';
    return $path;
}

function absolute_url(string $path): string {
    $cfg = site_config();
    return $cfg['site_url'] . ($path === '/' ? '/' : $path);
}

function esc(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function lead_ref(string $id): string {
    return substr(preg_replace('/[^A-Za-z0-9]/', '', $id) ?: 'lead', 0, 12);
}
