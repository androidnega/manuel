<?php

/** Normalize admin view slug (e.g. page → pages). */
function admin_nav_section(string $view): string
{
  $view = preg_replace('/[^a-z]/', '', $view);
  if ($view === '' || $view === 'dashboard') {
    return 'dashboard';
  }
  $parents = [
    'page' => 'pages',
    'list' => 'lists',
    'newsedit' => 'news',
  ];
  return $parents[$view] ?? $view;
}

function admin_nav_is_active(string $section, string $view): bool
{
  return admin_nav_section($view) === $section;
}

function admin_nav_class(string $section, string $view): string
{
  return admin_nav_is_active($section, $view)
    ? 'is-active bg-deep text-white shadow-sleek-sm'
    : 'text-body hover:bg-cloud hover:text-ink';
}

function admin_icon(string $name): string
{
  $icons = [
    'dashboard' => 'fa-gauge-high',
    'pages' => 'fa-file-lines',
    'lists' => 'fa-table-list',
    'team' => 'fa-users',
    'messages' => 'fa-envelope',
    'settings' => 'fa-gear',
    'site' => 'fa-arrow-up-right-from-square',
    'logout' => 'fa-right-from-bracket',
    'user' => 'fa-user',
    'save' => 'fa-floppy-disk',
    'back' => 'fa-arrow-left',
    'login' => 'fa-right-to-bracket',
    'quote' => 'fa-file-invoice-dollar',
    'attachments' => 'fa-graduation-cap',
    'news' => 'fa-newspaper',
    'gallery' => 'fa-images',
    'homehero' => 'fa-panorama',
  ];
  $class = $icons[$name] ?? 'fa-circle';
  return '<i class="fa-solid ' . $class . '" aria-hidden="true"></i>';
}
