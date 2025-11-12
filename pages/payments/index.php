<?php
/**
 * Payments List Page
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

$pageTitle = 'Payments';

// Get payments - teachers see only their own, admins see all
$paymentModel = new Payment();
$filters = [];

if (!isAdmin()) {
    // Teachers only see payments they recorded
    $filters['received_by'] = $_SESSION['user_id'];
}

$payments = $paymentModel->getAll($filters);

include dirname(__DIR__, 2) . '/includes/header.php';
include dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div id="alert-container"></div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-money-bill-wave"></i> Payment Records
        </h5>
        <div>
            <button class="btn btn-success me-2" onclick="window.location.href='bulk_receipts.php'">
                <i class="fas fa-print"></i> Print Bulk Receipts
            </button>
            <button class="btn btn-primary" onclick="window.location.href='record.php'">
                <i class="fas fa-plus"></i> Record Payment
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-3">
                <input type="date" class="form-control" id="dateFrom" placeholder="Date From">
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control" id="dateTo" placeholder="Date To">
            </div>
            <div class="col-md-3">
                <select class="form-control" id="methodFilter">
                    <option value="">All Methods</option>
                    <option value="cash">Cash</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="others">Others</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-info w-100" onclick="applyFilters()">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="paymentsTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Receipt No.</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Received By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo formatDate($payment['payment_date']); ?></td>
                            <td><code><?php echo htmlspecialchars($payment['receipt_number']); ?></code></td>
                            <td><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($payment['class']); ?></td>
                            <td class="text-success fw-bold"><?php echo formatCurrency($payment['amount_paid']); ?></td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($payment['received_by_name']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewReceipt('<?php echo $payment['receipt_number']; ?>')" title="View Receipt">
                                    <i class="fas fa-receipt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<'EOT'
<script>
    // Initialize DataTable
    let table = initDataTable('#paymentsTable', {
        order: [[0, 'desc']], // Sort by date descending
        columnDefs: [
            { orderable: false, targets: -1 }
        ]
    });

    // Apply filters
    function applyFilters() {
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        const method = document.getElementById('methodFilter').value;

        // Reload with filters
        const params = new URLSearchParams();
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);
        if (method) params.append('payment_method', method);

        window.location.href = 'index.php?' + params.toString();
    }

    // View receipt
    function viewReceipt(receiptNumber) {
        window.open('receipt.php?receipt=' + receiptNumber, '_blank');
    }
</script>

<style>
    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            align-items: flex-start !important;
        }

        .card-header h5 {
            font-size: 1rem;
            margin-bottom: 0.5rem !important;
        }

        .card-header .btn {
            width: 100%;
            margin-top: 0.5rem;
        }

        .row.mb-3 {
            margin-bottom: 1rem !important;
        }

        .row.mb-3 .col-md-3 {
            margin-bottom: 0.5rem;
        }

        .form-control,
        .btn {
            font-size: 0.9rem;
        }

        .table {
            font-size: 0.85rem;
        }

        .table th,
        .table td {
            padding: 0.5rem !important;
            white-space: nowrap;
        }

        .btn-sm {
            padding: 0.25rem 0.4rem;
            font-size: 0.75rem;
        }

        .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.4rem;
        }

        code {
            font-size: 0.75rem;
        }
    }

    @media (max-width: 576px) {
        .table {
            font-size: 0.75rem;
        }

        .table th,
        .table td {
            padding: 0.4rem !important;
        }

        .btn-sm {
            padding: 0.2rem 0.3rem;
            font-size: 0.7rem;
        }

        .btn-sm i {
            font-size: 0.8rem;
        }

        .badge {
            font-size: 0.65rem;
            padding: 0.2rem 0.3rem;
        }

        code {
            font-size: 0.7rem;
        }

        .form-control {
            font-size: 0.85rem;
        }

        .btn {
            font-size: 0.85rem;
        }
    }
</style>
EOT;

include dirname(__DIR__, 2) . '/includes/footer.php';
?>
