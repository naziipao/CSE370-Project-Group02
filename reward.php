<?php
/* ============================================================
   reward.php
   ============================================================ */

require_once "config/auth.php";
require_once "config/DBconnect.php";

$user_id = $_SESSION['user_id'];
$message = '';
$msg_type = '';

// Handle Purchase Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voucher_id'])) {
    $voucher_id = $_POST['voucher_id'];

    try {
        $stmt = $pdo->prepare("SELECT required_points FROM reward WHERE reward_id = ?");
        $stmt->execute([$voucher_id]);
        $voucher = $stmt->fetch();

        if (!$voucher) {
            $message = "Voucher not found.";
            $msg_type = "error";
        } else {
            $requiredPoints = $voucher['required_points'];

            $stmt = $pdo->prepare("SELECT current_points FROM wallet WHERE User_id = ?");
            $stmt->execute([$user_id]);
            $wallet = $stmt->fetch();
            $currentPoints = $wallet ? $wallet['current_points'] : 0;

            if ($currentPoints < $requiredPoints) {
                $message = "Insufficient points. Required: $requiredPoints, You have: $currentPoints.";
                $msg_type = "error";
            } else {
                $stmt = $pdo->prepare("UPDATE wallet SET current_points = current_points - ?, voucher = voucher + 1 WHERE User_id = ?");
                $stmt->execute([$requiredPoints, $user_id]);
                
                $message = "Voucher obtained successfully!";
                $msg_type = "success";
            }
        }
    } catch (Exception $e) {
        $message = "Database Error: " . $e->getMessage();
        $msg_type = "error";
    }
}

// Fetch User Wallet Points
$userPoints = 0;
try {
    $stmt = $pdo->prepare("SELECT current_points FROM wallet WHERE User_id = ?");
    $stmt->execute([$user_id]);
    $wallet = $stmt->fetch();
    if ($wallet) {
        $userPoints = $wallet['current_points'];
    }
} catch (Exception $e) {}

// Fetch Vouchers List
$vouchers = [];
try {
    $vouchersQuery = "
        SELECT 
            r.reward_id AS voucher_id,
            r.reward_name AS voucher_name,
            r.required_points,
            r.expiry_date,
            p.company_name
        FROM reward r
        LEFT JOIN partner_company p ON r.company_id = p.company_id
        ORDER BY r.reward_id ASC
    ";
    $stmt = $pdo->query($vouchersQuery);
    $vouchers = $stmt->fetchAll();
} catch (Exception $e) {}

include 'header.php';
?>

<?php if (!empty($message)): ?>
    <div style="padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; 
                background-color: <?= $msg_type === 'success' ? 'rgba(82, 183, 136, 0.2)' : 'rgba(239, 68, 68, 0.2)' ?>; 
                color: <?= $msg_type === 'success' ? '#52b788' : '#ef4444' ?>; 
                border: 1px solid <?= $msg_type === 'success' ? '#52b788' : '#ef4444' ?>;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<header class="top-header glow-card profile-header">
    <!-- NEW TITLE AREA -->
    <div class="header-title-area">
        <h1>🎁 Rewards Store</h1>
        <p>Exchange your eco-points for exclusive benefits from our <b>Partner Companies</b></p>
    </div>

    <!-- EXISTING WALLET BADGE -->
    <div class="wallet-badge">
        <span class="badge-icon">🌿</span>
        <div class="badge-info">
            <span class="badge-label">Eco Balance</span>
            <span class="badge-points"><?= htmlspecialchars($userPoints) ?> Points</span>
        </div>
    </div>
</header>

<section class="vouchers-section glow-card">
    <div class="section-header">
        <h2 class="section-title"><i class="fa-solid fa-tags"></i> Voucher Store</h2>
        <p class="section-subtitle">Redeem your earned points for exclusive partner discounts.</p>
    </div>
    
    <div class="voucher-list">
        <?php if (empty($vouchers)): ?>
            <p style="color: var(--text-muted); padding: 20px;">No vouchers available at the moment.</p>
        <?php else: ?>
            <?php foreach ($vouchers as $v): ?>
                <?php 
                    // Check if user has enough points
                    $canAfford = $userPoints >= $v['required_points']; 
                ?>
                <div class="voucher-store-item">
                    <div class="v-main-info">
                        <span class="v-icon"><i class="fa-solid fa-ticket"></i></span>
                        <div>
                            <h3 class="v-title"><?= htmlspecialchars($v['voucher_name']) ?></h3>
                            <span class="v-company"><?= htmlspecialchars($v['company_name'] ?: 'Partner') ?></span>
                        </div>
                    </div>
                    
                    <div class="v-meta">
                        <!-- EXPIRY DATE BLOCK -->
                        <div class="v-detail-block right-align">
                            <span class="v-label">Expires</span>
                            <span class="v-value">
                                <?= !empty($v['expiry_date']) ? htmlspecialchars(date('d M Y', strtotime($v['expiry_date']))) : 'No Expiry' ?>
                            </span>
                        </div>

                        <!-- COST BLOCK -->
                        <div class="v-detail-block right-align">
                            <span class="v-label">Cost</span>
                            <span class="v-points-cost"><?= htmlspecialchars($v['required_points']) ?> pts</span>
                        </div>
                        
                        <!-- OBTAIN BUTTON FORM -->
                        <form method="POST" action="reward.php" 
                              <?= $canAfford ? 'onsubmit="return confirm(\'Obtain this voucher for ' . htmlspecialchars($v['required_points']) . ' points?\');"' : 'onsubmit="return false;"' ?> 
                              style="margin: 0;">
                            <input type="hidden" name="voucher_id" value="<?= htmlspecialchars($v['voucher_id']) ?>">
                            
                            <button type="submit" class="btn-purchase" 
                                <?= !$canAfford ? 'disabled style="cursor: not-allowed; opacity: 0.4; filter: grayscale(100%);"' : '' ?> 
                                title="<?= !$canAfford ? 'Insufficient Points' : 'Obtain Voucher' ?>">
                                Obtain
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>