<?php
/**
 * Fees List Page
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

$pageTitle = 'Fee Management';

// Get all fees
$feeModel = new Fee();
$fees = $feeModel->getAll();

// Get classes from database
$db = new Database();
$db->query("SELECT DISTINCT class FROM students WHERE is_active = 1 ORDER BY class");
$classes = $db->fetchAll();

include dirname(__DIR__, 2) . '/includes/header.php';
include dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div id="alert-container"></div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-file-invoice-dollar"></i> Student Fees
        </h5>
        <?php if (isAdmin()): ?>
        <div>
            <button class="btn btn-success" onclick="window.location.href='bulk_assign.php'">
                <i class="fas fa-users"></i> Bulk Assign
            </button>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <!-- Filter Options -->
        <div class="row mb-3">
            <div class="col-md-3">
                <select class="form-control" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="partial">Partial</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-control" id="classFilter">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $classRow): ?>
                        <option value="<?php echo htmlspecialchars($classRow['class']); ?>"><?php echo htmlspecialchars($classRow['class']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table id="feesTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Amount Due</th>
                        <th>Amount Paid</th>
                        <th>Balance</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fees as $fee): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($fee['student_id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($fee['first_name'] . ' ' . $fee['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($fee['class']); ?></td>
                            <td><?php echo formatCurrency($fee['amount_due']); ?></td>
                            <td class="text-success"><?php echo formatCurrency($fee['amount_paid']); ?></td>
                            <td class="text-danger fw-bold"><?php echo formatCurrency($fee['balance']); ?></td>
                            <td><?php echo formatDate($fee['due_date']); ?></td>
                            <td><?php echo getFeeStatusBadge($fee['status']); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-info" onclick="viewStudent('<?php echo $fee['student_id']; ?>')" title="View Student">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($fee['balance'] > 0): ?>
                                        <button class="btn btn-success" onclick="recordPayment('<?php echo $fee['student_id']; ?>')" title="Record Payment">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
function getFeeStatusBadge($status) {
    $badges = [
        'paid' => '<span class="badge bg-success">PAID</span>',
        'partial' => '<span class="badge bg-warning">PARTIAL</span>',
        'pending' => '<span class="badge bg-secondary">PENDING</span>',
        'overdue' => '<span class="badge bg-danger">OVERDUE</span>'
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">UNKNOWN</span>';
}

$extraScripts = <<<'EOT'
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

        .card-header > div {
            display: flex;
            gap: 0.5rem;
            width: 100%;
        }

        .card-header .btn {
            flex: 1;
            font-size: 0.85rem;
            padding: 0.5rem 0.75rem;
        }

        .row.mb-3 {
            margin-bottom: 1rem !important;
        }

        .row.mb-3 .col-md-3 {
            margin-bottom: 0.5rem;
        }

        .form-control {
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

        .btn-group-sm > .btn {
            padding: 0.25rem 0.4rem;
            font-size: 0.75rem;
        }

        .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.4rem;
        }
    }

    @media (max-width: 576px) {
        .card-header > div {
            flex-direction: column;
        }

        .card-header .btn {
            width: 100%;
        }

        .table {
            font-size: 0.75rem;
        }

        .table th,
        .table td {
            padding: 0.4rem !important;
        }

        .btn-group-sm > .btn {
            padding: 0.2rem 0.3rem;
            font-size: 0.7rem;
        }

        .btn-group-sm > .btn i {
            font-size: 0.8rem;
        }

        .badge {
            font-size: 0.65rem;
            padding: 0.2rem 0.3rem;
        }

        strong {
            font-size: 0.85rem;
        }
    }
</style>

<script>
    // Initialize DataTable
    let table = initDataTable('#feesTable', {
        order: [[6, 'asc']], // Sort by due date
        columnDefs: [
            { orderable: false, targets: -1 }
        ]
    });

    // Filter by status
    document.getElementById('statusFilter').addEventListener('change', function() {
        table.column(7).search(this.value).draw();
    });

    // Filter by class
    document.getElementById('classFilter').addEventListener('change', function() {
        table.column(2).search(this.value).draw();
    });

    // View student
    function viewStudent(studentId) {
        window.location.href = '../students/view.php?id=' + encodeURIComponent(studentId);
    }

    // Record payment
    function recordPayment(studentId) {
        window.location.href = '../payments/record.php?student_id=' + encodeURIComponent(studentId);
    }
</script>
EOT;

include dirname(__DIR__, 2) . '/includes/footer.php';
?>
