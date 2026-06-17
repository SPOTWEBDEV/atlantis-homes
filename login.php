<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Already signed in? Send them straight to the right portal.
if (is_logged_in()) {
    $user = current_user();
    header('Location: ' . ($user['role'] === 'admin' ? base_url('admin/index.php') : base_url('dashboard.php')));
    exit;
}

$pageTitle = 'Investor Login';
$activeNav = 'login';

require __DIR__ . '/includes/header.php';
?>

<section class="pt-40 pb-28 max-w-md mx-auto px-6">
  <div data-reveal class="text-center mb-10">
    <p class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Investor Login</p>
    <h1 class="font-display text-3xl sm:text-4xl">Welcome back.</h1>
    <p class="text-slate text-sm mt-3">Sign in to track your purchase, milestones, and ledger.</p>
  </div>

  <form id="login-form" data-reveal data-reveal-delay="100" class="border border-white/10 rounded-3xl p-8 bg-obsidian-card" novalidate>
    <label class="block text-sm mb-5">
      <span class="block font-medium mb-1.5">Email</span>
      <input type="email" name="email" required autocomplete="username" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
    <label class="block text-sm mb-2">
      <span class="block font-medium mb-1.5">Password</span>
      <input type="password" name="password" required autocomplete="current-password" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>

    <p id="login-error" class="hidden text-sm text-red-400 mt-4" role="alert"></p>

    <button type="submit" id="login-submit-btn" class="w-full mt-6 bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full py-3.5 transition-colors">Sign In</button>

    <div class="mt-6 pt-6 border-t border-white/10 text-xs text-slate leading-relaxed">
      <p class="font-semibold text-white mb-1">Demo accounts</p>
      <p>Investor — chiamaka@example.com / Investor@123</p>
      <p>Admin — admin@atlantishomes.ng / Admin@123</p>
    </div>
  </form>
</section>

<script>
  const BASE = window.ATLANTIS_BASE_URL || '';
  const form = document.getElementById('login-form');
  const errorEl = document.getElementById('login-error');
  const submitBtn = document.getElementById('login-submit-btn');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    errorEl.classList.add('hidden');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Signing in…';

    try {
      const res = await fetch(`${BASE}/api/login.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          email: form.email.value,
          password: form.password.value,
        }),
      });
      const data = await res.json();
      if (!data.ok) {
        errorEl.textContent = data.error || 'Could not sign you in.';
        errorEl.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Sign In';
        return;
      }
      window.location.href = BASE + data.redirect;
    } catch (err) {
      errorEl.textContent = 'Something went wrong — please try again.';
      errorEl.classList.remove('hidden');
      submitBtn.disabled = false;
      submitBtn.textContent = 'Sign In';
    }
  });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
