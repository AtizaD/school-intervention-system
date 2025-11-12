<?php
/**
 * Add Student Page
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only admins can add students
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Add Student';

include dirname(__DIR__, 2) . '/includes/header.php';
include dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div id="alert-container"></div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-user-plus"></i> Add New Student
        </h5>
    </div>
    <div class="card-body">
        <form id="addStudentForm">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required
                           pattern="[A-Za-z\s\-]+" title="Only letters, spaces, and hyphens allowed">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required
                           pattern="[A-Za-z\s\-]+" title="Only letters, spaces, and hyphens allowed">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="level" class="form-label">Level <span class="text-danger">*</span></label>
                    <select class="form-control" id="level" name="level" required>
                        <option value="">Select Level</option>
                        <option value="SHS 3">SHS 3</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="program_id" class="form-label">Program <span class="text-danger">*</span></label>
                    <select class="form-control" id="program_id" name="program_id" required>
                        <option value="">Loading programs...</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="class" class="form-label">Class <span class="text-danger">*</span></label>
                    <select class="form-control" id="class" name="class" required disabled>
                        <option value="">Select Level and Program first</option>
                    </select>
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> Select Level and Program to load classes
                    </small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                    <select class="form-control" id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
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

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="parent_fullname" class="form-label">Parent/Guardian Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="parent_fullname" name="parent_fullname" required
                           pattern="[A-Za-z\s\-.]+" title="Only letters, spaces, hyphens, and periods allowed">
                </div>

                <div class="col-md-6 mb-3">
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
                <div class="col-md-6 mb-3">
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
                    <i class="fas fa-save"></i> Save Student
                </button>
            </div>
        </form>
        </div>
    </div>

<?php
$extraScripts = <<<'EOT'
<script>
    const form = document.getElementById('addStudentForm');
    const submitBtn = document.getElementById('submitBtn');
    const levelSelect = document.getElementById('level');
    const programSelect = document.getElementById('program_id');
    const classSelect = document.getElementById('class');

    // Load programs on page load
    async function loadPrograms() {
        try {
            const response = await fetch('../../api/classes/get_programs.php');
            const result = await response.json();

            if (result.success) {
                programSelect.innerHTML = '<option value="">Select Program</option>';
                result.programs.forEach(program => {
                    const option = document.createElement('option');
                    option.value = program.program_id;
                    option.textContent = program.program_name;
                    programSelect.appendChild(option);
                });
            } else {
                programSelect.innerHTML = '<option value="">Failed to load programs</option>';
            }
        } catch (error) {
            console.error('Error loading programs:', error);
            programSelect.innerHTML = '<option value="">Error loading programs</option>';
        }
    }

    // Load classes when both level and program are selected
    async function loadClasses() {
        const level = levelSelect.value;
        const programId = programSelect.value;

        if (!level || !programId) {
            classSelect.innerHTML = '<option value="">Select Level and Program first</option>';
            classSelect.disabled = true;
            return;
        }

        classSelect.innerHTML = '<option value="">Loading classes...</option>';
        classSelect.disabled = true;

        try {
            const response = await fetch('../../api/classes/get_by_program_level.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    program_id: parseInt(programId),
                    level: level
                })
            });

            const result = await response.json();

            if (result.success && result.classes.length > 0) {
                classSelect.innerHTML = '<option value="">Select Class</option>';
                result.classes.forEach(classInfo => {
                    const option = document.createElement('option');
                    option.value = classInfo.class_name;
                    option.textContent = classInfo.class_name;
                    classSelect.appendChild(option);
                });
                classSelect.disabled = false;
            } else {
                classSelect.innerHTML = '<option value="">No classes available</option>';
                classSelect.disabled = true;
            }
        } catch (error) {
            console.error('Error loading classes:', error);
            classSelect.innerHTML = '<option value="">Error loading classes</option>';
            classSelect.disabled = true;
        }
    }

    // Event listeners
    levelSelect.addEventListener('change', loadClasses);
    programSelect.addEventListener('change', loadClasses);

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Get form data
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        // Separate student and parent data
        const studentData = {
            first_name: data.first_name,
            last_name: data.last_name,
            level: data.level,
            gender: data.gender,
            class: data.class,
            house: data.house
        };

        const parentData = {
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
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        try {
            const response = await fetch('../../api/students/create.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (result.success) {
                const successMsg = result.student_id
                    ? `${result.message} <br><strong>Student ID/Username: ${result.student_id}</strong>`
                    : result.message;
                showAlert(successMsg, 'success');
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000);
            } else {
                showAlert(result.message, 'danger');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Student';
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Student';
        }
    });

    // Load programs on page load
    loadPrograms();
</script>
EOT;

include dirname(__DIR__, 2) . '/includes/footer.php';
?>
