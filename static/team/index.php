<?php
// Staff directory: lists every published card (folders publish.php created).
// Lives at /team/; publish.php refuses "team" as a card name because this folder isn't a card.
// Signed-in admins only. Delete the next two lines to make the directory public.
require dirname(__DIR__) . '/auth.php';
auth_require_page();
$root = dirname(__DIR__);
$e = function ($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
};

$people = [];
foreach (glob("$root/*/.card") ?: [] as $marker) {
    $dir = dirname($marker);
    $slug = basename($dir);
    $data = json_decode(@file_get_contents("$dir/card.json") ?: 'null', true) ?: [];
    $info = $data['genInfo'] ?? [];
    $name = trim(($info['fname'] ?? '') . ' ' . ($info['lname'] ?? ''));
    $photo = $data['images']['photo'] ?? null;
    if ($photo && !preg_match('/^[a-z]+\.\w+$/', $photo)) $photo = null;
    $people[] = [
        'slug' => $slug,
        'name' => $name !== '' ? $name : $slug,
        'title' => $info['title'] ?? '',
        'arName' => $info['arName'] ?? '',
        'photo' => $photo,
    ];
}
usort($people, function ($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});

// Company name for the heading, taken from the editor's built page title.
$company = 'Staff';
$index = @file_get_contents("$root/index.html");
if ($index && preg_match('~<title>(.*?) Business Cards</title>~', $index, $m)) $company = html_entity_decode($m[1]);
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= $e($company) ?> · Staff directory</title>
<link rel="icon" href="/favicon.ico">
<style>
  :root { --accent: #204080; --ink: #14213d; --muted: #56627a; --line: rgba(20, 33, 61, .12); --bg: #eef1f6; }
  * { box-sizing: border-box; }
  body { margin: 0; background: var(--bg); color: var(--ink);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
  header { background: #fff; border-bottom: 1px solid var(--line); }
  .wrap { max-width: 56rem; margin: 0 auto; padding: 0 1rem; }
  .top { display: flex; align-items: center; gap: 1rem; padding: 1.25rem 0; }
  .top img { height: 3rem; width: auto; }
  h1 { margin: 0; font-size: 1.375rem; }
  .count { margin: 0; color: var(--muted); font-size: .875rem; }
  .search { display: block; width: 100%; margin: 1.5rem 0 1rem; padding: .875rem 1rem; font: inherit;
    border: 1px solid #b8c2d6; border-radius: .75rem; background: #fff; color: var(--ink); }
  .search:focus-visible, a:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
  ul { list-style: none; margin: 0 0 3rem; padding: 0; display: grid; gap: .75rem;
    grid-template-columns: repeat(auto-fill, minmax(16rem, 1fr)); }
  a.person { display: flex; align-items: center; gap: .875rem; padding: .875rem; background: #fff;
    border-radius: 1rem; color: inherit; text-decoration: none; box-shadow: 0 1px 0 var(--line); }
  a.person:hover { box-shadow: 0 0 0 2px var(--accent); }
  .avatar { width: 3.25rem; height: 3.25rem; border-radius: 50%; flex-shrink: 0; object-fit: cover;
    display: grid; place-items: center; background: var(--accent); color: #fff; font-weight: 700; }
  .who { min-width: 0; }
  .name { font-weight: 700; }
  .title, .ar { font-size: .875rem; color: var(--muted); }
  .title { color: var(--accent); font-weight: 600; }
  .empty { color: var(--muted); padding: 2rem 0; text-align: center; }
</style>
</head>
<body>
<header>
  <div class="wrap top">
    <img src="/brand-logo.png" alt="<?= $e($company) ?>" onerror="this.remove()">
    <div>
      <h1>Staff directory</h1>
      <p class="count"><?= count($people) ?> <?= count($people) === 1 ? 'person' : 'people' ?></p>
    </div>
  </div>
</header>
<main class="wrap">
  <label for="q" style="position:absolute;left:-9999px">Search staff</label>
  <input id="q" class="search" type="search" placeholder="Search by name or job title" autocomplete="off">
  <?php if (!$people): ?>
    <p class="empty">No cards published yet.</p>
  <?php else: ?>
  <ul id="list">
    <?php foreach ($people as $p):
        $initials = strtoupper(implode('', array_map(function ($w) { return mb_substr($w, 0, 1); }, array_slice(preg_split('/\s+/', $p['name']), 0, 2)))); ?>
    <li data-search="<?= $e(mb_strtolower($p['name'] . ' ' . $p['title'] . ' ' . $p['arName'])) ?>">
      <a class="person" href="/<?= $e($p['slug']) ?>/">
        <?php if ($p['photo']): ?>
          <img class="avatar" src="/<?= $e($p['slug']) ?>/<?= $e($p['photo']) ?>" alt="" loading="lazy">
        <?php else: ?>
          <span class="avatar" aria-hidden="true"><?= $e($initials) ?></span>
        <?php endif; ?>
        <span class="who">
          <span class="name"><?= $e($p['name']) ?></span><br>
          <?php if ($p['title']): ?><span class="title"><?= $e($p['title']) ?></span><br><?php endif; ?>
          <?php if ($p['arName']): ?><span class="ar" dir="rtl" lang="ar"><?= $e($p['arName']) ?></span><?php endif; ?>
        </span>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>
  <p class="empty" id="none" hidden>No one matches that search.</p>
  <?php endif; ?>
</main>
<script>
  const q = document.getElementById('q'), items = [...document.querySelectorAll('#list li')], none = document.getElementById('none');
  q && q.addEventListener('input', () => {
    const t = q.value.trim().toLowerCase();
    let shown = 0;
    items.forEach(li => { const m = li.dataset.search.includes(t); li.hidden = !m; shown += m; });
    if (none) none.hidden = shown > 0;
  });
</script>
</body>
</html>
