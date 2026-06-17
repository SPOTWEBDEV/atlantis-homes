<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin(base_url('login.php'));

$pageTitle = 'Review Moderation';
$activeAdminNav = 'reviews';

$pending = get_db()->query("
    SELECT r.*, u.name AS user_name FROM reviews r LEFT JOIN users u ON u.id = r.user_id
    WHERE r.status = 'pending' ORDER BY r.created_at ASC
")->fetchAll();

require __DIR__ . '/../includes/admin-header.php';
?>

<p class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Control Center</p>
<h1 class="font-display text-3xl sm:text-4xl mb-2">Review Moderation</h1>
<p class="text-slate text-sm mb-10"><?= count($pending) ?> review<?= count($pending) === 1 ? '' : 's' ?> awaiting approval</p>

<div class="border border-white/10 rounded-2xl overflow-hidden bg-obsidian-card">
  <table class="w-full text-sm" id="moderation-table">
    <thead class="bg-white/5 text-slate text-xs uppercase tracking-wider">
      <tr>
        <th class="text-left px-5 py-3.5">Author</th>
        <th class="text-left px-5 py-3.5">Review</th>
        <th class="text-left px-5 py-3.5">Rating</th>
        <th class="text-left px-5 py-3.5">Submitted</th>
        <th class="text-right px-5 py-3.5">Action</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-white/5">
      <?php foreach ($pending as $r): ?>
        <?php $name = $r['user_id'] ? $r['user_name'] : $r['guest_name']; ?>
        <tr id="review-row-<?= (int) $r['id'] ?>" data-id="<?= (int) $r['id'] ?>">
          <td class="px-5 py-4">
            <p class="font-medium"><?= h($name) ?></p>
            <?php if ($r['verified_owner']): ?><span class="text-[10px] uppercase tracking-wide font-semibold bg-gold/15 text-gold border border-gold/30 rounded-full px-2 py-0.5">Verified Owner</span><?php endif; ?>
          </td>
          <td class="px-5 py-4 max-w-md">
            <p class="font-medium"><?= h($r['title']) ?></p>
            <p class="text-slate text-xs mt-1 line-clamp-2"><?= h($r['body']) ?></p>
          </td>
          <td class="px-5 py-4 text-gold whitespace-nowrap"><?= str_repeat('★', (int) $r['rating']) ?></td>
          <td class="px-5 py-4 text-slate whitespace-nowrap"><?= h(time_ago($r['created_at'])) ?></td>
          <td class="px-5 py-4 text-right whitespace-nowrap">
            <button type="button" class="approve-btn text-xs font-semibold bg-gold/15 text-gold hover:bg-gold hover:text-obsidian rounded-full px-3.5 py-1.5 transition-colors mr-2">Approve</button>
            <button type="button" class="reject-btn text-xs font-semibold bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white rounded-full px-3.5 py-1.5 transition-colors">Reject</button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p id="moderation-empty" class="<?= $pending ? 'hidden' : '' ?> text-center text-slate py-12">Nothing waiting for review right now.</p>
</div>

<?php
$pageScripts = ['assets/js/admin.js'];
require __DIR__ . '/../includes/admin-footer.php';
?>
