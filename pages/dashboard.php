<?php
/**
 * Dashboard Page
 */

require_once dirname(__DIR__) . '/middleware/auth.php';

$pageTitle = 'Dashboard';

// Get statistics
$studentModel = new Student();
$feeModel = new Fee();
$paymentModel = new Payment();
$userModel = new User();

$studentStats = $studentModel->getStats();
$feeStats = $feeModel->getStats();

// Payment stats - get total stats for all users
$totalPaymentStats = $paymentModel->getStats(['date_from' => date('Y-m-01')]);

// For teachers, also get their personal stats
$personalPaymentStats = null;
if (!isAdmin()) {
    $personalPaymentStats = $paymentModel->getStats([
        'date_from' => date('Y-m-01'),
        'received_by' => $_SESSION['user_id']
    ]);
}

$userStats = $userModel->getStats();

// Get recent payments - teachers see only their own, admins see all
$recentPaymentFilters = [];
if (!isAdmin()) {
    $recentPaymentFilters['received_by'] = $_SESSION['user_id'];
}
$recentPayments = $paymentModel->getAll($recentPaymentFilters);
$recentPayments = array_slice($recentPayments, 0, 10); // Last 10 payments

// Get overdue fees
$overdueFees = $feeModel->getOverdue();
$overdueCount = count($overdueFees);

include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/sidebar.php';
?>

