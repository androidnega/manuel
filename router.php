<?php
/** PHP built-in server router: php -S localhost:8080 router.php */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');

if (preg_match('/\.php$/i', $uri)) {
    $clean = preg_replace('/\.php$/i', '', $uri);
    if ($clean === '') {
        $clean = '/';
    }
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    header('Location: ' . $clean . ($qs !== '' ? '?' . $qs : ''), true, 301);
    exit;
}

$root = __DIR__;

if ($uri !== '/' && is_file($root . $uri)) {
    return false;
}

$candidate = $uri === '/' ? '/index.php' : $uri;
if (!str_ends_with(strtolower($candidate), '.php')) {
    $candidate .= '.php';
}

$script = $root . $candidate;
if (is_file($script)) {
    require $script;
    return true;
}

http_response_code(404);
echo '404 Not Found';
return true;
