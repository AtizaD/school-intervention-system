<?php
/**
 * Bulk Receipt Selection Page
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

$pageTitle = 'Print Bulk Receipts';

// Get classes from database
$db = new Database();
$db->query("SELECT DISTINCT class FROM students WHERE is_active = 1 ORDER BY class");
$classes = $db->fetchAll();

include dirname(__DIR__, 2) . '/includes/header.php';
include dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div id="alert-container"></div>

<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">
            <i class="fas fa-print"></i> Print Bulk Receipts
        </h5>
    </div>
    <div class="card-body">
        <p class="text-muted">Select receipts to print in bulk using the filters below.</p>

        <!-- Filter Options -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" id="dateFrom">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" id="dateTo">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Filter by Class</label>
                <select class="form-control" id="classFilter">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $classRow): ?>
                        <option value="<?php echo htmlspecialchars($classRow['class']); ?>">
                            <?php echo htmlspecialchars($classRow['class']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Payment Method</label>
                <select class="form-control" id="methodFilter">
                    <option value="">All Methods</option>
                    <option value="cash">Cash</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <button class="btn btn-primary" onclick="loadReceipts()">
                    <i class="fas fa-search"></i> Search Receipts
                </button>
                <button class="btn btn-success ms-2" onclick="printSelected()" id="printBtn" disabled>
                    <i class="fas fa-print"></i> Print Selected (<span id="selectedCount">0</span>)
                </button>
                <button class="btn btn-secondary ms-2" onclick="selectAll()">
                    <i class="fas fa-check-double"></i> Select All
                </button>
                <button class="btn btn-secondary ms-2" onclick="deselectAll()">
                    <i class="fas fa-times"></i> Deselect All
                </button>
            </div>
        </div>

        <!-- Receipts Table -->
        <div id="resultsSection" style="display: none;">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Select the receipts you want to print, then click "Print Selected"
            </div>

            <div class="table-responsive">
                <table id="receiptsTable" class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()">
                            </th>
                            <th>Date</th>
                            <th>Receipt No.</th>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Received By</th>
                        </tr>
                    </thead>
                    <tbody id="receiptsBody"></tbody>
                </table>
            </div>
        </div>

        <div id="noResults" style="display: none;" class="text-center py-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <p class="text-muted">No receipts found. Try adjusting your filters.</p>
        </div>

        <div id="initialState" class="text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <p class="text-muted">Use the filters above to search for receipts</p>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<'EOT'
<script>
    // Set default dates (current month)
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    document.getElementById('dateFrom').valueAsDate = firstDay;
    document.getElementById('dateTo').valueAsDate = today;

    let allReceipts = [];
    let selectedReceipts = [];

    // Load receipts based on filters
    async function loadReceipts() {
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        const classFilter = document.getElementById('classFilter').value;
        const methodFilter = document.getElementById('methodFilter').value;

        if (!dateFrom || !dateTo) {
            showAlert('Please select a date range', 'warning');
            return;
        }

        try {
            const params = new URLSearchParams({
                date_from: dateFrom,
                date_to: dateTo
            });

            if (classFilter) params.append('class', classFilter);
            if (methodFilter) params.append('payment_method', methodFilter);

            const response = await fetch('../../api/payments/list.php?' + params.toString());
            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                allReceipts = result.data;
                displayReceipts(allReceipts);
                document.getElementById('resultsSection').style.display = 'block';
                document.getElementById('noResults').style.display = 'none';
                document.getElementById('initialState').style.display = 'none';
            } else {
                document.getElementById('resultsSection').style.display = 'none';
                document.getElementById('noResults').style.display = 'block';
                document.getElementById('initialState').style.display = 'none';
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Failed to load receipts', 'danger');
        }
    }

    // Display receipts in table
    function displayReceipts(receipts) {
        const tbody = document.getElementById('receiptsBody');
        tbody.innerHTML = '';

        receipts.forEach(payment => {
            const row = `
                <tr>
                    <td>
                        <input type="checkbox" class="receipt-checkbox"
                               value="${payment.receipt_number}"
                               onchange="updateSelectedCount()">
                    </td>
                    <td>${formatDate(payment.payment_date)}</td>
                    <td><code>${payment.receipt_number}</code></td>
                    <td>${payment.first_name} ${payment.last_name}</td>
                    <td>${payment.class}</td>
                    <td class="text-success fw-bold">${formatCurrency(payment.amount_paid)}</td>
                    <td><span class="badge bg-secondary">${payment.payment_method.replace('_', ' ').toUpperCase()}</span></td>
                    <td>${payment.received_by_name}</td>
                </tr>
            `;
            tbody.innerHTML += row;
        });

        updateSelectedCount();
    }

    // Toggle all checkboxes
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAllCheckbox').checked;
        document.querySelectorAll('.receipt-checkbox').forEach(cb => {
            cb.checked = selectAll;
        });
        updateSelectedCount();
    }

    // Select all
    function selectAll() {
        document.getElementById('selectAllCheckbox').checked = true;
        toggleSelectAll();
    }

    // Deselect all
    function deselectAll() {
        document.getElementById('selectAllCheckbox').checked = false;
        toggleSelectAll();
    }

    // Update selected count
    function updateSelectedCount() {
        selectedReceipts = Array.from(document.querySelectorAll('.receipt-checkbox:checked'))
            .map(cb => cb.value);

        document.getElementById('selectedCount').textContent = selectedReceipts.length;
        document.getElementById('printBtn').disabled = selectedReceipts.length === 0;
    }

    // Print selected receipts
    function printSelected() {
        if (selectedReceipts.length === 0) {
            showAlert('Please select at least one receipt', 'warning');
            return;
        }

        // Open PDF in new window
        const receiptParam = selectedReceipts.join(',');
        const url = '../../api/reports/bulk_receipts.php?receipts=' + encodeURIComponent(receiptParam);
        window.open(url, '_blank');
        showAlert(`Generating ${selectedReceipts.length} receipt(s)...`, 'info');
    }
</script>
EOT;

include dirname(__DIR__, 2) . '/includes/footer.php';
?>
