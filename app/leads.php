<?php
declare(strict_types=1);

const LEAD_FIELDS = [
    'id','created_at','name','phone','service','message','consent','consent_version',
    'landing_page_id','landing_url','referrer','utm_source','utm_medium','utm_campaign',
    'utm_term','utm_content','gclid','gbraid','wbraid','ttclid','li_fat_id','session_id'
];

function lead_store_path(): string {
    $cfg = site_config();
    $dir = rtrim($cfg['data_dir'], '/');
    if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
        throw new RuntimeException('Lead data directory is unavailable');
    }
    $path = $dir . '/leads.jsonl';
    if (!file_exists($path)) {
        $handle = @fopen($path, 'xb');
        if ($handle !== false) fclose($handle);
    }
    if (!file_exists($path) || !is_writable($path)) {
        throw new RuntimeException('Lead store is unavailable');
    }
    return $path;
}

function append_lead(array $lead): void {
    $path = lead_store_path();
    $handle = fopen($path, 'ab');
    if ($handle === false) throw new RuntimeException('Cannot open lead store');
    try {
        if (!flock($handle, LOCK_EX)) throw new RuntimeException('Cannot lock lead store');
        $line = json_encode($lead, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
        $written = fwrite($handle, $line);
        if ($written === false || $written !== strlen($line)) throw new RuntimeException('Incomplete lead write');
        fflush($handle);
        if (function_exists('fsync')) @fsync($handle);
        flock($handle, LOCK_UN);
    } finally {
        fclose($handle);
    }
}

function stored_leads(): Generator {
    $path = lead_store_path();
    $handle = fopen($path, 'rb');
    if ($handle === false) return;
    try {
        if (!flock($handle, LOCK_SH)) return;
        while (($line = fgets($handle)) !== false) {
            $row = json_decode($line, true);
            if (!is_array($row)) continue;
            if (($row['_type'] ?? '') === 'runtime_config') continue;
            if (($row['id'] ?? '') === '') continue;
            yield $row;
        }
        flock($handle, LOCK_UN);
    } finally {
        fclose($handle);
    }
}

function text_length(string $value): int {
    $count = preg_match_all('/./us', $value, $matches);
    return $count === false ? strlen($value) : $count;
}

function normalize_phone(string $input): ?string {
    $raw = trim($input);
    if ($raw === '') return null;
    $hasPlus = str_starts_with($raw, '+');
    $digits = preg_replace('/\D+/', '', $raw) ?: '';
    if (strlen($digits) < 7 || strlen($digits) > 15) return null;

    if (str_starts_with($digits, '00966')) $digits = substr($digits, 2);
    if (str_starts_with($digits, '9665') && strlen($digits) === 12) return '+' . $digits;
    if (str_starts_with($digits, '05') && strlen($digits) === 10) return '+966' . substr($digits, 1);
    if (str_starts_with($digits, '5') && strlen($digits) === 9) return '+966' . $digits;
    if (str_starts_with($raw, '00')) return '+' . substr($digits, 2);
    if ($hasPlus) return '+' . $digits;
    return $digits;
}

function json_response(array $payload, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function handle_lead_submission(): never {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['ok' => false, 'error' => 'method_not_allowed'], 405);
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    if (!is_array($data)) $data = $_POST;

    $name = trim((string)($data['name'] ?? ''));
    $phone = normalize_phone((string)($data['phone'] ?? ''));
    $service = (string)($data['service'] ?? '');
    $message = trim((string)($data['message'] ?? ''));
    $consent = filter_var($data['consent'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $allowed = service_options();

    $errors = [];
    if (text_length($name) < 2 || text_length($name) > 100) $errors['name'] = 'الاسم غير مكتمل';
    if ($phone === null) $errors['phone'] = 'أدخل رقم تواصل صحيح بأي صيغة معتادة';
    if (!isset($allowed[$service])) $errors['service'] = 'اختر نوع الخدمة';
    if (text_length($message) > 1600) $errors['message'] = 'الرسالة أطول من الحد المسموح';
    if (!$consent) $errors['consent'] = 'الموافقة مطلوبة لإرسال الطلب';
    if ($errors) json_response(['ok' => false, 'errors' => $errors], 422);

    $id = 'SAL-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    $createdAt = gmdate('c');
    $extraFields = ['landing_page_id','landing_url','referrer','utm_source','utm_medium','utm_campaign','utm_term','utm_content','gclid','gbraid','wbraid','ttclid','li_fat_id','session_id'];
    $extra = [];
    foreach ($extraFields as $field) $extra[$field] = substr(trim((string)($data[$field] ?? '')), 0, 500);

    $lead = [
        'id' => $id,
        'created_at' => $createdAt,
        'name' => $name,
        'phone' => $phone,
        'service' => $service,
        'message' => $message,
        'consent' => 1,
        'consent_version' => PRIVACY_VERSION,
    ] + $extra;

    try {
        append_lead($lead);
    } catch (Throwable $e) {
        error_log('Lead write failed: ' . $e->getMessage());
        json_response(['ok' => false, 'error' => 'storage_error'], 500);
    }

    json_response(['ok' => true, 'lead_id' => $id, 'ref' => lead_ref($id)]);
}

function handle_lead_feed(): never {
    $cfg = site_config();
    if ($cfg['feed_token'] === '' || !hash_equals($cfg['feed_token'], (string)($_GET['token'] ?? ''))) {
        http_response_code(404); exit;
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Cache-Control: no-store');
    header('Content-Disposition: attachment; filename="salwa-leads.csv"');
    $out = fopen('php://output', 'wb');
    fputcsv($out, LEAD_FIELDS);
    $rows = iterator_to_array(stored_leads(), false);
    usort($rows, static fn(array $a, array $b): int => strcmp((string)($a['created_at'] ?? ''), (string)($b['created_at'] ?? '')));
    foreach ($rows as $row) {
        fputcsv($out, array_map(static fn(string $field) => $row[$field] ?? '', LEAD_FIELDS));
    }
    fclose($out); exit;
}
