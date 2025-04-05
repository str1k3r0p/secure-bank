<?php
/**
 * Banking DVWA Project
 * Account Dashboard View
 */
?>

<div class="dashboard-container">
    <!-- Account Overview -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="cyber-card dashboard-summary">
                <div class="cyber-card-header">
                    <h3>Account Summary</h3>
                </div>
                <div class="cyber-card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="summary-item">
                                <h4>Total Balance</h4>
                                <div class="balance-display"><?php echo $this->formatCurrency($totalBalance); ?></div>
                                <div class="balance-currency">USD</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-item">
                                <h4>Active Accounts</h4>
                                <div class="count-display"><?php echo count($accounts); ?></div>
                                <div class="count-label">Accounts</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-item">
                                <h4>Last Login</h4>
                                <div class="date-display"><?php echo date('M j, Y', strtotime($_SESSION['last_login'] ?? 'now')); ?></div>
                                <div class="time-display"><?php echo date('g:i A', strtotime($_SESSION['last_login'] ?? 'now')); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Accounts List -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="cyber-card">
                <div class="cyber-card-header d-flex justify-content-between align-items-center">
                    <h3>Your Accounts</h3>
                    <a href="<?php echo APP_URL; ?>/account/settings" class="btn cyber-btn-sm">Manage Accounts</a>
                </div>
                <div class="cyber-card-body">
                    <?php if (empty($accounts)): ?>
                        <div class="alert alert-info">You don't have any accounts yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover cyber-table">
                                <thead>
                                    <tr>
                                        <th>Account Number</th>
                                        <th>Type</th>
                                        <th>Balance</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($accounts as $account): ?>
                                        <tr>
                                            <td class="account-number"><?php echo $this->escape($account['account_number']); ?></td>
                                            <td><?php echo isset($accountTypeLabel) ? $accountTypeLabel($account['account_type']) : ucfirst($account['account_type']); ?></td>
                                            <td class="<?php echo $account['balance'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $this->formatCurrency($account['balance']); ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo APP_URL; ?>/account/details?id=<?php echo $account['id']; ?>" class="btn cyber-btn-sm">Details</a>
                                                    <a href="<?php echo APP_URL; ?>/transaction/new?account_id=<?php echo $account['id']; ?>" class="btn cyber-btn-sm">New Transaction</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row">
        <div class="col-md-12">
            <div class="cyber-card">
                <div class="cyber-card-header d-flex justify-content-between align-items-center">
                    <h3>Recent Transactions</h3>
                    <a href="<?php echo APP_URL; ?>/transaction/history" class="btn cyber-btn-sm">View All</a>
                </div>
                <div class="cyber-card-body">
                    <?php if (empty($recentTransactions)): ?>
                        <div class="alert alert-info">No recent transactions.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover cyber-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Account</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentTransactions as $transaction): ?>
                                        <tr>
                                            <td><?php echo $this->formatDate($transaction['created_at']); ?></td>
                                            <td><?php echo $this->escape($transaction['account_number']); ?></td>
                                            <td><?php echo ucfirst($transaction['transaction_type']); ?></td>
                                            <td><?php echo $this->escape($transaction['description'] ?: '-'); ?></td>
                                            <td class="<?php echo $transaction['amount'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $this->formatCurrency($transaction['amount']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-summary {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    border: 1px solid #00ffff;
    box-shadow: 0 0 15px rgba(0, 255, 255, 0.2);
}

.summary-item {
    text-align: center;
    padding: 20px;
}

.summary-item h4 {
    color: #ff00ff;
    margin-bottom: 10px;
    font-size: 1.1rem;
}

.balance-display, .count-display, .date-display {
    font-family: 'Share Tech Mono', monospace;
    font-size: 2rem;
    color: #00ffff;
    text-shadow: 0 0 5px rgba(0, 255, 255, 0.5);
    margin-bottom: 5px;
}

.balance-currency, .count-label, .time-display {
    color: #888;
    font-size: 0.9rem;
}

.cyber-table {
    background: rgba(10, 10, 30, 0.4);
    color: #ddd;
}

.cyber-table thead th {
    background: rgba(10, 10, 40, 0.6);
    color: #00ffff;
    border-bottom: 1px solid #00ffff;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 0.9rem;
}

.cyber-table tbody tr:hover {
    background: rgba(0, 255, 255, 0.05);
}

.account-number {
    font-family: 'Share Tech Mono', monospace;
    letter-spacing: 1px;
}

.text-success {
    color: #00ff00 !important;
}

.text-danger {
    color: #ff0066 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add cyberpunk animation effects to balance displays
    const balanceDisplays = document.querySelectorAll('.balance-display');
    
    balanceDisplays.forEach(display => {
        // Store original text
        const originalText = display.textContent;
        
        // Add glitch animation on hover
        display.addEventListener('mouseover', function() {
            // Start glitch animation
            this.classList.add('glitch-text');
            
            // Glitch effect on text
            let glitchInterval = setInterval(() => {
                if (Math.random() > 0.8) {
                    this.textContent = originalText.split('').map(char => {
                        return Math.random() > 0.8 ? String.fromCharCode(Math.floor(Math.random() * 26) + 97) : char;
                    }).join('');
                }
            }, 100);
            
            // Stop after a short time
            setTimeout(() => {
                clearInterval(glitchInterval);
                this.textContent = originalText;
                this.classList.remove('glitch-text');
            }, 500);
        });
    });
});
</script>