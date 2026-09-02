<?php
declare(strict_types=1);

/**
 * Load server-only runtime secrets before the public application config.
 * On this Hostinger runtime, a protected PHP config file inside the document
 * root is the most reliable source. Apache denies direct HTTP access to it.
 */
function bootstrap_runtime_secrets(): void {
    $baseDir = dirname(__DIR__);
    $token = '';

    $protectedPhpConfig = $baseDir . '/public/runtime-config.php';
    if (is_file($protectedPhpConfig) && is_readable($protectedPhpConfig)) {
        $data = require $protectedPhpConfig;
        if (is_array($data)) $token = trim((string)($data['feed_token'] ?? ''));
    }

    if ($token === '') {
        $protectedPublicToken = $baseDir . '/public/.salwa-feed-token';
        if (is_file($protectedPublicToken) && is_readable($protectedPublicToken)) {
            $token = trim((string) file_get_contents($protectedPublicToken));
        }
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
