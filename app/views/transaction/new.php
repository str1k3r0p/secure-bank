<?php
/**
 * Banking DVWA Project
 * New Transaction View
 */
?>

<div class="transaction-container">
    <!-- Transaction Form -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="cyber-card">
                <div class="cyber-card-header">
                    <h3>New Transaction</h3>
                </div>
                <div class="cyber-card-body">
                    <?php if (empty($accounts)): ?>
                        <div class="alert alert-warning">
                            <p>You don't have any active accounts to perform transactions.</p>
                            <a href="<?php echo APP_URL; ?>/account/settings" class="btn cyber-btn mt-2">Create Account</a>
                        </div>
                    <?php else: ?>
                        <!-- Transaction Type Tabs -->
                        <ul class="nav nav-tabs cyber-tabs mb-4" id="transactionTypeTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link <?php echo $transactionType === 'deposit' ? 'active' : ''; ?>" 
                                   id="deposit-tab" href="<?php echo APP_URL; ?>/transaction/new?type=deposit<?php echo $selectedAccount ? '&account_id=' . $selectedAccount['id'] : ''; ?>" 
                                   role="tab" aria-controls="deposit" aria-selected="<?php echo $transactionType === 'deposit' ? 'true' : 'false'; ?>">
                                    Deposit
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link <?php echo $transactionType === 'withdrawal' ? 'active' : ''; ?>" 
                                   id="withdrawal-tab" href="<?php echo APP_URL; ?>/transaction/new?type=withdrawal<?php echo $selectedAccount ? '&account_id=' . $selectedAccount['id'] : ''; ?>" 
                                   role="tab" aria-controls="withdrawal" aria-selected="<?php echo $transactionType === 'withdrawal' ? 'true' : 'false'; ?>">
                                    Withdrawal
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link <?php echo $transactionType === 'transfer' ? 'active' : ''; ?>" 
                                   id="transfer-tab" href="<?php echo APP_URL; ?>/transaction/new?type=transfer<?php echo $selectedAccount ? '&account_id=' . $selectedAccount['id'] : ''; ?>" 
                                   role="tab" aria-controls="transfer" aria-selected="<?php echo $transactionType === 'transfer' ? 'true' : 'false'; ?>">
                                    Transfer
                                </a>
                            </li>
                        </ul>
                        
                        <!-- Transaction Form -->
                        <form method="post" action="<?php echo APP_URL; ?>/transaction/process" class="cyber-form needs-validation" novalidate>
                            <?php echo $this->csrfField(); ?>
                            <input type="hidden" name="transaction_type" value="<?php echo $this->escape($transactionType); ?>">
                            
                            <!-- Account Selection -->
                            <div class="form-group row">
                                <label for="account_id" class="col-sm-4 col-form-label">From Account:</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="account_id" name="account_id" required>
                                        <option value="">-- Select Account --</option>
                                        <?php foreach ($accounts as $account): ?>
                                            <option value="<?php echo $account['id']; ?>" 
                                                    <?php echo (isset($selectedAccount) && $selectedAccount['id'] == $account['id']) ? 'selected' : ''; ?>>
                                                <?php echo $this->escape($account['account_number']); ?> - 
                                                <?php echo isset($accountTypeLabel) ? $accountTypeLabel($account['account_type']) : ucfirst($account['account_type']); ?> 
                                                (<?php echo $this->formatCurrency($account['balance']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['account_id'])): ?>
                                        <div class="invalid-feedback d-block"><?php echo $errors['account_id']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if ($transactionType === 'transfer'): ?>
                                <!-- Destination Account for Transfers -->
                                <div class="form-group row">
                                    <label for="to_account_number" class="col-sm-4 col-form-label">To Account Number:</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="to_account_number" name="to_account_number" 
                                               placeholder="Enter destination account number" 
                                               value="<?php echo isset($toAccountNumber) ? $this->escape($toAccountNumber) : ''; ?>" required>
                                        <?php if (isset($errors['to_account_number'])): ?>
                                            <div class="invalid-feedback d-block"><?php echo $errors['to_account_number']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Amount -->
                            <div class="form-group row">
                                <label for="amount" class="col-sm-4 col-form-label">Amount:</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><?php echo CURRENCY_SYMBOL; ?></span>
                                        </div>
                                        <input type="number" class="form-control" id="amount" name="amount" min="0.01" step="0.01" 
                                               placeholder="Enter amount" 
                                               value="<?php echo isset($amount) ? $this->escape($amount) : ''; ?>" required>
                                    </div>
                                    <?php if (isset($errors['amount'])): ?>
                                        <div class="invalid-feedback d-block"><?php echo $errors['amount']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="form-group row">
                                <label for="description" class="col-sm-4 col-form-label">Description:</label>
                                <div class="col-sm-8">
                                    <textarea class="form-control" id="description" name="description" rows="3" 
                                              placeholder="Enter a description for this transaction"><?php echo isset($description) ? $this->escape($description) : ''; ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="form-group row">
                                <div class="col-sm-8 offset-sm-4">
                                    <button type="submit" class="btn cyber-btn">Process Transaction</button>
                                    <a href="<?php echo APP_URL; ?>/account/dashboard" class="btn cyber-btn-outline ml-2">Cancel</a>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Transaction Info -->
    <div class="row mt-4">
        <div class="col-lg-8 mx-auto">
            <div class="cyber-card">
                <div class="cyber-card-header">
                    <h3>Transaction Information</h3>
                </div>
                <div class="cyber-card-body">
                    <?php if ($transactionType === 'deposit'): ?>
                        <div class="transaction-info">
                            <h4>About Deposits</h4>
                            <p>Deposits add funds to your account. Deposited funds are typically available immediately.</p>
                            <ul>
                                <li>Funds are instantly credited to your account</li>
                                <li>No fees for standard deposits</li>
                                <li>Maximum deposit amount: $10,000 per transaction</li>
                            </ul>
                        </div>
                    <?php elseif ($transactionType === 'withdrawal'): ?>
                        <div class="transaction-info">
                            <h4>About Withdrawals</h4>
                            <p>Withdrawals remove funds from your account. You cannot withdraw more than your available balance.</p>
                            <ul>
                                <li>Funds are instantly debited from your account</li>
                                <li>Daily withdrawal limit: $5,000</li>
                                <li>Minimum withdrawal amount: $10</li>
                            </ul>
                        </div>
                    <?php elseif ($transactionType === 'transfer'): ?>
                        <div class="transaction-info">
                            <h4>About Transfers</h4>
                            <p>Transfers move funds from your account to another account. The recipient account must be valid.</p>
                            <ul>
                                <li>Transfers between accounts are processed immediately</li>
                                <li>You need the recipient's account number to perform a transfer</li>
                                <li>Maximum transfer amount: $25,000 per transaction</li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.cyber-tabs {
    border-bottom: 1px solid #00ffff;
}

