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
  $stmt = $pdo->prepare('SELECT id, username FROM admin_users WHERE id = ?');
  $stmt->execute([(int) $_SESSION['admin_id']]);
  $user = $stmt->fetch();
  return $user ?: null;
}

function auth_login(string $username, string $password): bool
{
  auth_start();
  $pdo = cms_db();
  $stmt = $pdo->prepare('SELECT id, password_hash FROM admin_users WHERE username = ?');
  $stmt->execute([$username]);
  $row = $stmt->fetch();
  if (!$row || !password_verify($password, $row['password_hash'])) {
    return false;
  }
  $_SESSION['admin_id'] = (int) $row['id'];
  return true;
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
