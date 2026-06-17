<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    $user = current_user();
    header('Location: ' . ($user['role'] === 'admin' ? base_url('admin/index.php') : base_url('dashboard.php')));
    exit;
}

$pageTitle = 'Create an Account';
$activeNav = 'login';

require __DIR__ . '/includes/header.php';
?>

<section class="pt-40 pb-28 max-w-md mx-auto px-6">
  <div data-reveal class="text-center mb-10">
    <p class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Create an Account</p>
    <h1 class="font-display text-3xl sm:text-4xl">Become an investor.</h1>
    <p class="text-slate text-sm mt-3">Register to reserve investments, track milestones, and review your payment ledger.</p>
  </div>

  <form id="register-form" data-reveal data-reveal-delay="100" class="border border-white/10 rounded-3xl p-8 bg-obsidian-card" novalidate>
    <label class="block text-sm mb-5">
      <span class="block font-medium mb-1.5">Full Name</span>
      <input type="text" name="name" required autocomplete="name" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
    <label class="block text-sm mb-5">
      <span class="block font-medium mb-1.5">Email</span>
      <input type="email" name="email" required autocomplete="username" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
    <label class="block text-sm mb-2">
      <span class="block font-medium mb-1.5">Password</span>
      <input type="password" name="password" required minlength="8" autocomplete="new-password" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
    <p class="text-xs text-slate mb-5">At least 8 characters.</p>

    <p id="register-error" class="hidden text-sm text-red-400 mt-2 mb-2" role="alert"></p>

    <button type="submit" id="register-submit-btn" class="w-full mt-4 bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full py-3.5 transition-colors">Create Account</button>

    <p class="text-sm text-slate text-center mt-6">Already registered? <a href="<?= base_url('login.php') ?>" class="text-gold hover:text-gold-light">Sign in</a></p>
  </form>
</section>

<script>
  const BASE = window.ATLANTIS_BASE_URL || '';
  const form = document.getElementById('register-form');
  const errorEl = document.getElementById('register-error');
  const submitBtn = document.getElementById('register-submit-btn');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    errorEl.classList.add('hidden');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating account…';

    try {
      const res = await fetch(`${BASE}/api/register.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: form.name.value,
          email: form.email.value,
          password: form.password.value,
        }),
      });
      const data = await res.json();
      if (!data.ok) {
        errorEl.textContent = data.error || 'Could not create your account.';
        errorEl.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Create Account';
        return;
      }
      window.location.href = BASE + data.redirect;
    } catch (err) {
      errorEl.textContent = 'Something went wrong — please try again.';
      errorEl.classList.remove('hidden');
      submitBtn.disabled = false;
      submitBtn.textContent = 'Create Account';
    }
  });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
