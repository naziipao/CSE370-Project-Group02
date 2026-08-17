// File: frontend/JS/dashboard.js
document.addEventListener('DOMContentLoaded', () => {
  
  // DOM Elements targeting the IDs in dashboard.html
  const userNameDisplay = document.getElementById('userName');
  const ecoBalanceDisplay = document.getElementById('headerPoints');
  const totalRecycledDisplay = document.getElementById('valRecycled'); 
  const rankDisplay = document.getElementById('valRank');             
  const vouchersDisplay = document.getElementById('valVouchers');     

  async function loadDashboard() {
    try {
      const response = await fetch('/api/dashboard'); 
      const data = await response.json();

      if (!response.ok || !data.success) {
        console.error('Failed to load dashboard:', data.message);
        
        // If not logged in, redirect to login page
        if (response.status === 401) {
          window.location.href = '/HTML/login.html'; 
        }
        return;
      }

      const user = data.userData;

      // 1. Update Spendable Eco Balance & Name
      if (ecoBalanceDisplay) ecoBalanceDisplay.textContent = `${user.ecoBalance} Points`;
      if (userNameDisplay) userNameDisplay.textContent = user.name;

      // 2. Update the Dashboard Stat Cards
      // We only insert the numbers here, because the HTML handles the "kg", "#", and "Vouchers" units.
      if (totalRecycledDisplay) totalRecycledDisplay.textContent = user.totalRecycledKg;
      if (rankDisplay) rankDisplay.textContent = user.rank;
      if (vouchersDisplay) vouchersDisplay.textContent = user.availableVouchers;

    } catch (err) {
      console.error('Fetch error:', err);
    }
  }

  loadDashboard();
});