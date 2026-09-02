<?php
declare(strict_types=1);

/**
 * Load server-only runtime secrets before the public application config.
 * The preferred secret file lives in the document root only because this
 * Hostinger PHP runtime does not expose sibling private files reliably.
 * Apache blocks direct HTTP access to the dotfile in .htaccess.
 */
function bootstrap_runtime_secrets(): void {
    $baseDir = dirname(__DIR__);
    $token = '';

    $protectedPublicToken = $baseDir . '/public/.salwa-feed-token';
    if (is_file($protectedPublicToken) && is_readable($protectedPublicToken)) {
        $token = trim((string) file_get_contents($protectedPublicToken));
    }

    if ($token === '') {
        $rootJson = $baseDir . '/.salwa-runtime.json';
        if (is_file($rootJson) && is_readable($rootJson)) {
            $data = json_decode((string) file_get_contents($rootJson), true);
            if (is_array($data)) $token = trim((string)($data['feed_token'] ?? ''));
        }
    }

    if ($token === '') {
        $privateDir = $baseDir . '/private';
        $jsonPath = $privateDir . '/config.json';
        if (is_file($jsonPath) && is_readable($jsonPath)) {
            $data = json_decode((string) file_get_contents($jsonPath), true);
            if (is_array($data)) $token = trim((string)($data['feed_token'] ?? ''));
        }
        if ($token === '') {
            $tokenPath = $privateDir . '/feed-token.txt';
            if (is_file($tokenPath) && is_readable($tokenPath)) {
                $token = trim((string) file_get_contents($tokenPath));
            }
        }
    }

    if ($token === '') return;

    putenv('SALWA_FEED_TOKEN=' . $token);
    $_ENV['SALWA_FEED_TOKEN'] = $token;
    $_SERVER['SALWA_FEED_TOKEN'] = $token;
}

bootstrap_runtime_secrets();
