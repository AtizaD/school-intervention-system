<?php
/**
 * Reports Page
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

$pageTitle = 'Reports';

// Get statistics for reports
$feeModel = new Fee();
$paymentModel = new Payment();
$studentModel = new Student();

$feeStats = $feeModel->getStats();
$studentStats = $studentModel->getStats();

// Get classes from database
$db = new Database();
$db->query("SELECT DISTINCT class FROM students WHERE is_active = 1 ORDER BY class");
$classes = $db->fetchAll();

include dirname(__DIR__, 2) . '/includes/header.php';
include dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div id="alert-container"></div>

<div class="row">
    <!-- Payment Summary Report -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Payment Summary Report</h5>
            </div>
            <div class="card-body">
                <p>Generate detailed payment collection reports by date range, class, or payment method.</p>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control" id="paymentDateFrom">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control" id="paymentDateTo">
                    </div>
                </div>
                <button class="btn btn-primary w-100" onclick="generatePaymentReport()">
                    <i class="fas fa-file-pdf"></i> Generate Report
                </button>
            </div>
        </div>
    </div>

    <?php if (isAdmin()): ?>
    <!-- Outstanding Fees Report -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Outstanding Fees Report</h5>
            </div>
            <div class="card-body">
                <p>View all students with outstanding balances and overdue fees.</p>
                <div class="mb-3">
                    <label class="form-label">Filter by Class</label>
                    <select class="form-control" id="outstandingClass">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $classRow): ?>
                            <option value="<?php echo htmlspecialchars($classRow['class']); ?>"><?php echo htmlspecialchars($classRow['class']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-danger w-100" onclick="generateOutstandingReport()">
                    <i class="fas fa-file-pdf"></i> Generate Report
                </button>
            </div>
        </div>
    </div>

    <!-- Fee Collection Summary -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Collection Summary</h5>
            </div>
            <div class="card-body">
                <p>Overall fee collection statistics and analysis.</p>
                <table class="table table-sm">
                    <tr>
                        <th>Total Fees Assigned:</th>
                        <td class="text-end"><strong><?php echo formatCurrency($feeStats['total_due'] ?? 0); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Total Collected:</th>
                        <td class="text-end text-success"><strong><?php echo formatCurrency($feeStats['total_paid'] ?? 0); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Outstanding Balance:</th>
                        <td class="text-end text-danger"><strong><?php echo formatCurrency($feeStats['total_balance'] ?? 0); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Collection Rate:</th>
                        <td class="text-end">
                            <strong>
                                <?php
                                $rate = $feeStats['total_due'] > 0 ? ($feeStats['total_paid'] / $feeStats['total_due']) * 100 : 0;
                                echo number_format($rate, 1) . '%';
                                ?>
                            </strong>
                        </td>
                    </tr>
                </table>
                <button class="btn btn-success w-100" onclick="generateCollectionReport()">
                    <i class="fas fa-file-pdf"></i> Generate Full Report
                </button>
            </div>
        </div>
    </div>

    <!-- Student Statistics -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-user-graduate"></i> Student Statistics</h5>
            </div>
            <div class="card-body">
                <p>Student enrollment and demographic reports.</p>
                <table class="table table-sm">
                    <tr>
                        <th>Total Students:</th>
                        <td class="text-end"><strong><?php echo number_format($studentStats['total'] ?? 0); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Active Students:</th>
                        <td class="text-end"><strong><?php echo number_format($studentStats['active'] ?? 0); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Male Students:</th>
                        <td class="text-end"><strong><?php echo number_format($studentStats['males'] ?? 0); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Female Students:</th>
                        <td class="text-end"><strong><?php echo number_format($studentStats['females'] ?? 0); ?></strong></td>
                    </tr>
                </table>
                <button class="btn btn-info w-100" onclick="generateStudentReport()">
                    <i class="fas fa-file-pdf"></i> Generate Report
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Audit Log Section -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-history"></i> Recent Activity Log</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="auditTable" class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Table</th>
                        <th>Record ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $auditDb = new Database();

                    // Teachers only see their own logs, admins see all
                    if (isAdmin()) {
                        $auditDb->query("SELECT al.*, u.fullname
                                   FROM audit_logs al
                                   LEFT JOIN users u ON al.user_id = u.user_id
                                   ORDER BY al.created_at DESC
                                   LIMIT 100");
                        $logs = $auditDb->fetchAll();
                    } else {
                        $auditDb->query("SELECT al.*, u.fullname
                                   FROM audit_logs al
                                   LEFT JOIN users u ON al.user_id = u.user_id
                                   WHERE al.user_id = :user_id
                                   ORDER BY al.created_at DESC
                                   LIMIT 100");
                        $auditDb->bind(':user_id', $_SESSION['user_id']);
                        $logs = $auditDb->fetchAll();
                    }

                    foreach ($logs as $log):
                    ?>
                        <tr>
                            <td><small><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></small></td>
                            <td><?php echo htmlspecialchars($log['fullname'] ?? 'System'); ?></td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo htmlspecialchars($log['action']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($log['table_name']); ?></td>
                            <td><code><?php echo htmlspecialchars($log['record_id'] ?? '-'); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$isAdminJs = isAdmin() ? 'true' : 'false';
$extraScripts = <<<EOT
<script>
    // Set default dates
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

    document.getElementById('paymentDateFrom').valueAsDate = firstDay;
    document.getElementById('paymentDateTo').valueAsDate = today;

    // Initialize audit table
    initDataTable('#auditTable', {
        order: [[0, 'desc']],
        pageLength: 50
    });

    // Generate payment report
    function generatePaymentReport() {
        const dateFrom = document.getElementById('paymentDateFrom').value;
        const dateTo = document.getElementById('paymentDateTo').value;

        if (!dateFrom || !dateTo) {
            showAlert('Please select date range', 'warning');
            return;
        }

        // Open PDF in new window
        const url = '../../api/reports/payment_summary.php?date_from=' + encodeURIComponent(dateFrom) + '&date_to=' + encodeURIComponent(dateTo);
        window.open(url, '_blank');
        showAlert('Generating payment report...', 'info');
    }

    // Generate outstanding report
    function generateOutstandingReport() {
        const classFilter = document.getElementById('outstandingClass').value;

        // Only admins can generate this report
        const isAdmin = {$isAdminJs};
        if (!isAdmin) {
            showAlert('Only administrators can generate outstanding fees reports', 'danger');
            return;
        }

        // Open PDF in new window
        const url = '../../api/reports/outstanding_fees.php' + (classFilter ? '?class=' + encodeURIComponent(classFilter) : '');
        window.open(url, '_blank');
        showAlert('Generating outstanding fees report...', 'info');
    }

    // Generate collection report
    function generateCollectionReport() {
        // Only admins can generate this report
        const isAdmin = {$isAdminJs};
        if (!isAdmin) {
            showAlert('Only administrators can generate collection summary reports', 'danger');
            return;
        }

        // Open PDF in new window
        const url = '../../api/reports/collection_summary.php';
        window.open(url, '_blank');
        showAlert('Generating collection summary report...', 'info');
    }

    // Generate student report
    function generateStudentReport() {
        // Only admins can generate this report
        const isAdmin = {$isAdminJs};
        if (!isAdmin) {
            showAlert('Only administrators can generate student statistics reports', 'danger');
            return;
        }

        // Open PDF in new window
        const url = '../../api/reports/student_statistics.php';
        window.open(url, '_blank');
        showAlert('Generating student statistics report...', 'info');
    }
</script>
EOT;

include dirname(__DIR__, 2) . '/includes/footer.php';
?>