<!-- Statistics Cards -->
<div class="row">
    <!-- Total Students -->
    <div class="col-xl-3 col-md-6 col-sm-6 mb-4">
        <div class="card border-left-primary shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Students
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($studentStats['total'] ?? 0); ?>
                        </div>
                        <small class="text-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo number_format($studentStats['active'] ?? 0); ?> Active
                        </small>
                    </div>
                    <div class="text-primary" style="font-size: 3rem; opacity: 0.3;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Fees Due -->
    <div class="col-xl-3 col-md-6 col-sm-6 mb-4">
        <div class="card border-left-info shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Fees Due
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo formatCurrency($feeStats['total_due'] ?? 0); ?>
                        </div>
                        <small class="text-muted">
                            <?php echo number_format($feeStats['total_fees'] ?? 0); ?> Students
                        </small>
                    </div>
                    <div class="text-info" style="font-size: 3rem; opacity: 0.3;">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Paid -->
    <div class="col-xl-3 col-md-6 col-sm-6 mb-4">
        <div class="card border-left-success shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Collected
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo formatCurrency($feeStats['total_paid'] ?? 0); ?>
                        </div>
                        <small class="text-success">
                            <?php echo number_format($feeStats['paid_count'] ?? 0); ?> Fully Paid
                        </small>
                    </div>
                    <div class="text-success mobile-icon" style="font-size: 3rem; opacity: 0.3;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Outstanding Balance -->
    <div class="col-xl-3 col-md-6 col-sm-6 mb-4">
        <div class="card border-left-warning shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Outstanding Balance
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo formatCurrency($feeStats['total_balance'] ?? 0); ?>
                        </div>
                        <small class="text-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?php echo $overdueCount; ?> Overdue
                        </small>
                    </div>
                    <div class="text-warning" style="font-size: 3rem; opacity: 0.3;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <!-- Fee Status Chart -->
    <div class="col-xl-6 col-lg-6 col-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Fee Status Overview</h6>
            </div>
            <div class="card-body">
                <canvas id="feeStatusChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Monthly Collection Chart -->
    <div class="col-xl-6 col-lg-6 col-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">This Month's Collection</h6>
            </div>
            <div class="card-body">
                <?php if (!isAdmin() && $personalPaymentStats): ?>
                    <!-- Teacher Personal Collection -->
                    <div class="mb-4 pb-3 border-bottom">
                        <div class="text-center">
                            <p class="mb-1 text-muted small">Your Personal Collection</p>
                            <h3 class="text-primary mb-0"><?php echo formatCurrency($personalPaymentStats['total_amount'] ?? 0); ?></h3>
                            <p class="text-muted small">From <?php echo number_format($personalPaymentStats['total_payments'] ?? 0); ?> payments</p>
                        </div>
                        <div class="row mt-3">
                            <div class="col-4 text-center">
                                <p class="mb-1 text-muted small">Cash</p>
                                <h6 class="mb-0 small"><?php echo formatCurrency($personalPaymentStats['cash_amount'] ?? 0); ?></h6>
                            </div>
                            <div class="col-4 text-center">
                                <p class="mb-1 text-muted small">Mobile Money</p>
                                <h6 class="mb-0 small"><?php echo formatCurrency($personalPaymentStats['mobile_money_amount'] ?? 0); ?></h6>
                            </div>
                            <div class="col-4 text-center">
                                <p class="mb-1 text-muted small">Others</p>
                                <h6 class="mb-0 small"><?php echo formatCurrency($personalPaymentStats['others_amount'] ?? 0); ?></h6>
                            </div>
                        </div>
                    </div>

                    <!-- Total System Collection -->
                    <div class="text-center">
                        <p class="mb-1 text-muted small">Total System Collection (All Teachers)</p>
                        <h3 class="text-success mb-0"><?php echo formatCurrency($totalPaymentStats['total_amount'] ?? 0); ?></h3>
                        <p class="text-muted small">From <?php echo number_format($totalPaymentStats['total_payments'] ?? 0); ?> payments</p>
                    </div>
                    <div class="row mt-3">
                        <div class="col-4 text-center">
                            <p class="mb-1 text-muted small">Cash</p>
                            <h6 class="mb-0 small"><?php echo formatCurrency($totalPaymentStats['cash_amount'] ?? 0); ?></h6>
                        </div>
                        <div class="col-4 text-center">
                            <p class="mb-1 text-muted small">Mobile Money</p>
                            <h6 class="mb-0 small"><?php echo formatCurrency($totalPaymentStats['mobile_money_amount'] ?? 0); ?></h6>
                        </div>
                        <div class="col-4 text-center">
                            <p class="mb-1 text-muted small">Others</p>
                            <h6 class="mb-0 small"><?php echo formatCurrency($totalPaymentStats['others_amount'] ?? 0); ?></h6>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Admin Total Collection -->
                    <div class="text-center">
                        <h2 class="text-success"><?php echo formatCurrency($totalPaymentStats['total_amount'] ?? 0); ?></h2>
                        <p class="text-muted">From <?php echo number_format($totalPaymentStats['total_payments'] ?? 0); ?> payments</p>
                    </div>
                    <hr>
                    <div class="row mt-4">
                        <div class="col-4 text-center">
                            <p class="mb-1 text-muted small">Cash</p>
                            <h6 class="mb-0"><?php echo formatCurrency($totalPaymentStats['cash_amount'] ?? 0); ?></h6>
                        </div>
                        <div class="col-4 text-center">
                            <p class="mb-1 text-muted small">Mobile Money</p>
                            <h6 class="mb-0"><?php echo formatCurrency($totalPaymentStats['mobile_money_amount'] ?? 0); ?></h6>
                        </div>
                        <div class="col-4 text-center">
                            <p class="mb-1 text-muted small">Others</p>
                            <h6 class="mb-0"><?php echo formatCurrency($totalPaymentStats['others_amount'] ?? 0); ?></h6>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Tables Row -->
