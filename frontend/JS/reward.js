// File: frontend/JS/reward.js
document.addEventListener('DOMContentLoaded', () => {
  const storeList = document.getElementById('storeList');
  const headerPoints = document.getElementById('headerPoints');

  // --- Reverted, Stable Custom Modal Helper ---
// --- Brutally Simple Custom Modal Helper ---
  function showCustomModal(title, message, isConfirm = false) {
    return new Promise((resolve) => {
      const modal = document.getElementById('customModal');
      const btnConfirm = document.getElementById('modalBtnConfirm');
      const btnCancel = document.getElementById('modalBtnCancel');

      // Set text
      document.getElementById('modalTitle').textContent = title;
      document.getElementById('modalMessage').textContent = message;

      // Show/Hide Cancel
      btnCancel.style.display = isConfirm ? 'inline-block' : 'none';
      
      // Show Modal
      modal.classList.add('active');

      // Direct assignment completely overwrites any broken/stuck listeners
      btnConfirm.onclick = () => {
        modal.classList.remove('active');
        resolve(true);
      };

      btnCancel.onclick = () => {
        modal.classList.remove('active');
        resolve(false);
      };
    });
  }

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
          <button onclick="obtainVoucher('${v.voucher_id}')" class="btn-purchase">
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
    const isConfirmed = await showCustomModal('Confirm Purchase', 'Are you sure you want to obtain this voucher?', true);
    if (!isConfirmed) return;

    try {
      const response = await fetch('/api/vouchers/purchase', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ voucherId })
      });

      const data = await response.json();

      if (!response.ok || !data.success) {
        await showCustomModal('Error', data.message || 'Failed to obtain voucher.');
        return;
      }

      await showCustomModal('Success!', data.message);

      if (headerPoints && data.newBalance !== undefined) {
        headerPoints.textContent = `${data.newBalance} Points`;
      }
    } catch (err) {
      console.error('Purchase error:', err);
      await showCustomModal('Error', 'Error processing request: ' + err.message);
    }
  };

  loadVouchers();
});