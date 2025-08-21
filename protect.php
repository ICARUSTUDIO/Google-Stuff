<?php
// bot_protect.php
// Defense-in-depth bot protection for shared hosts using a JSON file store.
// Educational: do NOT use for SEO cloaking.

declare(strict_types=1);

// ------------------ Config --------------------
$HUMAN_COOKIE = 'site_human_js';
$JS_COOKIE_MAX = 3600;                 // seconds
$RATE_LIMIT_WINDOW = 60;               // seconds
$RATE_LIMIT_MAX = 100;                 // max requests in window per IP (tune)
$HONEYPOT_PATH = '/__hp_trap_9d2f';    // change to a secret path you monitor
$SUSPICIOUS_LOG = __DIR__ . '/suspicious.log';
define('RATE_LIMIT_WINDOW', 60);
define('RATE_LIMIT_MAX', 100);

// JSON store location (folder will be created automatically)
$CACHE_DIR = __DIR__ . '/cache';
$JSON_STORE = $CACHE_DIR . '/bot_store.json';
$JSON_PRUNE_EVERY = 100;   // prune after this many writes (keeps file lean)

// ------------------ Utilities -----------------
function clientIp(): string {
    // Basic REMOTE_ADDR - if behind proxy you might check HTTP_X_FORWARDED_FOR
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function nowIso(): string {
    return date('c');
}

function logSuspicious(string $ip, string $reason) {
    global $SUSPICIOUS_LOG;
    $line = nowIso() . " $ip $reason " . ($_SERVER['REQUEST_URI'] ?? '-') . PHP_EOL;
    @file_put_contents($SUSPICIOUS_LOG, $line, FILE_APPEND | LOCK_EX);
}

// ------------------ JSON store helpers -----------------
function ensureCacheDir() {
    global $CACHE_DIR;
    if (!is_dir($CACHE_DIR)) {
        @mkdir($CACHE_DIR, 0755, true);
    }
}

function loadStore(): array {
    global $JSON_STORE;
    ensureCacheDir();
    if (!file_exists($JSON_STORE)) {
        return ['meta' => ['writes' => 0], 'ips' => []];
    }
    $fh = @fopen($JSON_STORE, 'r');
    if (!$fh) return ['meta' => ['writes' => 0], 'ips' => []];
    if (!flock($fh, LOCK_SH)) {
        fclose($fh);
        return ['meta' => ['writes' => 0], 'ips' => []];
    }
    $contents = stream_get_contents($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    $data = json_decode($contents, true);
    if (!is_array($data)) return ['meta' => ['writes' => 0], 'ips' => []];
    return $data;
}

function saveStore(array $store): bool {
    global $JSON_STORE;
    ensureCacheDir();
    $fh = @fopen($JSON_STORE, 'c+');
    if (!$fh) return false;
    if (!flock($fh, LOCK_EX)) {
        fclose($fh);
        return false;
    }
    ftruncate($fh, 0);
    rewind($fh);
    $written = fwrite($fh, json_encode($store, JSON_PRETTY_PRINT));
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    return ($written !== false);
}

function pruneStore(array &$store) {
    // Remove IPs that are old (not seen for a long time)
    $now = time();
    $ttl = 60 * 60 * 24; // 24 hours default TTL for entries
    foreach ($store['ips'] as $ip => $rec) {
        if (isset($rec['last_seen']) && ($now - $rec['last_seen']) > $ttl) {
            unset($store['ips'][$ip]);
        }
    }
}

// ------------------ 1) Verify well-known crawlers by reverse DNS -----------------
function verifyCrawlerByReverseDNS(string $ip, array $allowedSuffixes = ['googlebot.com','google.com','search.msn.com']): bool {
    $host = @gethostbyaddr($ip);
    if (!$host || $host === $ip) return false;
    $hostLower = strtolower($host);
    foreach ($allowedSuffixes as $sfx) {
        if (str_ends_with($hostLower, strtolower($sfx))) {
            $resolved = @gethostbyname($hostLower);
            if ($resolved === $ip) return true;
        }
    }
    return false;
}

// ------------------ 2) UA + header heuristics -----------------
function isLikelyBotUA(): bool {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $uaLower = strtolower($ua);

    $botIndicators = [
        'bot','crawler','spider','crawl','scan','headless','phantomjs',
        'wget','curl','python-requests','httpclient','scrapy','java/','libwww-perl','nikto'
    ];
    foreach ($botIndicators as $ind) {
        if (strpos($uaLower, $ind) !== false) return true;
    }

    if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) || empty($_SERVER['HTTP_ACCEPT'])) {
        return true;
    }

    return false;
}