.cyber-tabs .nav-link {
    color: #ccc;
    background-color: rgba(10, 10, 30, 0.6);
    border: none;
    border-top-left-radius: 5px;
    border-top-right-radius: 5px;
    padding: 10px 20px;
    margin-right: 5px;
    transition: all 0.3s;
}

.cyber-tabs .nav-link:hover {
    color: #fff;
    background-color: rgba(0, 255, 255, 0.1);
}

.cyber-tabs .nav-link.active {
    color: #00ffff;
    background-color: rgba(0, 255, 255, 0.1);
    border: 1px solid #00ffff;
    border-bottom: none;
}

.cyber-form .form-control {
    background-color: rgba(10, 10, 30, 0.6);
    border: 1px solid #454545;
    color: #fff;
    transition: all 0.3s;
}

.cyber-form .form-control:focus {
    background-color: rgba(10, 10, 30, 0.8);
    border-color: #00ffff;
    box-shadow: 0 0 5px rgba(0, 255, 255, 0.5);
}

.cyber-form .input-group-text {
    background-color: rgba(10, 10, 30, 0.7);
    border: 1px solid #454545;
    color: #00ffff;
}

.cyber-form label {
    color: #00ffff;
}

.transaction-info {
    background-color: rgba(10, 10, 30, 0.3);
    border-left: 3px solid #00ffff;
    padding: 15px;
    border-radius: 5px;
}

.transaction-info h4 {
    color: #ff00ff;
    margin-bottom: 15px;
}

.transaction-info ul {
    color: #ccc;
    padding-left: 20px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Add glitch animation to submit button on hover
    const submitBtn = document.querySelector('.cyber-btn[type="submit"]');
    
    if (submitBtn) {
        submitBtn.addEventListener('mouseover', function() {
            this.classList.add('btn-glitch');
            
            setTimeout(() => {
                this.classList.remove('btn-glitch');
            }, 300);
        });
    }
});
</script>