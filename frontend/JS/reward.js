// File: frontend/JS/reward.js
document.addEventListener('DOMContentLoaded', () => {
  const storeList = document.getElementById('storeList');
  const headerPoints = document.getElementById('headerPoints');

  async function loadVouchers() {
    try {
      storeList.innerHTML = '<p style="color: var(--text-muted); padding: 20px;">Loading rewards...</p>';

      const response = await fetch('/api/vouchers');
      const data = await response.json();

      if (!response.ok || !data.success) {
        storeList.innerHTML = `<p style="color: #ff6b6b; padding: 20px;">${data.message || 'Error loading rewards'}</p>`;
        return;
      }

      if (headerPoints && data.userPoints !== undefined) {
        headerPoints.textContent = `${data.userPoints} Points`;
      }

      if (!data.vouchers || data.vouchers.length === 0) {
        storeList.innerHTML = '<p style="color: var(--text-muted); padding: 20px;">No vouchers available.</p>';
        return;
      }

      storeList.innerHTML = data.vouchers.map(v => `
        <div class="voucher-card glow-card" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; margin-bottom: 12px; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px;">
          <div>
            <h3 style="margin:0 0 5px 0;">${v.voucher_name}</h3>
            <p style="margin:0; color: #a0aec0;">${v.company_name || 'Partner'} — <strong>${v.required_points}</strong> Points</p>
          </div>
          <button 
            onclick="obtainVoucher('${v.voucher_id}')" 
            style="padding: 8px 16px; background-color: #00ff88; color: #000; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            Obtain Voucher
          </button>
        </div>
      `).join('');

    } catch (err) {
      console.error('Fetch error:', err);
      storeList.innerHTML = `<p style="color: #ff6b6b; padding: 20px;">Error: ${err.message}</p>`;
    }
  }

  window.obtainVoucher = async function(voucherId) {
    if (!confirm('Are you sure you want to obtain this voucher?')) return;

    try {
      // Pointing to your existing /purchase route
      const response = await fetch('/api/vouchers/purchase', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ voucherId })
      });

      const data = await response.json();

      if (!response.ok || !data.success) {
        alert(data.message || 'Failed to obtain voucher.');
        return;
      }

      alert(data.message);

      // Update the header points immediately with the newly calculated balance
      if (headerPoints && data.newBalance !== undefined) {
        headerPoints.textContent = `${data.newBalance} Points`;
      }
    } catch (err) {
      console.error('Purchase error:', err);
      alert('Error processing request: ' + err.message);
    }
  };

  loadVouchers();
});