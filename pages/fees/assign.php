<?php
/**
 * Assign Fee Page
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only admins can assign fees
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Assign Fee';

$preSelectedStudent = $_GET['student_id'] ?? '';

// Get students without fees
$studentModel = new Student();
$allStudents = $studentModel->getAll(['is_active' => 1]);

include dirname(__DIR__, 2) . '/includes/header.php';
include dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div id="alert-container"></div>

<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file-invoice-dollar"></i> Assign Fee to Student
                </h5>
            </div>
            <div class="card-body">
                <form id="assignFeeForm">
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Select Student <span class="text-danger">*</span></label>
                        <select class="form-control" id="student_id" name="student_id" required>
                            <option value="">-- Select Student --</option>
                            <?php foreach ($allStudents as $student): ?>
                                <option value="<?php echo $student['student_id']; ?>"
                                        <?php echo ($student['student_id'] === $preSelectedStudent) ? 'selected' : ''; ?>
                                        data-class="<?php echo htmlspecialchars($student['class']); ?>">
                                    <?php echo htmlspecialchars($student['student_id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['class'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="studentInfo" class="alert alert-info" style="display: none;">
                        <strong>Student:</strong> <span id="infoName"></span><br>
                        <strong>Class:</strong> <span id="infoClass"></span>
                    </div>

                    <div class="mb-3">
                        <label for="amount_due" class="form-label">Amount Due (<?php echo CURRENCY_CODE; ?>) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="amount_due" name="amount_due"
                               step="0.01" min="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="due_date" name="due_date" required>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> If a student already has an assigned fee, this will fail. Use the edit option instead.
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i> Assign Fee
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<'EOT'
<script>
    const form = document.getElementById('assignFeeForm');
    const submitBtn = document.getElementById('submitBtn');
    const studentSelect = document.getElementById('student_id');
    const studentInfo = document.getElementById('studentInfo');

    // Show student info when selected
    studentSelect.addEventListener('change', function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            const text = selectedOption.textContent;
            const className = selectedOption.getAttribute('data-class');

            document.getElementById('infoName').textContent = text;
            document.getElementById('infoClass').textContent = className;
            studentInfo.style.display = 'block';
        } else {
            studentInfo.style.display = 'none';
        }
    });

    // Trigger if pre-selected
    if (studentSelect.value) {
        studentSelect.dispatchEvent(new Event('change'));
    }

    // Set default due date to 30 days from now
    const today = new Date();
    today.setDate(today.getDate() + 30);
    document.getElementById('due_date').valueAsDate = today;

    // Handle form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Assigning...';

        try {
            const response = await fetch('../../api/fees/assign.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                showAlert(result.message, 'success');
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 1500);
            } else {
                showAlert(result.message, 'danger');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Assign Fee';
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Assign Fee';
        }
    });
</script>
EOT;

include dirname(__DIR__, 2) . '/includes/footer.php';
?>
