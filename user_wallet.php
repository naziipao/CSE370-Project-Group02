<?php
/* ============================================================
   user_wallet.php
   Displays the user's large eco-balance and purchased vouchers
   ============================================================ */

require_once "config/auth.php";
require_once "config/DBconnect.php";

$user_id = $_SESSION['user_id'] ?? null;

// 1. Fetch User's Eco Balance
$ecoBalance = 0;
if ($user_id) {
    try {
        $stmt = $pdo->prepare("SELECT current_points FROM wallet WHERE User_id = ?");
        $stmt->execute([$user_id]);
        $wallet = $stmt->fetch();
        if ($wallet) {
            $ecoBalance = $wallet['current_points'];
        }
    } catch (Exception $e) {
        // Silently handle or log error if needed
    }
}

// 2. Fetch Purchased Vouchers History
$purchasedVouchers = [];
if ($user_id) {
    try {
        $query = "
            SELECT 
                r.reward_name, 
                r.required_points, 
                r.expiry_date, 
                p.company_name,
                uv.purchase_date
            FROM voucher_transaction_history uv
            JOIN reward r ON uv.reward_id = r.reward_id
            LEFT JOIN partner_company p ON r.company_id = p.company_id
            WHERE uv.user_id = ?
            ORDER BY uv.purchase_date DESC
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        $purchasedVouchers = $stmt->fetchAll();
    } catch (Exception $e) {
        // Silently handle or log error if needed
    }
}

$totalPurchased = count($purchasedVouchers);

include 'header.php';
?>

<!-- Link to the specific wallet CSS -->
<link rel="stylesheet" href="css/user_wallet.css">

<!-- LARGE ECO BALANCE WIDGET -->
<section class="wallet-hero-widget glow-card">
    <div class="hero-icon-wrapper">
        <i class="fa-solid fa-leaf"></i>
    </div>
    <div class="hero-balance-text">My Eco Balance</div>
    <div class="hero-balance-amount"><?= htmlspecialchars($ecoBalance) ?> <span class="pts-label">pts</span></div>
    <p class="hero-subtitle">Keep recycling to grow your balance!</p>
</section>

<!-- PURCHASED VOUCHERS SECTION -->
<section class="wallet-vouchers-section glow-card">
    <div class="section-header">
        <h2 class="section-title">
            <i class="fa-solid fa-box-open"></i> Purchased Vouchers: (<?= $totalPurchased ?>)
        </h2>
        <p class="section-subtitle">A history of all the rewards you have claimed.</p>
    </div>
    
    <div class="wallet-voucher-list">
        <?php if (empty($purchasedVouchers)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-ghost"></i>
                <p>You haven't purchased any vouchers yet.</p>
                <a href="reward.php" class="btn-go-shop">Go to Reward Store</a>
            </div>
        <?php else: ?>
            <?php foreach ($purchasedVouchers as $v): ?>
                <?php
                    // Determine if the voucher is already expired based on today's date
                    $isExpired = false;
                    if (!empty($v['expiry_date']) && strtotime($v['expiry_date']) < strtotime('today')) {
                        $isExpired = true;
                    }
                ?>
                <div class="voucher-store-item <?= $isExpired ? 'expired-item' : '' ?>">
                    <div class="v-main-info">
                        <span class="v-icon"><i class="fa-solid fa-ticket"></i></span>
                        <div>
                            <h3 class="v-title"><?= htmlspecialchars($v['reward_name']) ?></h3>
                            <span class="v-company"><?= htmlspecialchars($v['company_name'] ?: 'Partner') ?></span>
                        </div>
                    </div>
                    
                    <div class="v-meta">
                        <!-- COST USED -->
                        <div class="v-detail-block right-align">
                            <span class="v-label">Cost</span>
                            <span class="v-points-cost"><?= htmlspecialchars($v['required_points']) ?> pts</span>
                        </div>

                        <!-- EXPIRY DATE -->
                        <div class="v-detail-block right-align">
                            <span class="v-label">Expires</span>
                            <span class="v-value <?= $isExpired ? 'text-danger' : '' ?>">
                                <?= !empty($v['expiry_date']) ? htmlspecialchars(date('d M Y', strtotime($v['expiry_date']))) : 'No Expiry' ?>
                            </span>
                        </div>
                        
                        <!-- STATUS BADGE -->
                        <div class="v-status-badge <?= $isExpired ? 'badge-expired' : 'badge-active' ?>">
                            <?= $isExpired ? 'Expired' : 'Active' ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>