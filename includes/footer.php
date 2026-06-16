</main>

<footer class="relative mt-24 border-t border-white/10 bg-obsidian-soft bg-[#0E0E10]">
  <?= render_skyline('absolute -top-[27px] left-0 w-full') ?>
  <div class="max-w-7xl mx-auto px-6 lg:px-10 py-16 grid grid-cols-1 md:grid-cols-4 gap-10">
    <div class="md:col-span-2">
      <span class="font-display text-xl">Atlantis <span class="text-gold">Homes</span></span>
      <p class="mt-4 text-slate text-sm leading-relaxed max-w-sm">Premium construction and smart real estate investment, building landmark residences across Lagos and Abuja since 2014.</p>
    </div>
    <div>
      <h3 class="font-display text-gold mb-4">Explore</h3>
      <ul class="space-y-3 text-sm text-slate">
        <li><a href="/portfolio.php" class="hover:text-white transition-colors">Portfolio</a></li>
        <li><a href="/investor-hub.php" class="hover:text-white transition-colors">Investor Hub</a></li>
        <li><a href="/reviews.php" class="hover:text-white transition-colors">Reviews</a></li>
        <li><a href="/login.php" class="hover:text-white transition-colors">Investor Login</a></li>
      </ul>
    </div>
    <div>
      <h3 class="font-display text-gold mb-4">Head Office</h3>
      <ul class="space-y-3 text-sm text-slate">
        <li>16B Kofo Abayomi Street, Victoria Island, Lagos</li>
        <li>+234 1 700 5582</li>
        <li>invest@atlantishomes.ng</li>
      </ul>
    </div>
  </div>
  <div class="border-t border-white/10 py-6 text-center text-xs text-slate/70">
    &copy; <?= date('Y') ?> Atlantis Homes Ltd. All rights reserved.
  </div>
</footer>

<script src="/assets/js/main.js"></script>
<?php foreach ($pageScripts ?? [] as $src): ?>
<script src="<?= h($src) ?>"></script>
<?php endforeach; ?>
</body>
</html>
