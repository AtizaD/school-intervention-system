<?php
/**
 * Edit Student Page
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only admins can edit students
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Edit Student';

$studentId = $_GET['id'] ?? '';

if (empty($studentId)) {
    header('Location: index.php');
    exit;
}

// Get classes from database
$db = new Database();
$db->query("SELECT DISTINCT class FROM students WHERE is_active = 1 ORDER BY class");
$classes = $db->fetchAll();

include dirname(__DIR__, 2) . '/includes/header.php';
include dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div id="alert-container"></div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-edit"></i> Edit Student
                </h5>
            </div>
            <div class="card-body">
                <div id="loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading student data...</p>
                </div>

                <form id="editStudentForm" style="display: none;">
                    <input type="hidden" id="student_id" name="student_id" value="<?php echo htmlspecialchars($studentId); ?>">

                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label for="student_id_display" class="form-label">Student ID</label>
                            <input type="text" class="form-control" id="student_id_display" disabled>
                        </div>

                        <div class="col-md-6 col-sm-12 mb-3">
                            <label for="class" class="form-label">Class <span class="text-danger">*</span></label>
                            <select class="form-control" id="class" name="class" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $classRow): ?>
                                    <option value="<?php echo htmlspecialchars($classRow['class']); ?>"><?php echo htmlspecialchars($classRow['class']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>

                        <div class="col-md-6 col-sm-12 mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-control" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>

                        <div class="col-md-6 col-sm-12 mb-3">
                            <label for="house" class="form-label">House <span class="text-danger">*</span></label>
                            <select class="form-control" id="house" name="house" required>
                                <option value="">Select House</option>
                                <option value="1">House 1</option>
                                <option value="2">House 2</option>
                                <option value="3">House 3</option>
                                <option value="4">House 4</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3 text-primary"><i class="fas fa-users"></i> Parent/Guardian Information</h6>
                    <input type="hidden" id="parent_id" name="parent_id">

                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label for="parent_fullname" class="form-label">Parent/Guardian Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="parent_fullname" name="parent_fullname" required
                                   pattern="[A-Za-z\s\-.]+" title="Only letters, spaces, hyphens, and periods allowed">
                        </div>

                        <div class="col-md-6 col-sm-12 mb-3">
                            <label for="parent_relationship" class="form-label">Relationship <span class="text-danger">*</span></label>
                            <select class="form-control" id="parent_relationship" name="parent_relationship" required>
                                <option value="">Select Relationship</option>
                                <option value="father">Father</option>
                                <option value="mother">Mother</option>
                                <option value="guardian">Guardian</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label for="parent_contact" class="form-label">Parent/Guardian Contact <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="parent_contact" name="parent_contact" required
                                   pattern="[0-9+\-\s()]+" title="Only numbers, +, -, spaces, and parentheses allowed"
                                   placeholder="e.g., 0241234567 or +233241234567">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Enter a valid phone number
                            </small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i> Update Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<EOT
<style>
    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .col-lg-8 {
            max-width: 100%;
        }

        .card-header h5 {
            font-size: 1rem;
        }

        .form-label {
            font-size: 0.9rem;
        }

        .form-control {
            font-size: 0.9rem;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 0.5rem;
        }

        .d-flex.justify-content-between .btn {
            width: 100%;
        }
    }

    @media (max-width: 576px) {
        .card-body {
            padding: 1rem;
        }

        .form-label {
            font-size: 0.85rem;
        }

        .form-control {
            font-size: 0.85rem;
            padding: 0.5rem;
        }

        .btn {
            font-size: 0.85rem;
            padding: 0.5rem;
        }

        .row .col-md-6 {
            margin-bottom: 0.75rem !important;
        }
    }
</style>

<script>
    const form = document.getElementById('editStudentForm');
    const submitBtn = document.getElementById('submitBtn');
    const loading = document.getElementById('loading');
    const studentId = '{$studentId}';

    // Load student data
    async function loadStudent() {
        try {
            const response = await fetch('../../api/students/get.php?id=' + encodeURIComponent(studentId));
            const result = await response.json();

            if (result.success) {
                const student = result.data;

                // Populate student form
                document.getElementById('student_id_display').value = student.student_id;
                document.getElementById('first_name').value = student.first_name;
                document.getElementById('last_name').value = student.last_name;
                document.getElementById('gender').value = student.gender;
                document.getElementById('class').value = student.class;
                document.getElementById('house').value = student.house;

                // Populate parent form if parent data exists
                if (student.parent_id) {
                    document.getElementById('parent_id').value = student.parent_id;
                    document.getElementById('parent_fullname').value = student.parent_fullname || '';
                    document.getElementById('parent_relationship').value = student.parent_relationship || '';
                    document.getElementById('parent_contact').value = student.parent_contact || '';
                }

                // Show form
                loading.style.display = 'none';
                form.style.display = 'block';
            } else {
                showAlert(result.message, 'danger');
                setTimeout(() => window.location.href = 'index.php', 2000);
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Failed to load student data.', 'danger');
            setTimeout(() => window.location.href = 'index.php', 2000);
        }
    }

    // Handle form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Get form data
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        // Separate student and parent data
        const studentData = {
            student_id: data.student_id,
            first_name: data.first_name,
            last_name: data.last_name,
            gender: data.gender,
            class: data.class,
            house: data.house
        };

        const parentData = {
            parent_id: data.parent_id || null,
            fullname: data.parent_fullname,
            relationship: data.parent_relationship,
            contact: data.parent_contact
        };

        // Combine into final payload
        const payload = {
            student: studentData,
            parent: parentData
        };

        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

        try {
            const response = await fetch('../../api/students/update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
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
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Student';
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Student';
        }
    });

    // Load student on page load
    loadStudent();
</script>
EOT;

include dirname(__DIR__, 2) . '/includes/footer.php';
?>
