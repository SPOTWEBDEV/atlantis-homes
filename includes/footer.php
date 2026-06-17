</main>

<footer class="relative mt-24 border-t border-white/10 bg-obsidian-soft bg-[#0E0E10]">
  <?= render_skyline('absolute -top-[27px] left-0 w-full') ?>
  <div class="max-w-7xl mx-auto px-6 lg:px-10 py-16 grid grid-cols-1 md:grid-cols-4 gap-10">
    <div class="md:col-span-2">
      <span class="font-display text-xl">Atlantis <span class="text-gold">Homes</span></span>
      <p class="mt-4 text-slate text-sm leading-relaxed max-w-sm">Premium construction and smart real estate investment, building landmark residences across Lagos and Abuja since 2014.</p>
      <a href="https://www.instagram.com/atlantishomes.ng" target="_blank" rel="noopener" class="inline-flex items-center gap-2 mt-5 text-sm text-slate hover:text-gold transition-colors">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
        @atlantishomes.ng
      </a>
    </div>
    <div>
      <h3 class="font-display text-gold mb-4">Explore</h3>
      <ul class="space-y-3 text-sm text-slate">
        <li><a href="<?= base_url('portfolio.php') ?>" class="hover:text-white transition-colors">Portfolio</a></li>
        <li><a href="<?= base_url('investor-hub.php') ?>" class="hover:text-white transition-colors">Investor Hub</a></li>
        <li><a href="<?= base_url('estimate.php') ?>" class="hover:text-white transition-colors">Build Estimate</a></li>
        <li><a href="<?= base_url('reviews.php') ?>" class="hover:text-white transition-colors">Reviews</a></li>
        <li><a href="<?= base_url('book-a-session.php') ?>" class="hover:text-white transition-colors">Book a Session</a></li>
        <li><a href="<?= base_url('login.php') ?>" class="hover:text-white transition-colors">Investor Login</a></li>
      </ul>
    </div>
    <div>
      <h3 class="font-display text-gold mb-4">Head Office</h3>
      <ul class="space-y-3 text-sm text-slate">
        <li>16B Kofo Abayomi Street, Victoria Island, Lagos</li>
        <li><a href="tel:+23417005582" class="hover:text-white transition-colors">+234 1 700 5582</a></li>
        <li><a href="mailto:invest@atlantishomes.ng" class="hover:text-white transition-colors">invest@atlantishomes.ng</a></li>
        <li><a href="<?= base_url('contact.php') ?>" class="text-gold hover:text-gold-light transition-colors">Contact us &rarr;</a></li>
      </ul>
    </div>
  </div>
  <div class="border-t border-white/10 py-6 text-center text-xs text-slate/70">
    &copy; <?= date('Y') ?> Atlantis Homes Ltd. All rights reserved.
  </div>
</footer>

<script src="<?= base_url('assets/js/main.js') ?>"></script>
<?php foreach ($pageScripts ?? [] as $src): ?>
<script src="<?= base_url($src) ?>"></script>
<?php endforeach; ?>
</body>
</html>
