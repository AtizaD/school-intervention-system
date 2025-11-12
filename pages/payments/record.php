<?php
/**
 * Record Payment Page
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

$pageTitle = 'Record Payment';

$preSelectedStudent = $_GET['student_id'] ?? '';

// Get students with fees
$feeModel = new Fee();
$fees = $feeModel->getAll();

// Add Select2 CSS
$extraStyles = <<<'EOT'
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
EOT;

include dirname(__DIR__, 2) . '/includes/header.php';
include dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div id="alert-container"></div>

<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">
            <i class="fas fa-money-bill-wave"></i> Record Payment
        </h5>
    </div>
    <div class="card-body">
        <form id="recordPaymentForm">
            <div class="mb-3">
                <label for="student_id" class="form-label">Select Student <span class="text-danger">*</span></label>
                <select class="form-control" id="student_id" name="student_id" required>
                    <option value="">-- Select Student --</option>
                    <?php foreach ($fees as $fee): ?>
                        <?php if ($fee['balance'] > 0): ?>
                            <option value="<?php echo $fee['student_id']; ?>"
                                    <?php echo ($fee['student_id'] === $preSelectedStudent) ? 'selected' : ''; ?>
                                    data-balance="<?php echo $fee['balance']; ?>"
                                    data-name="<?php echo htmlspecialchars($fee['first_name'] . ' ' . $fee['last_name']); ?>"
                                    data-class="<?php echo htmlspecialchars($fee['class']); ?>"
                                    data-due="<?php echo $fee['amount_due']; ?>"
                                    data-paid="<?php echo $fee['amount_paid']; ?>">
                                <?php echo htmlspecialchars($fee['student_id'] . ' - ' . $fee['first_name'] . ' ' . $fee['last_name'] . ' (' . $fee['class'] . ')'); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Only students with outstanding balances are shown</small>
            </div>

            <div id="feeInfo" class="alert alert-info" style="display: none;">
                <h6><strong>Fee Information:</strong></h6>
                <div class="row">
                    <div class="col-md-4">
                        <small>Amount Due:</small><br>
                        <strong id="infoAmountDue">-</strong>
                    </div>
                    <div class="col-md-4">
                        <small>Amount Paid:</small><br>
                        <strong class="text-success" id="infoAmountPaid">-</strong>
                    </div>
                    <div class="col-md-4">
                        <small>Balance:</small><br>
                        <strong class="text-danger" id="infoBalance">-</strong>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 col-sm-12 mb-3">
                    <label for="amount_paid" class="form-label">Amount to Pay (<?php echo CURRENCY_CODE; ?>) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="amount_paid" name="amount_paid"
                           step="0.01" min="0.01" required>
                    <small class="text-muted">Maximum: <span id="maxAmount">-</span></small>
                </div>

                <div class="col-md-6 col-sm-12 mb-3">
                    <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="payment_date" name="payment_date" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 col-sm-12 mb-3">
                    <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                    <select class="form-control" id="payment_method" name="payment_method" required>
                        <option value="">-- Select Method --</option>
                        <option value="cash">Cash</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="others">Others</option>
                    </select>
                </div>

                <div class="col-md-6 col-sm-12 mb-3">
                    <label for="reference_number" class="form-label">Reference Number</label>
                    <input type="text" class="form-control" id="reference_number" name="reference_number"
                           placeholder="Optional">
                </div>
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"
                          placeholder="Optional notes about this payment"></textarea>
            </div>

            <div class="alert alert-success">
                <i class="fas fa-info-circle"></i>
                <strong>Receipt:</strong> A receipt will be automatically generated after recording the payment.
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <button type="submit" class="btn btn-success" id="submitBtn">
                    <i class="fas fa-save"></i> Record Payment
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$extraScripts = <<<'EOT'
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    const form = document.getElementById('recordPaymentForm');
    const submitBtn = document.getElementById('submitBtn');
    const studentSelect = document.getElementById('student_id');
    const feeInfo = document.getElementById('feeInfo');
    const amountInput = document.getElementById('amount_paid');

    // Initialize Select2 for searchable dropdown
    $(document).ready(function() {
        $('#student_id').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Search for a student --',
            allowClear: true,
            width: '100%',
            dropdownAutoWidth: true
        });
    });

    // Set today's date
    document.getElementById('payment_date').valueAsDate = new Date();

    // Show fee information when student selected (using jQuery for Select2 compatibility)
    $('#student_id').on('change', function() {
        const value = $(this).val();
        if (value) {
            const option = this.options[this.selectedIndex];
            const balance = parseFloat(option.getAttribute('data-balance'));
            const amountDue = parseFloat(option.getAttribute('data-due'));
            const amountPaid = parseFloat(option.getAttribute('data-paid'));

            document.getElementById('infoAmountDue').textContent = formatCurrency(amountDue);
            document.getElementById('infoAmountPaid').textContent = formatCurrency(amountPaid);
            document.getElementById('infoBalance').textContent = formatCurrency(balance);
            document.getElementById('maxAmount').textContent = formatCurrency(balance);

            $('#feeInfo').show();

            // Set max attribute for amount input
            amountInput.max = balance;
            amountInput.value = balance; // Pre-fill with full balance
        } else {
            $('#feeInfo').hide();
            amountInput.max = '';
            amountInput.value = '';
        }
    });

    // Trigger if pre-selected
    $(document).ready(function() {
        if ($('#student_id').val()) {
            $('#student_id').trigger('change');
        }
    });

    // Handle form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        // Validate amount
        const maxAmount = parseFloat(amountInput.max);
        const amount = parseFloat(data.amount_paid);

        if (amount > maxAmount) {
            showAlert('Amount cannot exceed the outstanding balance of ' + formatCurrency(maxAmount), 'danger');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Recording...';

        try {
            const response = await fetch('../../api/payments/record.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                showAlert(result.message + ' Receipt No: ' + result.data.receipt_number, 'success');

                // Open receipt in new tab
                if (result.data.receipt_number) {
                    setTimeout(() => {
                        window.open('receipt.php?receipt=' + result.data.receipt_number, '_blank');
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                }
            } else {
                showAlert(result.message, 'danger');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Record Payment';
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Record Payment';
        }
    });
</script>

<style>
    /* Select2 Custom Styles */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }

    .select2-container--bootstrap-5 .select2-selection--single {
        padding: 0.375rem 0.75rem;
    }

    .select2-container--bootstrap-5 .select2-dropdown {
        border-color: #dee2e6;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
    }

    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color: #4f46e5;
    }

    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        /* Select2 mobile adjustments */
        .select2-container--bootstrap-5 .select2-selection {
            font-size: 0.9rem;
        }

        .select2-dropdown {
            font-size: 0.9rem;
        }
        .card-header h5 {
            font-size: 1rem;
        }

        .form-label {
            font-size: 0.9rem;
        }

        .form-control,
        .form-select {
            font-size: 0.9rem;
            padding: 0.5rem;
        }

        .btn {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }

        .alert {
            font-size: 0.85rem;
            padding: 0.75rem;
        }

        #feeInfo {
            font-size: 0.85rem;
        }

        #feeInfo h6 {
            font-size: 0.95rem;
        }

        #feeInfo strong {
            font-size: 0.9rem;
        }

        small {
            font-size: 0.8rem;
        }
    }

    @media (max-width: 576px) {
        /* Select2 extra small devices */
        .select2-container--bootstrap-5 .select2-selection {
            font-size: 0.85rem;
            padding: 0.4rem;
        }

        .select2-dropdown {
            font-size: 0.85rem;
        }

        .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
            font-size: 0.85rem;
            padding: 0.4rem;
        }

        .card-header h5 {
            font-size: 0.95rem;
        }

        .form-label {
            font-size: 0.85rem;
        }

        .form-control,
        .form-select {
            font-size: 0.85rem;
            padding: 0.4rem;
        }

        .btn {
            font-size: 0.85rem;
            padding: 0.4rem 0.8rem;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
        }

        .d-flex.justify-content-between .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .alert {
            font-size: 0.8rem;
        }

        #feeInfo .row .col-md-4 {
            margin-bottom: 0.75rem;
        }

        small {
            font-size: 0.75rem;
        }
    }
</style>
EOT;

include dirname(__DIR__, 2) . '/includes/footer.php';
?>
