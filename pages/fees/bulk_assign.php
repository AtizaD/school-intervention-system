<?php
/**
 * Bulk Assign Fees Page
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only admins can assign fees
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Bulk Assign Fees';

// Get students
$studentModel = new Student();
$allStudents = $studentModel->getAll(['is_active' => 1]);

// Get classes from database
$db = new Database();
$db->query("SELECT DISTINCT class FROM students WHERE is_active = 1 ORDER BY class");
$classes = $db->fetchAll();

include dirname(__DIR__, 2) . '/includes/header.php';
include dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div id="alert-container"></div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-users"></i> Bulk Assign Fees
        </h5>
    </div>
    <div class="card-body">
        <form id="bulkAssignForm">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="amount" class="form-label">Amount Due (<?php echo CURRENCY_CODE; ?>) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="amount" name="amount"
                           step="0.01" min="0.01" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="due_date" name="due_date" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Filter Students <span class="text-danger">*</span></label>
                <div class="row">
                    <div class="col-md-4">
                        <select class="form-control" id="classFilter">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $classRow): ?>
                                <option value="<?php echo htmlspecialchars($classRow['class']); ?>"><?php echo htmlspecialchars($classRow['class']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="houseFilter">
                            <option value="">All Houses</option>
                            <option value="1">House 1</option>
                            <option value="2">House 2</option>
                            <option value="3">House 3</option>
                            <option value="4">House 4</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-info w-100" onclick="applyFilters()">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Select Students <span class="text-danger">*</span></label>
                    <div>
                        <button type="button" class="btn btn-sm btn-success" onclick="selectAll()">
                            <i class="fas fa-check-square"></i> Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="deselectAll()">
                            <i class="fas fa-square"></i> Deselect All
                        </button>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover">
                        <thead class="sticky-top bg-white">
                            <tr>
                                <th width="50">
                                    <input type="checkbox" id="selectAllCheckbox" onclick="toggleAll()">
                                </th>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>House</th>
                                <th>Has Fee</th>
                            </tr>
                        </thead>
                        <tbody id="studentsTableBody">
                            <?php foreach ($allStudents as $student): ?>
                                <tr class="student-row"
                                    data-class="<?php echo $student['class']; ?>"
                                    data-house="<?php echo $student['house']; ?>">
                                    <td>
                                        <input type="checkbox" class="student-checkbox" name="students[]"
                                               value="<?php echo $student['student_id']; ?>"
                                               <?php echo ($student['amount_due'] > 0) ? 'disabled' : ''; ?>>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['class']); ?></td>
                                    <td>House <?php echo $student['house']; ?></td>
                                    <td>
                                        <?php if ($student['amount_due'] > 0): ?>
                                            <span class="badge bg-success">Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mt-2">
                    <i class="fas fa-info-circle"></i>
                    Selected: <strong id="selectedCount">0</strong> students
                </div>
            </div>

            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Note:</strong> Students who already have fees assigned will be skipped automatically.
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Assign Fees
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$extraScripts = <<<'EOT'
<script>
    const form = document.getElementById('bulkAssignForm');
    const submitBtn = document.getElementById('submitBtn');
    const selectedCountEl = document.getElementById('selectedCount');

    // Set default due date to 30 days from now
    const today = new Date();
    today.setDate(today.getDate() + 30);
    document.getElementById('due_date').valueAsDate = today;

    // Apply filters
    function applyFilters() {
        const classFilter = document.getElementById('classFilter').value;
        const houseFilter = document.getElementById('houseFilter').value;

        const rows = document.querySelectorAll('.student-row');

        rows.forEach(row => {
            const rowClass = row.getAttribute('data-class');
            const rowHouse = row.getAttribute('data-house');

            let show = true;

            if (classFilter && rowClass !== classFilter) {
                show = false;
            }

            if (houseFilter && rowHouse !== houseFilter) {
                show = false;
            }

            row.style.display = show ? '' : 'none';
        });

        updateSelectedCount();
    }

    // Select all visible students
    function selectAll() {
        const checkboxes = document.querySelectorAll('.student-checkbox:not(:disabled)');
        checkboxes.forEach(cb => {
            if (cb.closest('.student-row').style.display !== 'none') {
                cb.checked = true;
            }
        });
        updateSelectedCount();
    }

    // Deselect all
    function deselectAll() {
        const checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(cb => cb.checked = false);
        updateSelectedCount();
    }

    // Toggle all
    function toggleAll() {
        const mainCheckbox = document.getElementById('selectAllCheckbox');
        const checkboxes = document.querySelectorAll('.student-checkbox:not(:disabled)');
        checkboxes.forEach(cb => {
            if (cb.closest('.student-row').style.display !== 'none') {
                cb.checked = mainCheckbox.checked;
            }
        });
        updateSelectedCount();
    }

    // Update selected count
    function updateSelectedCount() {
        const count = document.querySelectorAll('.student-checkbox:checked').length;
        selectedCountEl.textContent = count;
    }

    // Add change listener to all checkboxes
    document.querySelectorAll('.student-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    // Handle form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const selectedStudents = Array.from(document.querySelectorAll('.student-checkbox:checked'))
            .map(cb => cb.value);

        if (selectedStudents.length === 0) {
            showAlert('Please select at least one student', 'warning');
            return;
        }

        const amount = document.getElementById('amount').value;
        const dueDate = document.getElementById('due_date').value;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Assigning...';

        try {
            const response = await fetch('../../api/fees/bulk_assign.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    student_ids: selectedStudents,
                    amount: amount,
                    due_date: dueDate
                })
            });

            const result = await response.json();

            if (result.success) {
                showAlert(result.message, 'success');
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000);
            } else {
                showAlert(result.message, 'danger');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Assign Fees';
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Assign Fees';
        }
    });
</script>
EOT;

include dirname(__DIR__, 2) . '/includes/footer.php';
?>
