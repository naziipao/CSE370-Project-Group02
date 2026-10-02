<?php
require_once "config/auth.php";
require_once "config/DBconnect.php";

// 1. Instantly secure the page (removes the need for manual session checks below)
require_login(); 
$user_id = $_SESSION['user_id'];
$message = '';
$msg_type = '';

// 2. Handle Success Message from Redirect
if (isset($_GET['success'])) {
    $message = "Voucher obtained successfully!";
    $msg_type = "success";
}

// 3. Process Voucher Purchase (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voucher_id'])) {
    $voucher_id = $_POST['voucher_id'];

    // Fetch the cost of the voucher
    $stmt = $pdo->prepare("SELECT required_points FROM reward WHERE reward_id = ?");
    $stmt->execute([$voucher_id]);
    $voucher = $stmt->fetch();

    // Fetch the user's current points
    $stmt = $pdo->prepare("SELECT current_points FROM wallet WHERE User_id = ?");
    $stmt->execute([$user_id]);
    $wallet = $stmt->fetch();
    $currentPoints = $wallet['current_points'] ?? 0;

    // Validate and Process
    if ($voucher && $currentPoints >= $voucher['required_points']) {
        $requiredPoints = $voucher['required_points'];
        
        // Deduct points and increment voucher count
        $pdo->prepare("UPDATE wallet SET current_points = current_points - ?, voucher = voucher + 1 WHERE User_id = ?")
            ->execute([$requiredPoints, $user_id]);
            
        // Save to history
        $pdo->prepare("INSERT INTO voucher_transaction_history (user_id, reward_id) VALUES (?, ?)")
            ->execute([$user_id, $voucher_id]);
            
        // Redirect to refresh data and clear form submission
        header("Location: reward.php?success=1");
        exit();
    } else {
        $message = "Insufficient points. Required: " . ($voucher['required_points'] ?? 'N/A') . ", You have: $currentPoints.";
        $msg_type = "error";
    }
}

// 4. Fetch Data for Page Load (Clean, straight-forward queries without nested try/catch blocks)
$userPoints = $pdo->query("SELECT current_points FROM wallet WHERE User_id = $user_id")->fetchColumn() ?: 0;

