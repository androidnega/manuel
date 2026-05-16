<?php
/**
 * Route a request when the app runs in a subfolder but URLs are at domain root.
 * Used by deploy/public_html.index.php (copy to public_html/index.php).
 */
declare(strict_types=1);

function front_router_slug_aliases(): array
{
  return [
    'project' => 'projects',
  ];
}

function front_router_mime(string $ext): string
{
  $map = [
    'css' => 'text/css; charset=UTF-8',
    'js' => 'application/javascript; charset=UTF-8',
    'webp' => 'image/webp',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'svg' => 'image/svg+xml',
    'ico' => 'image/x-icon',
    'woff' => 'font/woff',
    'woff2' => 'font/woff2',
    'txt' => 'text/plain; charset=UTF-8',
    'xml' => 'application/xml',
    'json' => 'application/json',
    'webmanifest' => 'application/manifest+json',
  ];
  return $map[$ext] ?? 'application/octet-stream';
}

/** @return bool true if handled */
function front_router_dispatch(string $appRoot, string $appFolder = 'manuelcode'): bool
{
  $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
  $path = '/' . trim(str_replace('\\', '/', $path), '/');
  $query = $_SERVER['QUERY_STRING'] ?? '';

  if ($path !== '/') {
    $static = $appRoot . $path;
    if (is_file($static)) {
      $ext = strtolower(pathinfo($static, PATHINFO_EXTENSION));
      $allowed = ['css', 'js', 'map', 'webp', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'txt', 'xml', 'json', 'webmanifest'];
      if (in_array($ext, $allowed, true)) {
        header('Content-Type: ' . front_router_mime($ext));
        header('Content-Length: ' . (string) filesize($static));
        readfile($static);
        return true;
      }
    }
  }

  $segment = strtolower(trim($path, '/'));
  $aliases = front_router_slug_aliases();
  if ($segment !== '' && isset($aliases[$segment])) {
    header('Location: /' . $aliases[$segment] . ($query !== '' ? '?' . $query : ''), true, 301);
    return true;
  }

  if ($segment === '') {
    $script = 'index.php';
  } elseif (preg_match('/^[a-z][a-z0-9-]*$/', $segment)) {
    $script = $segment . '.php';
  } else {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo '404 Not Found';
    return true;
  }

  $scriptPath = $appRoot . '/' . $script;
  if (!is_file($scriptPath)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo '404 Not Found';
    return true;
  }

  $_SERVER['SCRIPT_NAME'] = '/' . trim($appFolder, '/') . '/' . $script;
  $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
  chdir($appRoot);
  require $scriptPath;
  return true;
}
