  </main>
</div>

<script src="<?= base_url('assets/js/main.js') ?>"></script>
<script>
  (function () {
    const btn = document.getElementById('admin-mobile-menu-btn');
    const menu = document.getElementById('admin-mobile-menu');
    if (!btn || !menu) return;
    btn.addEventListener('click', () => {
      const isOpen = !menu.classList.contains('hidden');
      menu.classList.toggle('hidden');
      btn.setAttribute('aria-expanded', String(!isOpen));
    });
  })();
</script>
<?php foreach ($pageScripts ?? [] as $src): ?>
<script src="<?= base_url($src) ?>"></script>
<?php endforeach; ?>
</body>
</html>
