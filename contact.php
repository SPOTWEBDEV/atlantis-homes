<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Contact';
$activeNav = 'contact';
$user = current_user();

require __DIR__ . '/includes/header.php';
?>

<section class="pt-36 pb-24 max-w-7xl mx-auto px-6 lg:px-10">
  <div class="grid lg:grid-cols-5 gap-12">

    <div data-reveal class="lg:col-span-2">
      <p class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Contact</p>
      <h1 class="font-display text-4xl sm:text-5xl">Let's talk.</h1>
      <p class="text-slate mt-4 max-w-sm">Reach us directly, or send a message and a member of our investment team will respond within one business day.</p>

      <ul class="mt-10 space-y-6">
        <li class="flex items-start gap-4">
          <span class="w-10 h-10 rounded-full bg-gold/15 text-gold flex items-center justify-center shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 7l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
          </span>
          <div>
            <p class="text-xs text-slate uppercase tracking-wider">Email</p>
            <a href="mailto:invest@atlantishomes.ng" class="font-medium hover:text-gold transition-colors">invest@atlantishomes.ng</a>
          </div>
        </li>
        <li class="flex items-start gap-4">
          <span class="w-10 h-10 rounded-full bg-gold/15 text-gold flex items-center justify-center shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
          </span>
          <div>
            <p class="text-xs text-slate uppercase tracking-wider">Phone</p>
            <a href="tel:+23417005582" class="font-medium hover:text-gold transition-colors">+234 1 700 5582</a>
          </div>
        </li>
        <li class="flex items-start gap-4">
          <span class="w-10 h-10 rounded-full bg-gold/15 text-gold flex items-center justify-center shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
          </span>
          <div>
            <p class="text-xs text-slate uppercase tracking-wider">Instagram</p>
            <a href="https://www.instagram.com/atlantishomes.ng" target="_blank" rel="noopener" class="font-medium hover:text-gold transition-colors">@atlantishomes.ng</a>
          </div>
        </li>
        <li class="flex items-start gap-4">
          <span class="w-10 h-10 rounded-full bg-gold/15 text-gold flex items-center justify-center shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
          </span>
          <div>
            <p class="text-xs text-slate uppercase tracking-wider">Head Office</p>
            <p class="font-medium">16B Kofo Abayomi Street, Victoria Island, Lagos</p>
          </div>
        </li>
      </ul>
    </div>

    <form data-reveal data-reveal-delay="100" data-inquiry-type="contact" class="lg:col-span-3 border border-white/10 rounded-3xl p-8 bg-obsidian-card" novalidate>
      <div class="grid sm:grid-cols-2 gap-5 mb-5">
        <label class="block text-sm">
          <span class="block font-medium mb-1.5">Full Name</span>
          <input type="text" name="name" required value="<?= $user ? h($user['name']) : '' ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
        </label>
        <label class="block text-sm">
          <span class="block font-medium mb-1.5">Email</span>
          <input type="email" name="email" required value="<?= $user ? h($user['email']) : '' ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
        </label>
      </div>
      <label class="block text-sm mb-5">
        <span class="block font-medium mb-1.5">Phone <span class="text-slate">(optional)</span></span>
        <input type="tel" name="phone" placeholder="+234" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
      </label>
      <label class="block text-sm mb-6">
        <span class="block font-medium mb-1.5">Message</span>
        <textarea name="message" rows="5" required minlength="10" maxlength="2000" placeholder="How can we help?" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none resize-none"></textarea>
      </label>

      <p data-form-error class="hidden text-sm text-red-400 mb-4" role="alert"></p>
      <p data-form-success class="hidden text-sm text-gold mb-4" role="status"></p>

      <button type="submit" class="w-full bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full py-3.5 transition-colors">Send Message</button>
    </form>
  </div>
</section>

<?php
$pageScripts = ['assets/js/inquiry-form.js'];
require __DIR__ . '/includes/footer.php';
?>