<div class="row">
    <!-- Recent Payments -->
    <div class="col-xl-8 col-lg-7 col-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Payments</h6>
                <a href="<?php echo APP_URL; ?>/pages/payments/index.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Receipt</th>
                                <th>Student</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentPayments)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No payments recorded yet</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentPayments as $payment): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($payment['receipt_number']); ?></code></td>
                                        <td><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                                        <td class="text-success font-weight-bold"><?php echo formatCurrency($payment['amount_paid']); ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></span>
                                        </td>
                                        <td><?php echo formatDate($payment['payment_date']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Fees -->
    <div class="col-xl-4 col-lg-5 col-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-danger">Overdue Fees (<?php echo $overdueCount; ?>)</h6>
            </div>
            <div class="card-body">
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($overdueFees)): ?>
                        <p class="text-center text-muted">No overdue fees</p>
                    <?php else: ?>
                        <?php foreach (array_slice($overdueFees, 0, 10) as $fee): ?>
                            <div class="alert alert-danger py-2 px-3 mb-2">
                                <strong><?php echo htmlspecialchars($fee['first_name'] . ' ' . $fee['last_name']); ?></strong><br>
                                <small>Class: <?php echo htmlspecialchars($fee['class']); ?></small><br>
                                <small>Balance: <strong><?php echo formatCurrency($fee['balance']); ?></strong></small><br>
                                <small class="text-danger">Due: <?php echo formatDate($fee['due_date']); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-primary {
        border-left: 4px solid #4f46e5 !important;
    }

    .border-left-success {
        border-left: 4px solid #10b981 !important;
    }

    .border-left-info {
        border-left: 4px solid #3b82f6 !important;
    }

    .border-left-warning {
        border-left: 4px solid #f59e0b !important;
    }

    .text-xs {
        font-size: 0.7rem;
    }

    .text-primary {
        color: #4f46e5 !important;
    }

    .text-success {
        color: #10b981 !important;
    }

    .text-info {
        color: #3b82f6 !important;
    }

    .text-warning {
        color: #f59e0b !important;
    }

    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        /* Statistics Cards */
        .h5 {
            font-size: 1.1rem !important;
        }

        .text-xs {
            font-size: 0.65rem;
        }

        .mobile-icon {
            font-size: 2rem !important;
        }

        /* Card spacing */
        .card-body {
            padding: 1rem !important;
        }

        .card-header {
            padding: 0.75rem 1rem !important;
        }

        .card-header h6 {
            font-size: 0.9rem !important;
        }

        /* Buttons */
        .btn-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        /* Tables */
        .table {
            font-size: 0.85rem;
        }

        .table th,
        .table td {
            padding: 0.5rem !important;
            white-space: nowrap;
        }

        /* Overdue alerts */
        .alert {
            font-size: 0.85rem;
        }

        /* Chart height */
        #feeStatusChart {
            height: 250px !important;
        }

        /* Collection stats */
        .row.mt-4 .col-4 h6,
        .row.mt-3 .col-4 h6 {
            font-size: 0.9rem;
        }

        .row.mt-4 .col-4 p,
        .row.mt-3 .col-4 p {
            font-size: 0.75rem;
        }

        /* Teacher collection sections */
        h3 {
            font-size: 1.5rem !important;
        }
    }

    @media (max-width: 576px) {
        /* Extra small devices */
        .h5 {
            font-size: 1rem !important;
        }

        .mobile-icon {
            font-size: 1.5rem !important;
        }

        .text-xs {
            font-size: 0.6rem;
        }

        small {
            font-size: 0.75rem;
        }

        /* Reduce padding on small screens */
        .card-body {
            padding: 0.75rem !important;
        }

        .card-header h6 {
            font-size: 0.85rem !important;
        }

        /* Stack collection method columns */
        .row.mt-4 .col-4,
        .row.mt-3 .col-4 {
            margin-bottom: 0.5rem;
        }

        /* Table text */
        .table {
            font-size: 0.75rem;
        }

        code {
            font-size: 0.7rem;
        }

        .badge {
            font-size: 0.65rem;
        }

        /* Teacher collection headers */
        h3 {
            font-size: 1.25rem !important;
        }
    }
</style>

<?php
// Extract chart data values
$paidCount = $feeStats['paid_count'] ?? 0;
$partialCount = $feeStats['partial_count'] ?? 0;
$pendingCount = $feeStats['pending_count'] ?? 0;
$overdueCount = $feeStats['overdue_count'] ?? 0;

$extraScripts = <<<EOT
<script>
    // Fee Status Chart
    const feeStatusCtx = document.getElementById('feeStatusChart').getContext('2d');
    new Chart(feeStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Paid', 'Partial', 'Pending', 'Overdue'],
            datasets: [{
                data: [
                    {$paidCount},
                    {$partialCount},
                    {$pendingCount},
                    {$overdueCount}
                ],
                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
EOT;

include dirname(__DIR__) . '/includes/footer.php';
?>
