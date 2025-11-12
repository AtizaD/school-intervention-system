<?php
/**
 * Parents List Page
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only admins can access parent management
if (!isAdmin()) {
    header('Location: ../dashboard.php');
    exit;
}

$pageTitle = 'Parents/Guardians';

// Get all parents
$db = new Database();
$db->query("SELECT p.*,
            COUNT(DISTINCT sp.student_id) as student_count,
            GROUP_CONCAT(DISTINCT CONCAT(s.first_name, ' ', s.last_name) SEPARATOR ', ') as students
            FROM parents p
            LEFT JOIN student_parents sp ON p.parent_id = sp.parent_id
            LEFT JOIN students s ON sp.student_id = s.student_id
            GROUP BY p.parent_id
            ORDER BY p.fullname ASC");
$parents = $db->fetchAll();

include dirname(__DIR__, 2) . '/includes/header.php';
include dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div id="alert-container"></div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-users"></i> Parents & Guardians
        </h5>
        <div>
            <button class="btn btn-primary" onclick="window.location.href='add.php'">
                <i class="fas fa-plus"></i> Add Parent
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="parentsTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Relationship</th>
                        <th>Contact</th>
                        <th>Students</th>
                        <th>Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($parents as $parent): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($parent['fullname']); ?></strong></td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo ucfirst($parent['relationship']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($parent['contact']); ?></td>
                            <td>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($parent['students'] ?: 'No students linked'); ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo $parent['student_count']; ?> student(s)
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-info" onclick="viewParent(<?php echo $parent['parent_id']; ?>)" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning" onclick="editParent(<?php echo $parent['parent_id']; ?>)" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if (isAdmin()): ?>
                                        <button class="btn btn-danger" onclick="deleteParent(<?php echo $parent['parent_id']; ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
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
$extraScripts = <<<'EOT'
<script>
    // Initialize DataTable
    let table = initDataTable('#parentsTable', {
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: -1 }
        ]
    });

    // View parent
    function viewParent(parentId) {
        window.location.href = 'view.php?id=' + parentId;
    }

    // Edit parent
    function editParent(parentId) {
        window.location.href = 'edit.php?id=' + parentId;
    }

    // Delete parent
    async function deleteParent(parentId) {
        if (!confirm('Are you sure you want to delete this parent/guardian? This will also remove all student associations.')) {
            return;
        }

        try {
            const response = await fetch('../../api/parents/delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ parent_id: parentId })
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
