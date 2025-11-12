<?php
/**
 * Students List Page
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

$pageTitle = 'Students';

// Get all students
$studentModel = new Student();
$students = $studentModel->getAll();

include dirname(__DIR__, 2) . '/includes/header.php';
include dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div id="alert-container"></div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-user-graduate"></i> Students List
        </h5>
        <?php if (isAdmin()): ?>
        <div>
            <button class="btn btn-primary" onclick="window.location.href='add.php'">
                <i class="fas fa-plus"></i> Add Student
            </button>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="studentsTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Class</th>
                        <th>House</th>
                        <th>Fees</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $student['gender'] == 'male' ? 'info' : 'pink'; ?>">
                                    <?php echo ucfirst($student['gender']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($student['class']); ?></td>
                            <td>
                                <span class="badge bg-secondary">House <?php echo $student['house']; ?></span>
                            </td>
                            <td><?php echo formatCurrency($student['amount_due'] ?? 0); ?></td>
                            <td>
                                <?php if ($student['balance'] > 0): ?>
                                    <span class="text-danger fw-bold"><?php echo formatCurrency($student['balance']); ?></span>
                                <?php else: ?>
                                    <span class="text-success">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($student['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-info" onclick="viewStudent('<?php echo $student['student_id']; ?>')" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if (isAdmin()): ?>
                                        <button class="btn btn-warning" onclick="editStudent('<?php echo $student['student_id']; ?>')" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-<?php echo $student['is_active'] ? 'secondary' : 'success'; ?>"
                                                onclick="toggleStatus('<?php echo $student['student_id']; ?>', <?php echo $student['is_active'] ? 'false' : 'true'; ?>)"
                                                title="<?php echo $student['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                            <i class="fas fa-power-off"></i>
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

<style>
    .bg-pink {
        background-color: #ec4899 !important;
        color: white;
    }

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

<?php
$extraScripts = <<<'EOT'
<script>
    // Initialize DataTable
    let table = initDataTable('#studentsTable', {
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: -1 }
        ]
    });

    // View student
    function viewStudent(studentId) {
        window.location.href = 'view.php?id=' + encodeURIComponent(studentId);
    }

    // Edit student
    function editStudent(studentId) {
        window.location.href = 'edit.php?id=' + encodeURIComponent(studentId);
    }

    // Toggle student status
    async function toggleStatus(studentId, activate) {
        const action = activate ? 'activate' : 'deactivate';

        if (!confirm(`Are you sure you want to ${action} this student?`)) {
            return;
        }

        try {
            const response = await fetch('../../api/students/toggle_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ student_id: studentId })
            });

            const result = await response.json();

            if (result.success) {
                showAlert(result.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(result.message, 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'danger');
        }
    }
</script>
EOT;

include dirname(__DIR__, 2) . '/includes/footer.php';
?>
