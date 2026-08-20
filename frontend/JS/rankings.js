// File: frontend/JS/rankings.js
document.addEventListener('DOMContentLoaded', () => {

  // ---- DOM elements ----
  const podiumEl    = document.getElementById('podium');
  const tableBody   = document.getElementById('rankTableBody');
  const myRankEl    = document.getElementById('myRank');
  const myNameEl    = document.getElementById('myInstituteName');
  const myMetaEl    = document.getElementById('myInstituteMeta');
  const myPointsEl  = document.getElementById('myPoints');
  const totalEl     = document.getElementById('totalInstitutes');
  const updatedEl   = document.getElementById('lastUpdated');
  const refreshBtn  = document.getElementById('refreshBtn');

  const REFRESH_MS = 20000; // re-ask the server every 20 seconds

  // ---- small helpers ----
  const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => (
    { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
  ));

  const formatNumber = (n) => Number(n || 0).toLocaleString();

  const medalFor = (rank) => (rank === 1 ? '🥇' : rank === 2 ? '🥈' : rank === 3 ? '🥉' : '🏅');

  // ---- main loader ----
  async function loadRankings() {
    try {
      refreshBtn.disabled = true;

      const response = await fetch('/api/rankings');
      const data = await response.json();

      if (!response.ok || !data.success) {
        if (response.status === 401) {
          window.location.href = '/';
          return;
        }
        showError(data.message || 'Could not load rankings.');
        return;
      }

      const myEiin = data.myInstitute ? data.myInstitute.eiin : null;

      renderMyInstitute(data.myInstitute);
      renderPodium(data.rankings.slice(0, 3), myEiin);
      renderTable(data.rankings, myEiin);

      totalEl.textContent = `${data.totalInstitutes} institutes ranked`;
      updatedEl.textContent = 'Updated ' + new Date().toLocaleTimeString();

    } catch (err) {
      console.error('Rankings fetch error:', err);
      showError('Cannot reach the server. Check that it is running.');
    } finally {
      refreshBtn.disabled = false;
    }
  }

  // ---- renderers ----
  function renderMyInstitute(mine) {
    if (!mine) {
      myRankEl.textContent = '—';
      myNameEl.textContent = 'No institute linked';
      myMetaEl.textContent = 'Only student accounts are counted towards an institute.';
      myPointsEl.textContent = '—';
      return;
    }

    myRankEl.textContent = '#' + mine.rank;
    myNameEl.textContent = mine.name;
    myMetaEl.textContent = `EIIN ${mine.eiin} · ${formatNumber(mine.members)} members recycling`;
    myPointsEl.textContent = formatNumber(mine.points);
  }

  function renderPodium(top, myEiin) {
    if (top.length === 0) {
      podiumEl.innerHTML = '';
      return;
    }

    // Display order: 2nd on the left, 1st in the middle, 3rd on the right.
    const order = [top[1], top[0], top[2]].filter(Boolean);

    podiumEl.innerHTML = order.map((item) => `
      <div class="podium-card ${item === top[0] ? 'is-first' : ''} ${item.eiin === myEiin ? 'is-mine' : ''}">
        <span class="podium-medal">${medalFor(item.rank)}</span>
        <h3 class="podium-name">${escapeHtml(item.name)}</h3>
        <p class="podium-points">${formatNumber(item.points)} <span>pts</span></p>
        <span class="podium-members">${formatNumber(item.members)} members</span>
      </div>
    `).join('');
  }

  function renderTable(rankings, myEiin) {
    if (rankings.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="4" class="table-msg">No institutes registered yet.</td></tr>';
      return;
    }

    tableBody.innerHTML = rankings.map((item) => `
      <tr class="${item.eiin === myEiin ? 'is-mine' : ''}">
        <td><span class="rank-number">${item.rank}</span></td>
        <td>
          ${escapeHtml(item.name)}
          ${item.eiin === myEiin ? '<span class="you-tag">You</span>' : ''}
        </td>
        <td class="col-right">${formatNumber(item.members)}</td>
        <td class="col-right points-cell">${formatNumber(item.points)}</td>
      </tr>
    `).join('');
  }

  function showError(message) {
    tableBody.innerHTML = `<tr><td colspan="4" class="table-msg error">${escapeHtml(message)}</td></tr>`;
    updatedEl.textContent = 'Update failed';
  }

  // ---- events + auto refresh ----
  refreshBtn.addEventListener('click', loadRankings);

  // Only poll while the tab is actually being looked at.
  setInterval(() => {
    if (!document.hidden) loadRankings();
  }, REFRESH_MS);

  loadRankings();
});
