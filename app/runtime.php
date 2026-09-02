<?php
declare(strict_types=1);

/**
 * Load server-only runtime secrets before the public application config.
 * Secrets stay outside the document root and are never committed to GitHub.
 */
function bootstrap_runtime_secrets(): void {
    $baseDir = dirname(__DIR__);
    $token = '';

    // Preferred: persistent project-root runtime file. The deploy script replaces
    // app/public/scripts only, so this survives future GitHub deployments.
    $rootJson = $baseDir . '/.salwa-runtime.json';
    if (is_file($rootJson) && is_readable($rootJson)) {
        $data = json_decode((string) file_get_contents($rootJson), true);
        if (is_array($data)) $token = trim((string)($data['feed_token'] ?? ''));
    }

    // Backward-compatible fallbacks.
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
