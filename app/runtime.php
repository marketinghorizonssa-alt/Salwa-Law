<?php
declare(strict_types=1);

function bootstrap_runtime_secrets(): void {
    $baseDir = dirname(__DIR__);
    $token = trim((string)(
        $_SERVER['SALWA_FEED_TOKEN']
        ?? $_SERVER['REDIRECT_SALWA_FEED_TOKEN']
        ?? $_ENV['SALWA_FEED_TOKEN']
        ?? ''
    ));

    if ($token === '') {
        $leadStore = $baseDir . '/private/leads.jsonl';
        if (is_file($leadStore) && is_readable($leadStore)) {
            $handle = fopen($leadStore, 'rb');
            if ($handle !== false) {
                try {
                    if (flock($handle, LOCK_SH)) {
                        while (($line = fgets($handle)) !== false) {
                            $row = json_decode(trim($line), true);
                            if (!is_array($row) || ($row['_type'] ?? '') !== 'runtime_config') continue;
                            $candidate = trim((string)($row['feed_token'] ?? ''));
                            if (preg_match('/^[a-f0-9]{48}$/', $candidate)) $token = $candidate;
                        }
                        flock($handle, LOCK_UN);
                    }
                } finally {
                    fclose($handle);
                }
            }
        }
    }

    if ($token === '') {
        $protectedPhpConfig = $baseDir . '/public/runtime-config.php';
        if (is_file($protectedPhpConfig) && is_readable($protectedPhpConfig)) {
            $data = require $protectedPhpConfig;
            if (is_array($data)) $token = trim((string)($data['feed_token'] ?? ''));
        }
    }
    if ($token === '') {
        $rootJson = $baseDir . '/.salwa-runtime.json';
        if (is_file($rootJson) && is_readable($rootJson)) {
            $data = json_decode((string) file_get_contents($rootJson), true);
            if (is_array($data)) $token = trim((string)($data['feed_token'] ?? ''));
        }
    }

    if ($token === '') return;

    putenv('SALWA_FEED_TOKEN=' . $token);
    $_ENV['SALWA_FEED_TOKEN'] = $token;
    $_SERVER['SALWA_FEED_TOKEN'] = $token;
}

function bootstrap_public_tracking_config(): void {
    $gtm = trim((string)(getenv('SALWA_GTM_ID') ?: ($_SERVER['SALWA_GTM_ID'] ?? $_ENV['SALWA_GTM_ID'] ?? '')));
    if ($gtm === '') $gtm = 'GTM-WTJF998D';
    putenv('SALWA_GTM_ID=' . $gtm);
    $_ENV['SALWA_GTM_ID'] = $gtm;
    $_SERVER['SALWA_GTM_ID'] = $gtm;
}

bootstrap_runtime_secrets();
bootstrap_public_tracking_config();