$vouchers = $pdo->query("
    SELECT r.reward_id AS voucher_id, r.reward_name AS voucher_name, r.required_points, r.expiry_date, p.company_name
    FROM reward r
    LEFT JOIN partner_company p ON r.company_id = p.company_id
    WHERE r.expiry_date >= CURDATE() OR r.expiry_date IS NULL
    ORDER BY r.reward_id ASC
")->fetchAll();

$partners = $pdo->query("
    SELECT p.company_name, p.contact_no AS company_phone, b.Branch_name, b.address AS branch_address, 
           GROUP_CONCAT(bt.Telephones SEPARATOR ', ') AS branch_telephones
    FROM partner_company p
    JOIN branch b ON p.company_id = b.C_ID
    LEFT JOIN branch_telephone bt ON b.Branch_id = bt.Branch_id
    GROUP BY b.Branch_id, p.company_id
    ORDER BY p.company_name ASC
")->fetchAll();

include 'header.php';
?>

<!-- NOTIFICATION BANNER -->
<?php if (!empty($message)): ?>
    <div style="padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; 
                background-color: <?= $msg_type === 'success' ? 'rgba(82, 183, 136, 0.2)' : 'rgba(239, 68, 68, 0.2)' ?>; 
                color: <?= $msg_type === 'success' ? '#52b788' : '#ef4444' ?>; 
                border: 1px solid <?= $msg_type === 'success' ? '#52b788' : '#ef4444' ?>;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<header class="top-header glow-card profile-header">
    <div class="header-title-area">
        <h1><span class="title-emoji">🎁</span> Vouchers Store</h1>
        <p>Exchange your eco-points for exclusive benefits from our <b>Partner Companies</b></p>
    </div>
    <div class="wallet-badge">
        <i class="fa-solid fa-leaf badge-icon"></i>
        <div class="badge-info">
            <span class="badge-label">Eco Balance</span>
            <span class="badge-points"><?= htmlspecialchars($userPoints) ?> Points</span>
        </div>
    </div>
</header>

<!-- VOUCHERS STORE SECTION -->
<section class="vouchers-section glow-card">
    <div class="section-header list-header-flex">
        <div>
            <h2 class="section-title"><i class="fa-solid fa-tags"></i> Voucher Store</h2>
            <p class="section-subtitle">Redeem your earned points for exclusive partner discounts.</p>
        </div>
        <div class="search-wrapper">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="voucherSearch" class="list-search-input" placeholder="Search vouchers...">
        </div>
    </div>
    
    <div class="voucher-list" id="voucherContainer">
        <?php if (empty($vouchers)): ?>
            <p style="color: var(--text-muted); padding: 20px;">No vouchers available at the moment.</p>
        <?php else: ?>
            <?php foreach ($vouchers as $v): ?>
                <?php $canAfford = $userPoints >= $v['required_points']; ?>
                
                <div class="voucher-store-item filterable-item">
                    <div class="v-main-info">
                        <span class="v-icon"><i class="fa-solid fa-ticket"></i></span>
                        <div>
                            <h3 class="v-title"><?= htmlspecialchars($v['voucher_name']) ?></h3>
                            <span class="v-company"><?= htmlspecialchars($v['company_name'] ?: 'Partner') ?></span>
                        </div>
                    </div>
                    
                    <div class="v-meta">
                        <div class="v-detail-block right-align">
                            <span class="v-label">Expires</span>
                            <span class="v-value"><?= !empty($v['expiry_date']) ? htmlspecialchars(date('d M Y', strtotime($v['expiry_date']))) : 'No Expiry' ?></span>
                        </div>

                        <div class="v-detail-block right-align">
                            <span class="v-label">Cost</span>
                            <span class="v-points-cost"><?= htmlspecialchars($v['required_points']) ?> pts</span>
                        </div>
                        
                        <form method="POST" action="reward.php" style="margin: 0;" <?= $canAfford ? 'onsubmit="return confirm(\'Obtain this voucher for ' . $v['required_points'] . ' points?\');"' : 'onsubmit="return false;"' ?>>
                            <input type="hidden" name="voucher_id" value="<?= htmlspecialchars($v['voucher_id']) ?>">
                            <button type="submit" class="btn-purchase" <?= !$canAfford ? 'disabled style="cursor: not-allowed; opacity: 0.4; filter: grayscale(100%);"' : '' ?> title="<?= !$canAfford ? 'Insufficient Points' : 'Obtain Voucher' ?>">
                                Obtain
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- GREEN PARTNERS SECTION -->
<section class="partners-section glow-card">
    <div class="section-header list-header-flex">
        <div>
            <h2 class="section-title"><i class="fa-solid fa-earth-americas"></i> Green Partners</h2>
            <p class="section-subtitle">The companies and local branches supporting your sustainability journey.</p>
        </div>
        <div class="search-wrapper">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="partnerSearch" class="list-search-input" placeholder="Search branches...">
        </div>
    </div>
    
    <div class="partner-list" id="partnerContainer">
        <?php if (empty($partners)): ?>
            <p style="color: var(--text-muted); padding: 20px;">No partner branches available at the moment.</p>
        <?php else: ?>
            <?php foreach ($partners as $partner): ?>
                <div class="partner-store-item filterable-item">
                    <div class="p-main-info">
                        <span class="p-icon"><i class="fa-solid fa-store"></i></span>
                        <div>
                            <h3 class="p-company-name"><?= htmlspecialchars($partner['company_name']) ?></h3>
                            <span class="p-branch-name"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($partner['Branch_name']) ?></span>
                        </div>
                    </div>
                    
                    <div class="p-meta">
                        <div class="p-detail-block">
                            <span class="p-label">Branch Address</span>
                            <span class="p-value"><?= htmlspecialchars($partner['branch_address']) ?></span>
                        </div>

                        <div class="p-detail-block right-align">
                            <span class="p-label">Contact</span>
                            <span class="p-phone">
                                <i class="fa-solid fa-phone"></i> <?= htmlspecialchars($partner['branch_telephones'] ?: ($partner['company_phone'] ?: 'N/A')) ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<script>
    // Simple, instant live-filtering logic for both lists
    function setupLiveSearch(inputId, containerId) {
        const searchInput = document.getElementById(inputId);
        const container = document.getElementById(containerId);
        
        if(searchInput && container) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const items = container.querySelectorAll('.filterable-item');
                
                items.forEach(item => {
                    // Extract all inner text (names, branches, costs, etc.)
                    const textContext = item.textContent.toLowerCase();
                    if(textContext.includes(searchTerm)) {
                        item.style.display = 'flex'; 
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
    }

    // Initialize listeners on page load
    document.addEventListener('DOMContentLoaded', function() {
        setupLiveSearch('voucherSearch', 'voucherContainer');
        setupLiveSearch('partnerSearch', 'partnerContainer');
    });
</script>

<?php include 'footer.php'; ?>
