<?php

function auth_start(): void
{
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }
}

function auth_user(): ?array
{
  auth_start();
  if (empty($_SESSION['admin_id'])) {
    return null;
  }
  $pdo = cms_db();
  $stmt = $pdo->prepare('SELECT id, username, role, class_group FROM admin_users WHERE id = ?');
  $stmt->execute([(int) $_SESSION['admin_id']]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$user) {
    return null;
  }
  $user['role'] = auth_normalize_role($user['role'] ?? 'super');
  $user['class_group'] = trim((string) ($user['class_group'] ?? ''));
  return $user;
}

function auth_normalize_role(string $role): string
{
  return $role === 'class' ? 'class' : 'super';
}

function auth_is_super(?array $user = null): bool
{
  $user = $user ?? auth_user();
  return $user && auth_normalize_role($user['role'] ?? 'super') === 'super';
}

function auth_is_class_user(?array $user = null): bool
{
  $user = $user ?? auth_user();
  return $user && auth_normalize_role($user['role'] ?? 'super') === 'class';
}

function auth_user_class_group(?array $user = null): ?string
{
  $user = $user ?? auth_user();
  if (!$user || !auth_is_class_user($user)) {
    return null;
  }
  $group = trim((string) ($user['class_group'] ?? ''));
  return $group !== '' ? $group : null;
}

function auth_can_class_group(string $groupKey, ?array $user = null): bool
{
  if (auth_is_super($user)) {
    return true;
  }
  $scope = auth_user_class_group($user);
  return $scope !== null && $scope === $groupKey;
}

function auth_require_super(): void
{
  auth_require();
  if (!auth_is_super()) {
    header('Location: ' . redirect_url('login') . '?p=attachments&flash=' . urlencode('You do not have permission for that action.') . '&t=err');
    exit;
  }
}

function auth_attachment_row_allowed(PDO $pdo, int $id, ?array $user = null): bool
{
  $user = $user ?? auth_user();
  if (!$user) {
    return false;
  }
  if (auth_is_super($user)) {
    return true;
  }
  $row = cms_attachment_get_by_id($pdo, $id);
  if (!$row) {
    return false;
  }
  return auth_can_class_group((string) ($row['class_group'] ?? ''), $user);
}

function auth_login(string $username, string $password): bool
{
  auth_start();
  $username = cms_normalize_admin_username($username);
  if ($username === '') {
    return false;
  }
  $pdo = cms_db();
  $stmt = $pdo->prepare('SELECT id, password_hash FROM admin_users WHERE LOWER(username) = LOWER(?)');
  $stmt->execute([$username]);
  $row = $stmt->fetch();
  if (!$row || !password_verify($password, $row['password_hash'])) {
    return false;
  }
  $_SESSION['admin_id'] = (int) $row['id'];
  return true;
}

function auth_login_redirect_url(): string
{
  $user = auth_user();
  if ($user && auth_is_class_user($user)) {
    return redirect_url('login') . '?p=attachments';
  }
  return redirect_url('login');
}

function auth_logout(): void
{
  auth_start();
  unset($_SESSION['admin_id']);
}

function auth_require(): void
{
  if (!auth_user()) {
    if (!function_exists('site_url')) {
      require_once __DIR__ . '/data.php';
    }
    header('Location: ' . redirect_url('login'));
    exit;
  }
}

function auth_change_password(int $userId, string $newPassword): void
{
  $pdo = cms_db();
  $stmt = $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?');
  $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
}
