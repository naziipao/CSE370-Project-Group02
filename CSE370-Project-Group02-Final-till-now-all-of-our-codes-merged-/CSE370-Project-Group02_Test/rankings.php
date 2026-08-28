<?php
/* ============================================================
   rankings.php   —   FRONT-END PAGE

   This is the page the user opens from the sidebar.
   It does NOT query the database itself. It just draws an empty
   shell, then its JavaScript keeps asking rankings_data.php for
   fresh data and redraws the board. That polling is what makes
   the leaderboard update on its own when points change.
   ============================================================ */

require_once "config/auth.php";
require_once "config/DBconnect.php";

require_login();                 // must be logged in to view

include 'header.php';            // sidebar + <head> (loads dashboard.css + rankings.css)
?>

<!-- PAGE HEADER -->
<header class="top-header glow-card">
  <div class="welcome-text">
    <h1>🏆 Institute Rankings</h1>
    <p>Live leaderboard — institutes ranked by total points earned.</p>
  </div>

  <!-- "Live" indicator; the JS updates the time inside it -->
  <div class="live-badge" id="liveBadge">
    <span class="live-dot"></span>
    <div class="badge-info">
      <span class="badge-label">Live</span>
      <span class="badge-points" id="updatedAt">connecting…</span>
    </div>
  </div>
</header>

<!-- TOP 3 PODIUM (filled by JavaScript) -->
<section class="podium glow-card" id="podium">
  <p class="loading-text">Loading top institutes…</p>
</section>

<!-- FULL RANKED LIST (filled by JavaScript) -->
<section class="ranking-section glow-card">
  <div class="section-header">
    <h2 class="section-title"><i class="fa-solid fa-ranking-star"></i> Full Standings</h2>
    <p class="section-subtitle">Your own institute is highlighted in green.</p>
  </div>

  <div class="ranking-list" id="rankingList">
    <p class="loading-text">Loading standings…</p>
  </div>
</section>


<script>
/* ============================================================
   Front-end logic: ask the endpoint, draw the board, repeat.
   Plain JavaScript (fetch) — no libraries.
   ============================================================ */

const POLL_MS = 5000;   // how often to refresh (5s). Raise for less DB load.

const podiumEl  = document.getElementById('podium');
const listEl    = document.getElementById('rankingList');
const updatedEl = document.getElementById('updatedAt');
const liveBadge = document.getElementById('liveBadge');

const medals = { 1: '🥇', 2: '🥈', 3: '🥉' };

// Turn 12500 into "12,500" so big numbers are readable.
function commas(n) {
  return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// Ask the back-end endpoint for the latest ranked list.
async function loadRankings() {
  try {
    const res  = await fetch('rankings_data.php', { cache: 'no-store' });
    const data = await res.json();

    if (!data.ok) throw new Error(data.error || 'bad response');

    drawPodium(data.institutes.slice(0, 3));
    drawList(data.institutes);

    updatedEl.textContent = 'updated ' + data.updated_at;
    liveBadge.classList.remove('offline');
  } catch (err) {
    // Keep whatever is already on screen; just flag that we lost contact.
    updatedEl.textContent = 'reconnecting…';
    liveBadge.classList.add('offline');
  }
}

// Build the top-3 podium. Order on screen: 2nd, 1st, 3rd (1st raised in the middle).
function drawPodium(top3) {
  const order = [top3[1], top3[0], top3[2]];   // 2nd, 1st, 3rd
  podiumEl.innerHTML = '';

  order.forEach(inst => {
    if (!inst) return;                          // fewer than 3 institutes? skip gaps

    const card = document.createElement('div');
    card.className = 'podium-card place-' + inst.rank;
    if (inst.is_me) card.classList.add('is-me');

    const medal = document.createElement('div');
    medal.className = 'podium-medal';
    medal.textContent = medals[inst.rank] || '';

    const name = document.createElement('div');
    name.className = 'podium-name';
    name.textContent = inst.name;               // textContent = safe, no HTML injection

    const pts = document.createElement('div');
    pts.className = 'podium-points';
    pts.textContent = commas(inst.points) + ' pts';

    card.append(medal, name, pts);
    podiumEl.appendChild(card);
  });
}

// Build the full ranked list.
function drawList(institutes) {
  listEl.innerHTML = '';

  institutes.forEach(inst => {
    const row = document.createElement('div');
    row.className = 'rank-row';
    if (inst.is_me)     row.classList.add('is-me');
    if (inst.rank <= 3) row.classList.add('top-three');

    const rank = document.createElement('span');
    rank.className = 'rank-num';
    rank.textContent = medals[inst.rank] || ('#' + inst.rank);

    const name = document.createElement('span');
    name.className = 'rank-name';
    name.textContent = inst.name + (inst.is_me ? '  (your institute)' : '');

    const pts = document.createElement('span');
    pts.className = 'rank-points';
    pts.textContent = commas(inst.points) + ' pts';

    row.append(rank, name, pts);
    listEl.appendChild(row);
  });
}

// Start now, then keep refreshing.
loadRankings();
let timer = setInterval(loadRankings, POLL_MS);

// Be efficient: pause polling when the tab is hidden, resume when it's back.
document.addEventListener('visibilitychange', () => {
  if (document.hidden) {
    clearInterval(timer);
  } else {
    loadRankings();
    timer = setInterval(loadRankings, POLL_MS);
  }
});
</script>

<?php include 'footer.php'; ?>
