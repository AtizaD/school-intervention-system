<?php
/**
 * Settings Page (Admin Only)
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only admins can access
if (!isAdmin()) {
    header('Location: ../dashboard.php');
    exit;
}

$pageTitle = 'Settings';

// Get all users
$userModel = new User();
$users = $userModel->getAll();

// Get system settings
$db = new Database();
$db->query("SELECT * FROM settings ORDER BY category, setting_key");
$settings = $db->fetchAll();

// Group settings by category
$settingsByCategory = [];
foreach ($settings as $setting) {
    $category = $setting['category'] ?? 'general';
    $settingsByCategory[$category][] = $setting;
}

include dirname(__DIR__, 2) . '/includes/header.php';
include dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div id="alert-container"></div>

<!-- User Management Section -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-users-cog"></i> User Management</h5>
        <button class="btn btn-light btn-sm" onclick="showAddUserModal()">
            <i class="fas fa-plus"></i> Add User
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Contact</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($user['fullname']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['contact']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'info'; ?>">
                                    <?php echo strtoupper($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php echo $user['last_login'] ? formatDate($user['last_login'], DATETIME_FORMAT) : 'Never'; ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($user['user_id'] != getCurrentUserId()): ?>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-warning" onclick="resetPassword(<?php echo $user['user_id']; ?>)" title="Reset Password">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button class="btn btn-<?php echo $user['is_active'] ? 'secondary' : 'success'; ?>"
                                                onclick="toggleUserStatus(<?php echo $user['user_id']; ?>)"
                                                title="<?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-info">Current User</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- System Settings Section -->
<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-cog"></i> System Settings</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php
            $categoryColors = [
                'general' => 'primary',
                'fees' => 'success',
                'notifications' => 'info',
                'payments' => 'warning',
                'security' => 'danger'
            ];

            foreach ($settingsByCategory as $category => $categorySettings):
                $color = $categoryColors[$category] ?? 'secondary';
            ?>
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-<?php echo $color; ?> bg-opacity-10 border-<?php echo $color; ?> border-start border-4">
                            <h6 class="mb-0 text-<?php echo $color; ?>">
                                <i class="fas fa-<?php
                                    echo $category === 'general' ? 'cog' :
                                        ($category === 'fees' ? 'money-bill-wave' :
                                        ($category === 'notifications' ? 'bell' :
                                        ($category === 'payments' ? 'credit-card' : 'shield-alt')));
                                ?>"></i>
                                <?php echo ucfirst($category); ?> Settings
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($categorySettings as $setting): ?>
                                <div class="mb-3">
                                    <label class="form-label mb-1">
                                        <strong><?php echo ucwords(str_replace('_', ' ', $setting['setting_key'])); ?></strong>
                                        <?php if ($setting['description']): ?>
                                            <i class="fas fa-info-circle text-muted ms-1"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="<?php echo htmlspecialchars($setting['description']); ?>"
                                               style="cursor: help;"></i>
                                        <?php endif; ?>
                                    </label>
                                    <?php if ($setting['setting_type'] === 'boolean'): ?>
                                        <select class="form-select form-select-sm" data-setting="<?php echo $setting['setting_key']; ?>">
                                            <option value="true" <?php echo $setting['setting_value'] === 'true' ? 'selected' : ''; ?>>Enabled</option>
                                            <option value="false" <?php echo $setting['setting_value'] === 'false' ? 'selected' : ''; ?>>Disabled</option>
                                        </select>
                                    <?php else: ?>
                                        <input type="<?php echo $setting['setting_type'] === 'number' ? 'number' : 'text'; ?>"
                                               class="form-control form-control-sm"
                                               data-setting="<?php echo $setting['setting_key']; ?>"
                                               value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                                               <?php echo $setting['setting_type'] === 'number' ? 'step="0.01" min="0"' : ''; ?>>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-end mt-3">
            <button class="btn btn-success btn-lg" onclick="saveSettings()">
                <i class="fas fa-save"></i> Save All Settings
            </button>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<'EOT'
<script>
    let addUserModal, resetPasswordModal;

    document.addEventListener('DOMContentLoaded', function() {
        addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
        resetPasswordModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));

        // Initialize all tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Show add user modal
    function showAddUserModal() {
        document.getElementById('addUserForm').reset();
        addUserModal.show();
    }

    // Submit add user form
    async function submitAddUser() {
        const form = document.getElementById('addUserForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        // Basic validation
        if (!data.fullname || !data.contact || !data.password || !data.role) {
            showAlert('Please fill all required fields', 'danger');
            return;
        }

        try {
            const response = await fetch('../../api/users/create.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                showAlert(result.message, 'success');
                addUserModal.hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(result.message, 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'danger');
        }
    }

    // Reset user password
    function resetPassword(userId) {
        document.getElementById('resetUserId').value = userId;
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';
        resetPasswordModal.show();
    }

    // Submit password reset
    async function submitPasswordReset() {
        const userId = document.getElementById('resetUserId').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (!newPassword || !confirmPassword) {
            showAlert('Please fill both password fields', 'danger');
            return;
        }

        if (newPassword !== confirmPassword) {
            showAlert('Passwords do not match', 'danger');
            return;
        }

        if (newPassword.length < 8) {
            showAlert('Password must be at least 8 characters', 'danger');
            return;
        }

        try {
            const response = await fetch('../../api/users/reset_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: userId,
                    new_password: newPassword
                })
            });

            const result = await response.json();

            if (result.success) {
                showAlert(result.message, 'success');
                resetPasswordModal.hide();
            } else {
                showAlert(result.message, 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'danger');
        }
    }

    // Toggle user status
    async function toggleUserStatus(userId) {
        if (!confirm('Are you sure you want to change this user\'s status?')) {
            return;
        }

        try {
            const response = await fetch('../../api/users/toggle_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId })
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

    // Save settings
    async function saveSettings() {
        const settings = {};

        document.querySelectorAll('[data-setting]').forEach(input => {
            settings[input.dataset.setting] = input.value;
        });

        try {
            const response = await fetch('../../api/settings/update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ settings: settings })
            });

            const result = await response.json();

            if (result.success) {
                showAlert(result.message, 'success');
            } else {
                showAlert(result.message, 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'danger');
        }
    }

    // Format contact number (digits only)
    document.addEventListener('DOMContentLoaded', function() {
        const contactInput = document.getElementById('contact');
        if (contactInput) {
            contactInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    });
</script>
EOT;
?>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Add New User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label for="fullname" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="fullname" name="fullname" required>
                    </div>

                    <div class="mb-3">
                        <label for="contact" class="form-label">Contact Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="contact" name="contact"
                               placeholder="0XXXXXXXXX" maxlength="10" required>
                        <small class="text-muted">10 digits starting with 0</small>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">Min 8 characters, uppercase, lowercase, number & special character</small>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="">-- Select Role --</option>
                            <option value="admin">Admin</option>
                            <option value="teacher">Teacher</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitAddUser()">
                    <i class="fas fa-save"></i> Create User
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-key"></i> Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="resetUserId">
                <div class="mb-3">
                    <label for="newPassword" class="form-label">New Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="newPassword" required>
                    <small class="text-muted">Min 8 characters, uppercase, lowercase, number & special character</small>
                </div>
                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="confirmPassword" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="submitPasswordReset()">
                    <i class="fas fa-save"></i> Reset Password
                </button>
            </div>
        </div>
    </div>
</div>

<?php
include dirname(__DIR__, 2) . '/includes/footer.php';
?>
