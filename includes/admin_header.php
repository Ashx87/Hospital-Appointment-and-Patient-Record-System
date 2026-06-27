<?php
/**
 * includes/admin_header.php — Admin console shell (top of every admin page)
 *
 * Why a separate header from includes/header.php:
 *   The admin area is a back-office "console" with a different navigation model
 *   (a persistent left sidebar) than the patient/doctor/receptionist areas, which
 *   keep the simple top bar. Splitting the shell keeps each area's markup focused
 *   and means restyling the admin console never risks the public-facing pages.
 *
 * Responsibilities:
 *   - Emit the shared <head> (admin stylesheet scope lives in assets/css/style.css)
 *   - Render the fixed sidebar (brand + role-scoped nav) and the sticky topbar
 *   - Highlight the active nav item using $adminNav set by the including page
 *   - Render the one-time flash message inside the content area
 *   - Open <section class="admin-content"> which includes/admin_footer.php closes
 *
 * Contract for the including page (set BEFORE requiring this file):
 *   $pageTitle    string  Browser tab + topbar heading (required)
 *   $adminNav     string  Active nav key: dashboard|users|doctors|reports (required)
 *   $pageSubtitle string  Optional one-line description shown under the heading
 *
 * session_start() and Auth::requireRole('admin') must have already run in the page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/flash.php';

/** Inline SVG icon set (Lucide-style, 24×24 stroke). No emojis, no external requests. */
function adminIcon(string $name): string
{
    $paths = [
        'dashboard' => '<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>',
        'users'     => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'doctors'   => '<path d="M8 2v4"/><path d="M16 2v4"/><path d="M6 6h12a2 2 0 0 1 2 2v6a8 8 0 0 1-16 0V8a2 2 0 0 1 2-2Z"/><circle cx="12" cy="16" r="2"/>',
        'reports'   => '<path d="M3 3v18h18"/><rect x="7" y="11" width="3" height="6"/><rect x="12" y="7" width="3" height="10"/><rect x="17" y="13" width="3" height="4"/>',
        'patients'  => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.5 4.04 3 5.5l7 7Z"/>',
        'calendar'  => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'logout'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
    ];
    $inner = $paths[$name] ?? '';
    return '<svg class="ad-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
         . 'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
         . $inner . '</svg>';
}

$adminNav   = $adminNav   ?? '';
$navItems   = [
    'dashboard' => ['label' => 'Dashboard', 'href' => 'dashboard.php'],
    'users'     => ['label' => 'Users',     'href' => 'users.php'],
    'doctors'   => ['label' => 'Doctors',   'href' => 'doctors.php'],
    'reports'   => ['label' => 'Reports',   'href' => 'reports.php'],
];
$adminName  = $_SESSION['name'] ?? 'Admin';
$initial    = strtoupper(mb_substr($adminName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin Console') ?> · HospitalCare</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>

<body class="admin-body">
<div class="admin-shell">

    <!-- ─── Sidebar ─────────────────────────────────────────────── -->
    <aside class="admin-sidebar" id="adminSidebar">
        <a href="<?= BASE_URL ?>pages/admin/dashboard.php" class="admin-brand">
            <span class="admin-brand__mark"><?= adminIcon('doctors') ?></span>
            <span class="admin-brand__text">HospitalCare<small>Admin Console</small></span>
        </a>

        <nav class="admin-nav" aria-label="Admin sections">
            <?php foreach ($navItems as $key => $item): ?>
                <a href="<?= BASE_URL ?>pages/admin/<?= $item['href'] ?>"
                   class="admin-nav__link<?= $adminNav === $key ? ' is-active' : '' ?>"
                   <?= $adminNav === $key ? 'aria-current="page"' : '' ?>>
                    <?= adminIcon($key) ?>
                    <span><?= htmlspecialchars($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <a href="<?= BASE_URL ?>logout.php" class="admin-nav__link admin-nav__logout">
            <?= adminIcon('logout') ?><span>Log out</span>
        </a>
    </aside>

    <!-- ─── Main column ─────────────────────────────────────────── -->
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="admin-topbar__heading">
                <h1><?= htmlspecialchars($pageTitle ?? 'Admin Console') ?></h1>
                <?php if (!empty($pageSubtitle)): ?>
                    <p><?= htmlspecialchars($pageSubtitle) ?></p>
                <?php endif; ?>
            </div>
            <div class="admin-user">
                <span class="admin-user__avatar" aria-hidden="true"><?= htmlspecialchars($initial) ?></span>
                <span class="admin-user__meta">
                    <strong><?= htmlspecialchars($adminName) ?></strong>
                    <small>Administrator</small>
                </span>
            </div>
        </header>

        <section class="admin-content">
            <?php displayFlash(); ?>