// ------------------ 3) JS cookie challenge -----------------
function requireJsAndCookieChallenge() {
    global $HUMAN_COOKIE, $JS_COOKIE_MAX;
    if (!empty($_COOKIE[$HUMAN_COOKIE]) && $_COOKIE[$HUMAN_COOKIE] === '1') return;
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Checking...</title></head><body>';
    echo '<noscript><h2>Please enable JavaScript to view this site.</h2></noscript>';
    echo '<script>
      try {
        document.cookie = "'.addslashes($HUMAN_COOKIE).'=1; path=/; max-age=' . intval($JS_COOKIE_MAX) . '";
        if (!location.search.includes("jsok=1")) {
          const sep = location.search ? "&" : "?";
          location.href = location.pathname + location.search + sep + "jsok=1";
        } else {
          const url = new URL(location.href);
          url.searchParams.delete("jsok");
          location.replace(url.toString());
        }
      } catch(e) { document.body.innerHTML = "<h2>Error</h2>"; }
    </script>';
    echo '</body></html>';
    exit;
}

// ------------------ 4) Honeypot detection -----------------
function checkHoneypotHit() {
    global $HONEYPOT_PATH;
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    if (strpos($uri, $HONEYPOT_PATH) === 0) {
        $ip = clientIp();
        logSuspicious($ip, 'honeypot-hit');
        http_response_code(403);
        echo "Access Denied";
        exit;
    }
}

// ------------------ 5) Rate limiter using JSON store -----------------
function incrementIpCounter(string $ip): array {
    global $JSON_PRUNE_EVERY;
    $store = loadStore();
    if (!isset($store['meta'])) $store['meta'] = ['writes' => 0];
    if (!isset($store['ips'])) $store['ips'] = [];

    $now = time();
    if (!isset($store['ips'][$ip])) {
        $store['ips'][$ip] = ['count' => 1, 'window_start' => $now, 'last_seen' => $now, 'ua' => $_SERVER['HTTP_USER_AGENT'] ?? ''];
    } else {
        // reset window if passed
        if (($now - $store['ips'][$ip]['window_start']) > RATE_LIMIT_WINDOW) {
            $store['ips'][$ip]['count'] = 1;
            $store['ips'][$ip]['window_start'] = $now;
        } else {
            $store['ips'][$ip]['count']++;
        }
        $store['ips'][$ip]['last_seen'] = $now;
    }

    // increment write count and prune occasionally
    $store['meta']['writes'] = ($store['meta']['writes'] ?? 0) + 1;
    if (($store['meta']['writes'] % $JSON_PRUNE_EVERY) === 0) {
        pruneStore($store);
    }

    saveStore($store);
    return $store['ips'][$ip];
}

function isRateLimited(string $ip): bool {
    $rec = incrementIpCounter($ip);
    $now = time();
    $count = $rec['count'] ?? 0;
    $window_start = $rec['window_start'] ?? $now;
    if (($now - $window_start) > RATE_LIMIT_WINDOW) {
        return false;
    }
    return ($count > RATE_LIMIT_MAX);
}

// ------------------ 6) Top-level flow -----------------
$ip = clientIp();
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

// 6a) Honeypot path check always first
checkHoneypotHit();

// 6b) If UA claims known crawlers - verify and skip challenges if verified
$lowerUa = strtolower($ua);
if (strpos($lowerUa, 'googlebot') !== false || strpos($lowerUa, 'bingbot') !== false) {
    if (verifyCrawlerByReverseDNS($ip)) {
        // trusted crawler — let through
        return;
    } else {
        logSuspicious($ip, 'claimed-crawler-unverified:' . $ua);
    }
}

// 6c) Basic heuristics
if (isLikelyBotUA()) {
    logSuspicious($ip, 'ua-suspicious:' . substr($ua,0,200));
    // rate-limit check
    if (isRateLimited($ip)) {
        http_response_code(429);
        header('Retry-After: 60');
        echo "Too many requests";
        exit;
    }
    // JS challenge
    requireJsAndCookieChallenge();
}

// If we reach here consider visitor likely human; continue rendering the page normally.
return;
