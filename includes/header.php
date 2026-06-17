<?php
/**
 * Shared <head> + sticky nav, included at the top of every public-facing page.
 * Expects (optionally) $pageTitle and $activeNav to be set before include.
 */
$pageTitle = $pageTitle ?? 'Atlantis Homes';
$activeNav = $activeNav ?? '';
$navUser   = current_user();
?>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle) ?> | Atlantis Homes</title>
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
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">

<script>
  window.ATLANTIS_BASE_URL = <?= json_encode(base_path()) ?>;
</script>
</head>
<body class="bg-obsidian text-white font-sans antialiased">

<a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:bg-gold focus:text-obsidian focus:px-4 focus:py-2 focus:rounded">Skip to content</a>

<header id="site-nav" class="fixed top-0 inset-x-0 z-40 glass border-b border-white/10 transition-colors">
  <nav class="max-w-7xl mx-auto px-6 lg:px-10  flex items-center justify-between">
    <a href="<?= base_url('index.php') ?>" class="flex items-center gap-2 group">
      <img src="<?= base_url('assets/images/logo.png') ?>" alt="Atlantis Homes" class="w-[200px] object-contain group-hover:scale-105 transition-transform">
      
    </a>

    <div class="hidden lg:flex items-center gap-7 font-medium text-sm">
      <a href="<?= base_url('portfolio.php') ?>" class="hover:text-gold transition-colors <?= $activeNav === 'portfolio' ? 'text-gold' : '' ?>">Portfolio</a>
      <a href="<?= base_url('investor-hub.php') ?>" class="hover:text-gold transition-colors <?= $activeNav === 'investor-hub' ? 'text-gold' : '' ?>">Investor Hub</a>
      <a href="<?= base_url('estimate.php') ?>" class="hover:text-gold transition-colors <?= $activeNav === 'estimate' ? 'text-gold' : '' ?>">Build Estimate</a>
      <a href="<?= base_url('reviews.php') ?>" class="hover:text-gold transition-colors <?= $activeNav === 'reviews' ? 'text-gold' : '' ?>">Reviews</a>
      <a href="<?= base_url('contact.php') ?>" class="hover:text-gold transition-colors <?= $activeNav === 'contact' ? 'text-gold' : '' ?>">Contact</a>
      <?php if ($navUser): ?>
        <a href="<?= $navUser['role'] === 'admin' ? base_url('admin/index.php') : base_url('dashboard.php') ?>" class="hover:text-gold transition-colors <?= $activeNav === 'dashboard' ? 'text-gold' : '' ?>">
          <?= $navUser['role'] === 'admin' ? 'Admin Center' : 'My Portal' ?>
        </a>
      <?php endif; ?>
    </div>

    <div class="hidden lg:flex items-center gap-3">
      <a href="<?= base_url('book-a-session.php') ?>" class="text-sm font-semibold border border-white/15 hover:border-gold hover:text-gold rounded-full px-5 py-2 transition-colors <?= $activeNav === 'book' ? 'border-gold text-gold' : '' ?>">Book a Session</a>
      <?php if ($navUser): ?>
        <a href="<?= base_url('logout.php') ?>" class="text-sm font-semibold bg-gold text-obsidian hover:bg-gold-light rounded-full px-5 py-2 transition-colors">Sign Out</a>
      <?php else: ?>
        <a href="<?= base_url('login.php') ?>" class="text-sm font-semibold bg-gold text-obsidian hover:bg-gold-light rounded-full px-5 py-2 transition-colors">Investor Login</a>
      <?php endif; ?>
    </div>

    <button id="mobile-menu-btn" aria-label="Toggle menu" aria-expanded="false" class="lg:hidden text-white p-2 -mr-2">
      <svg id="menu-icon-open" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      <svg id="menu-icon-close" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="hidden"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
  </nav>

  <div id="mobile-menu" class="hidden lg:hidden border-t border-white/10 px-6 py-6 flex flex-col gap-5 font-medium">
    <a href="<?= base_url('portfolio.php') ?>" class="hover:text-gold">Portfolio</a>
    <a href="<?= base_url('investor-hub.php') ?>" class="hover:text-gold">Investor Hub</a>
    <a href="<?= base_url('estimate.php') ?>" class="hover:text-gold">Build Estimate</a>
    <a href="<?= base_url('reviews.php') ?>" class="hover:text-gold">Reviews</a>
    <a href="<?= base_url('contact.php') ?>" class="hover:text-gold">Contact</a>
    <a href="<?= base_url('book-a-session.php') ?>" class="hover:text-gold">Book a Session</a>
    <?php if ($navUser): ?>
      <a href="<?= $navUser['role'] === 'admin' ? base_url('admin/index.php') : base_url('dashboard.php') ?>" class="hover:text-gold"><?= $navUser['role'] === 'admin' ? 'Admin Center' : 'My Portal' ?></a>
      <a href="<?= base_url('logout.php') ?>" class="hover:text-gold">Sign Out</a>
    <?php else: ?>
      <a href="<?= base_url('login.php') ?>" class="inline-block bg-gold text-obsidian text-center rounded-full px-5 py-2.5">Investor Login</a>
    <?php endif; ?>
  </div>
</header>

<main id="main-content">
