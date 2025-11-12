<?php
/**
 * Add Parent Page
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only admins can access parent management
if (!isAdmin()) {
    header('Location: ../dashboard.php');
    exit;
}

$pageTitle = 'Add Parent/Guardian';

// Get all students for linking
$studentModel = new Student();
$students = $studentModel->getAll();

include dirname(__DIR__, 2) . '/includes/header.php';
include dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div id="alert-container"></div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-user-plus"></i> Add New Parent/Guardian</h5>
        <a href="index.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
    <div class="card-body">
        <form id="addParentForm">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fullname" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="fullname" name="fullname" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="relationship" class="form-label">Relationship <span class="text-danger">*</span></label>
                    <select class="form-control" id="relationship" name="relationship" required>
                        <option value="">-- Select Relationship --</option>
                        <option value="father">Father</option>
                        <option value="mother">Mother</option>
                        <option value="guardian">Guardian</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="contact" class="form-label">Contact Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="contact" name="contact"
                           placeholder="0XXXXXXXXX" maxlength="10" required>
                    <small class="text-muted">10 digits starting with 0</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="occupation" class="form-label">Occupation</label>
                    <input type="text" class="form-control" id="occupation" name="occupation">
                </div>

                <div class="col-md-12 mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Link Students (Optional)</label>
                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                        <?php foreach ($students as $student): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="student_ids[]"
                                       value="<?php echo $student['student_id']; ?>"
                                       id="student_<?php echo $student['student_id']; ?>">
                                <label class="form-check-label" for="student_<?php echo $student['student_id']; ?>">
                                    <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                    <small class="text-muted">(<?php echo htmlspecialchars($student['class']); ?>)</small>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Parent
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$extraScripts = <<<'EOT'
<script>
    // Format contact number (digits only)
    document.getElementById('contact').addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Submit form
    document.getElementById('addParentForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = Object.fromEntries(formData);

        // Get selected student IDs
        const studentIds = [];
        document.querySelectorAll('input[name="student_ids[]"]:checked').forEach(cb => {
            studentIds.push(parseInt(cb.value));
        });
        data.student_ids = studentIds;

        // Remove the checkbox array from data
        delete data['student_ids[]'];

        try {
            const response = await fetch('../../api/parents/create.php', {
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
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'danger');
        }
    });
</script>
EOT;

include dirname(__DIR__, 2) . '/includes/footer.php';
?>
