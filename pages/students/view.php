<?php
/**
 * View Student Page
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

$pageTitle = 'View Student';

$studentId = $_GET['id'] ?? '';

if (empty($studentId)) {
    header('Location: index.php');
    exit;
}

include dirname(__DIR__, 2) . '/includes/header.php';
include dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div id="alert-container"></div>

<div id="loading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status"></div>
    <p class="mt-2">Loading student data...</p>
</div>

<div id="studentContent" style="display: none;">
    <div class="row">
        <!-- Student Details Card -->
        <div class="col-lg-4 col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Student Details</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="student-avatar mb-3">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h4 id="studentName"></h4>
                        <p class="text-muted" id="studentId"></p>
                    </div>

                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Gender:</th>
                            <td id="studentGender"></td>
                        </tr>
                        <tr>
                            <th>Class:</th>
                            <td id="studentClass"></td>
                        </tr>
                        <tr>
                            <th>House:</th>
                            <td id="studentHouse"></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td id="studentStatus"></td>
                        </tr>
                    </table>

                    <div class="d-grid gap-2 mt-3">
                        <?php if (isAdmin()): ?>
                        <button class="btn btn-warning" onclick="editStudent()">
                            <i class="fas fa-edit"></i> Edit Student
                        </button>
                        <?php endif; ?>
                        <button class="btn btn-secondary" onclick="window.location.href='index.php'">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </button>
                    </div>
                </div>
            </div>

            <!-- Parent/Guardian Information Card -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Parent/Guardian</h5>
                </div>
                <div class="card-body">
                    <div id="noParent" style="display: none;">
                        <p class="text-muted text-center">No parent/guardian information available.</p>
                    </div>

                    <div id="parentInfo" style="display: none;">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Full Name:</th>
                                <td id="parentFullname"></td>
                            </tr>
                            <tr>
                                <th>Relationship:</th>
                                <td id="parentRelationship"></td>
                            </tr>
                            <tr>
                                <th>Contact:</th>
                                <td id="parentContact"></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fee & Payment Information -->
        <div class="col-lg-8 col-md-12">
            <!-- Fee Status Card -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> Fee Status</h5>
                </div>
                <div class="card-body">
                    <div id="noFee" style="display: none;">
                        <p class="text-muted text-center">No fees assigned yet.</p>
                        <div class="text-center">
                            <button class="btn btn-primary" onclick="window.location.href='../fees/assign.php?student_id=' + studentIdValue">
                                <i class="fas fa-plus"></i> Assign Fee
                            </button>
                        </div>
                    </div>

                    <div id="feeInfo" style="display: none;">
                        <div class="row text-center">
                            <div class="col-md-3 col-sm-6 col-6">
                                <p class="text-muted mb-1">Amount Due</p>
                                <h4 id="amountDue" class="text-primary">-</h4>
                            </div>
                            <div class="col-md-3 col-sm-6 col-6">
                                <p class="text-muted mb-1">Amount Paid</p>
                                <h4 id="amountPaid" class="text-success">-</h4>
                            </div>
                            <div class="col-md-3 col-sm-6 col-6">
                                <p class="text-muted mb-1">Balance</p>
                                <h4 id="balance" class="text-danger">-</h4>
                            </div>
                            <div class="col-md-3 col-sm-6 col-6">
                                <p class="text-muted mb-1">Due Date</p>
                                <h4 id="dueDate" class="text-warning">-</h4>
                            </div>
                        </div>
                        <hr>
                        <div class="text-center">
                            <span id="feeStatus"></span>
                            <button class="btn btn-success ms-2" onclick="recordPayment()" id="paymentBtn">
                                <i class="fas fa-money-bill-wave"></i> Record Payment
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment History Card -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Payment History</h5>
                </div>
                <div class="card-body">
                    <div id="noPayments" style="display: none;">
                        <p class="text-muted text-center">No payment records yet.</p>
                    </div>

                    <div id="paymentsTable" class="table-responsive" style="display: none;">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Receipt No.</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Received By</th>
                                </tr>
                            </thead>
                            <tbody id="paymentsBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<EOT
<style>
    .student-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        font-size: 3rem;
        color: white;
    }

    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .student-avatar {
            width: 100px;
            height: 100px;
            font-size: 2.5rem;
        }

        .card-header h5 {
            font-size: 1rem;
        }

        .table {
            font-size: 0.85rem;
        }

        .d-grid.gap-2 .btn {
            font-size: 0.9rem;
            padding: 0.6rem;
        }

        .row.text-center h4 {
            font-size: 1.2rem;
        }

        .row.text-center .col-md-3 {
            margin-bottom: 1rem;
        }
    }

    @media (max-width: 576px) {
        .student-avatar {
            width: 80px;
            height: 80px;
            font-size: 2rem;
        }

        .card-body {
            padding: 1rem;
        }

        .table {
            font-size: 0.75rem;
        }

        .table th,
        .table td {
            padding: 0.4rem !important;
        }

        .row.text-center h4 {
            font-size: 1rem;
        }

        .row.text-center p {
            font-size: 0.8rem;
            margin-bottom: 0.3rem !important;
        }

        .btn {
            font-size: 0.85rem;
            padding: 0.5rem;
        }

        .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.4rem;
        }
    }
</style>

<script>
    const studentIdValue = '{$studentId}';
    const loading = document.getElementById('loading');
    const content = document.getElementById('studentContent');

    // Load student data
    async function loadStudent() {
        try {
            const response = await fetch('../../api/students/get.php?id=' + encodeURIComponent(studentIdValue));
            const result = await response.json();

            if (result.success) {
                const student = result.data;
                displayStudent(student);
                await loadPayments();

                loading.style.display = 'none';
                content.style.display = 'block';
            } else {
                showAlert(result.message, 'danger');
                setTimeout(() => window.location.href = 'index.php', 2000);
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Failed to load student data.', 'danger');
        }
    }

    // Display student information
    function displayStudent(student) {
        document.getElementById('studentName').textContent = student.first_name + ' ' + student.last_name;
        document.getElementById('studentId').textContent = student.student_id;
        document.getElementById('studentGender').innerHTML = '<span class="badge bg-' + (student.gender === 'male' ? 'info' : 'pink') + '">' + student.gender.toUpperCase() + '</span>';
        document.getElementById('studentClass').textContent = student.class;
        document.getElementById('studentHouse').innerHTML = '<span class="badge bg-secondary">House ' + student.house + '</span>';
        document.getElementById('studentStatus').innerHTML = student.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';

        // Parent information
        if (student.parent_fullname && student.parent_contact) {
            document.getElementById('parentInfo').style.display = 'block';
            document.getElementById('noParent').style.display = 'none';

            document.getElementById('parentFullname').textContent = student.parent_fullname;
            document.getElementById('parentRelationship').innerHTML = '<span class="badge bg-info">' + (student.parent_relationship || 'N/A').toUpperCase() + '</span>';
            document.getElementById('parentContact').innerHTML = '<a href="tel:' + student.parent_contact + '" class="text-decoration-none"><i class="fas fa-phone"></i> ' + student.parent_contact + '</a>';
        } else {
            document.getElementById('parentInfo').style.display = 'none';
            document.getElementById('noParent').style.display = 'block';
        }

        // Fee information
        if (student.amount_due && student.amount_due > 0) {
            document.getElementById('feeInfo').style.display = 'block';
            document.getElementById('noFee').style.display = 'none';

            document.getElementById('amountDue').textContent = formatCurrency(student.amount_due);
            document.getElementById('amountPaid').textContent = formatCurrency(student.amount_paid || 0);
            document.getElementById('balance').textContent = formatCurrency(student.balance || 0);
            document.getElementById('dueDate').textContent = student.due_date ? formatDate(student.due_date) : '-';

            // Fee status badge
            let statusBadge = '';
            switch(student.fee_status) {
                case 'paid':
                    statusBadge = '<span class="badge bg-success">PAID</span>';
                    document.getElementById('paymentBtn').style.display = 'none';
                    break;
                case 'partial':
                    statusBadge = '<span class="badge bg-warning">PARTIAL PAYMENT</span>';
                    break;
                case 'overdue':
                    statusBadge = '<span class="badge bg-danger">OVERDUE</span>';
                    break;
                default:
                    statusBadge = '<span class="badge bg-secondary">PENDING</span>';
            }
            document.getElementById('feeStatus').innerHTML = statusBadge;
        } else {
            document.getElementById('feeInfo').style.display = 'none';
            document.getElementById('noFee').style.display = 'block';
        }
    }

    // Load payment history
    async function loadPayments() {
        try {
            const response = await fetch('../../api/payments/list.php?student_id=' + encodeURIComponent(studentIdValue));
            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                document.getElementById('paymentsTable').style.display = 'block';
                document.getElementById('noPayments').style.display = 'none';

                const tbody = document.getElementById('paymentsBody');
                tbody.innerHTML = '';

                result.data.forEach(payment => {
                    const row = `
                        <tr>
                            <td>\${formatDate(payment.payment_date)}</td>
                            <td><code>\${payment.receipt_number}</code></td>
                            <td class="text-success fw-bold">\${formatCurrency(payment.amount_paid)}</td>
                            <td><span class="badge bg-secondary">\${payment.payment_method.replace('_', ' ').toUpperCase()}</span></td>
                            <td>\${payment.received_by_name}</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                document.getElementById('paymentsTable').style.display = 'none';
                document.getElementById('noPayments').style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading payments:', error);
        }
    }

    // Edit student
    function editStudent() {
        window.location.href = 'edit.php?id=' + encodeURIComponent(studentIdValue);
    }

    // Record payment
    function recordPayment() {
        window.location.href = '../payments/record.php?student_id=' + encodeURIComponent(studentIdValue);
    }

    // Load on page load
    loadStudent();
</script>
EOT;

include dirname(__DIR__, 2) . '/includes/footer.php';
?>
