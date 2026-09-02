<?php
declare(strict_types=1);

/**
 * Load server-only runtime secrets before the public application config.
 * Hostinger/Apache may expose SetEnv variables through $_SERVER rather than
 * getenv(). Internal rewrites can prefix them with REDIRECT_.
 */
function bootstrap_runtime_secrets(): void {
    $baseDir = dirname(__DIR__);
    $token = trim((string)(
        $_SERVER['SALWA_FEED_TOKEN']
        ?? $_SERVER['REDIRECT_SALWA_FEED_TOKEN']
        ?? $_ENV['SALWA_FEED_TOKEN']
        ?? ''
    ));

    if ($token === '') {
        $protectedPhpConfig = $baseDir . '/public/runtime-config.php';
        if (is_file($protectedPhpConfig) && is_readable($protectedPhpConfig)) {
            $data = require $protectedPhpConfig;
            if (is_array($data)) $token = trim((string)($data['feed_token'] ?? ''));
        }
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
