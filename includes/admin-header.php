<?php
/**
 * Shared admin shell — sidebar + topbar. Included at the top of every
 * admin/*.php page, after that page has already called require_admin().
 * Expects (optionally) $pageTitle and $activeAdminNav.
 */
$pageTitle = $pageTitle ?? 'Admin';
$activeAdminNav = $activeAdminNav ?? '';
$adminUser = current_user();

$navItems = [
    'analytics' => ['label' => 'Analytics', 'href' => base_url('admin/index.php'), 'icon' => 'M3 3v18h18M7 14l4-4 3 3 5-6'],
    'properties' => ['label' => 'Property Management', 'href' => base_url('admin/properties.php'), 'icon' => 'M3 10l9-7 9 7M5 10v10h14V10'],
    'investments' => ['label' => 'Investment Opportunities', 'href' => base_url('admin/investments.php'), 'icon' => 'M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6'],
    'purchases' => ['label' => 'Investor Ledger', 'href' => base_url('admin/purchases.php'), 'icon' => 'M3 7l9-4 9 4v2H3zM5 9v9h14V9M9 13h6'],
    'inquiries' => ['label' => 'Inquiries', 'href' => base_url('admin/inquiries.php'), 'icon' => 'M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z'],
    'reviews' => ['label' => 'Review Moderation', 'href' => base_url('admin/reviews.php'), 'icon' => 'M12 17.3l-5.4 3 1-6-4.6-4 6.1-.6L12 4l2.9 5.7 6.1.6-4.6 4 1 6z'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle) ?> | Atlantis Admin</title>
<meta name="description" content="Atlantis Homes — premium construction and smart real estate investment in Lagos and Abuja.">

<meta property="og:type" content="website">
<meta property="og:title" content="<?= h($pageTitle) ?> | Atlantis Homes">
<meta property="og:description" content="Atlantis Homes — premium construction and smart real estate investment in Lagos and Abuja.">
<meta property="og:url" content="<?= base_url($_SERVER['REQUEST_URI'] ?? '') ?>">
<meta property="og:image" content="<?= base_url('assets/images/logo.png') ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:site_name" content="Atlantis Homes">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= h($pageTitle) ?> | Atlantis Homes">
<meta name="twitter:description" content="Atlantis Homes — premium construction and smart real estate investment in Lagos and Abuja.">
<meta name="twitter:image" content="<?= base_url('assets/images/logo.png') ?>">

<link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/images/logo.png') ?>">
<link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/images/logo.png') ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('assets/images/logo.png') ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<script src="<?= base_url('assets/js/tailwind.js') ?>"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          obsidian: { DEFAULT: '#0B0B0C', soft: '#141416', card: '#181819' },
          gold: { DEFAULT: '#C5A880', deep: '#9C7F56', light: '#E3D2B8' },
          slate: { DEFAULT: '#A1A1AA' },
        },
        fontFamily: {
          display: ['"Playfair Display"', 'serif'],
          sans: ['"Plus Jakarta Sans"', 'sans-serif'],
        },
      },
    },
  };
</script>
<meta name="csrf-token" content="<?= h(csrf_token()) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
<script>window.ATLANTIS_BASE_URL = <?= json_encode(base_path()) ?>;</script>
</head>
<body class="bg-obsidian text-white font-sans antialiased">

<div class="flex min-h-screen">

  <!-- Sidebar -->
  <aside class="hidden lg:flex w-72 flex-col border-r border-white/10 bg-obsidian-soft bg-[#0E0E10] px-6 py-8 shrink-0">
    <a href="<?= base_url('admin/index.php') ?>" class="flex items-center gap-2 mb-12">
      <!-- <svg width="26" height="26" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-gold">
        <path d="M15 2L27 24H3L15 2Z" stroke="currentColor" stroke-width="1.4"/>
        <path d="M15 11L21 24H9L15 11Z" fill="currentColor"/>
      </svg>
      <span class="font-display text-lg">Atlantis <span class="text-gold">Admin</span></span> -->

      <img src="<?= base_url('assets/images/logo.png') ?>" alt="Atlantis Homes" class="w-[200px] object-contain">
    </a>

    <nav class="flex flex-col gap-1.5">
      <?php foreach ($navItems as $key => $item): ?>
        <a href="<?= $item['href'] ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors <?= $activeAdminNav === $key ? 'bg-gold/15 text-gold' : 'text-slate hover:bg-white/5 hover:text-white' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="<?= $item['icon'] ?>"/></svg>
          <?= h($item['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="mt-auto pt-8 border-t border-white/10">
      <a href="<?= base_url('index.php') ?>" class="block text-sm text-slate hover:text-white mb-4">&larr; View Public Site</a>
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium"><?= h($adminUser['name']) ?></p>
          <p class="text-xs text-slate"><?= h($adminUser['email']) ?></p>
        </div>
        <a href="<?= base_url('logout.php') ?>" aria-label="Sign out" class="text-slate hover:text-gold">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
        </a>
      </div>
    </div>
  </aside>

  <!-- Mobile topbar -->
  <div class="lg:hidden fixed top-0 inset-x-0 z-40 glass border-b border-white/10 px-5 h-16 flex items-center justify-between">
    <a href="<?= base_url('admin/index.php') ?>" class="font-display text-lg">
      <img src="<?= base_url('assets/images/logo.png') ?>" alt="Atlantis Homes" class="w-[150px] object-contain">
    </a>
    <button id="admin-mobile-menu-btn" class="text-white p-2" aria-label="Toggle menu" aria-expanded="false">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
    </button>
  </div>
  <div id="admin-mobile-menu" class="hidden lg:hidden fixed top-16 inset-x-0 z-30 bg-obsidian border-b border-white/10 px-5 py-5 flex flex-col gap-2">
    <?php foreach ($navItems as $key => $item): ?>
      <a href="<?= $item['href'] ?>" class="px-4 py-3 rounded-xl text-sm font-medium <?= $activeAdminNav === $key ? 'bg-gold/15 text-gold' : 'text-slate hover:bg-white/5' ?>"><?= h($item['label']) ?></a>
    <?php endforeach; ?>
    <a href="<?= base_url('logout.php') ?>" class="px-4 py-3 rounded-xl text-sm font-medium text-slate hover:bg-white/5">Sign Out</a>
  </div>

  <!-- Main content -->
  <main class="flex-1 px-6 lg:px-12 py-10 lg:py-12 pt-24 lg:pt-12 max-w-6xl">
