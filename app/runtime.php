<?php
declare(strict_types=1);

/**
 * Load server-only runtime secrets before the public application config.
 * Secrets stay outside the document root and are never committed to GitHub.
 */
function bootstrap_runtime_secrets(): void {
    $tokenPath = dirname(__DIR__) . '/private/feed-token.txt';
    if (!is_file($tokenPath) || !is_readable($tokenPath)) return;

    $token = trim((string) file_get_contents($tokenPath));
    if ($token === '') return;

    putenv('SALWA_FEED_TOKEN=' . $token);
    $_ENV['SALWA_FEED_TOKEN'] = $token;
    $_SERVER['SALWA_FEED_TOKEN'] = $token;
}

bootstrap_runtime_secrets();
