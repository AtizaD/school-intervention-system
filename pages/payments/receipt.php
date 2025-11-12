<?php
/**
 * Payment Receipt Page
 * Printable receipt for payments
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Get receipt number from URL
$receiptNumber = $_GET['receipt'] ?? '';

if (empty($receiptNumber)) {
    header('Location: index.php');
    exit;
}

// Get payment details
$paymentModel = new Payment();
$payment = $paymentModel->getByReceiptNumber($receiptNumber);

if (!$payment) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Payment Receipt - ' . $receiptNumber;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            .receipt-container {
                border: none !important;
                box-shadow: none !important;
            }
        }

        body {
            background-color: #f8f9fa;
            padding: 20px;
        }

        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border: 1px solid #dee2e6;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px solid #198754;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .receipt-header h1 {
            color: #198754;
            font-weight: bold;
            margin: 0;
        }

        .receipt-header .subtitle {
            color: #6c757d;
            margin-top: 5px;
        }

        .receipt-number {
            background: #198754;
            color: white;
            padding: 10px 20px;
            display: inline-block;
            border-radius: 5px;
            font-weight: bold;
            font-size: 1.1rem;
            margin: 15px 0;
        }

        .info-section {
            margin-bottom: 30px;
        }

        .info-section h5 {
            color: #198754;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px dotted #dee2e6;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: bold;
            min-width: 180px;
            color: #495057;
        }

        .info-value {
            flex: 1;
            color: #212529;
        }

        .amount-box {
            background: #f8f9fa;
            border: 2px solid #198754;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }

        .amount-box .label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .amount-box .amount {
            color: #198754;
            font-size: 2rem;
            font-weight: bold;
        }

        .balance-info {
            background: #e7f3ff;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin: 20px 0;
        }

        .balance-info h6 {
            color: #0d6efd;
            margin-bottom: 10px;
        }

        .receipt-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
            padding-top: 20px;
        }

        .signature-box {
            text-align: center;
            min-width: 200px;
        }

        .signature-line {
            border-top: 1px solid #212529;
            margin-top: 50px;
            padding-top: 5px;
        }

        @media print {
            @page {
                margin: 0.5cm;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="receipt-header">
            <h1><i class="fas fa-graduation-cap"></i> <?php echo APP_NAME; ?></h1>
            <div class="subtitle">School Money Collection System</div>
            <div class="receipt-number">
                <i class="fas fa-receipt"></i> Receipt: <?php echo htmlspecialchars($payment['receipt_number']); ?>
            </div>
        </div>

        <!-- Date and Time -->
        <div class="text-end mb-3">
            <small class="text-muted">
                <i class="fas fa-calendar"></i> Payment Date: <strong><?php echo formatDate($payment['payment_date']); ?></strong><br>
                <i class="fas fa-clock"></i> Recorded: <?php echo formatDate($payment['created_at'], DATETIME_FORMAT); ?>
            </small>
        </div>

        <!-- Student Information -->
        <div class="info-section">
            <h5><i class="fas fa-user-graduate"></i> Student Information</h5>
            <div class="info-row">
                <div class="info-label">Student ID:</div>
                <div class="info-value"><?php echo htmlspecialchars($payment['student_id']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Student Name:</div>
                <div class="info-value"><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Class:</div>
                <div class="info-value"><?php echo htmlspecialchars($payment['class']); ?></div>
            </div>
            <?php if ($payment['house']): ?>
            <div class="info-row">
                <div class="info-label">House:</div>
                <div class="info-value"><?php echo htmlspecialchars($payment['house']); ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Payment Details -->
        <div class="info-section">
            <h5><i class="fas fa-money-bill-wave"></i> Payment Details</h5>
            <div class="info-row">
                <div class="info-label">Payment Method:</div>
                <div class="info-value">
                    <span class="badge bg-<?php
                        echo $payment['payment_method'] === 'cash' ? 'success' :
                            ($payment['payment_method'] === 'mobile_money' ? 'info' : 'secondary');
                    ?>">
                        <?php echo strtoupper(str_replace('_', ' ', $payment['payment_method'])); ?>
                    </span>
                </div>
            </div>
            <?php if (!empty($payment['reference_number'])): ?>
            <div class="info-row">
                <div class="info-label">Reference Number:</div>
                <div class="info-value"><?php echo htmlspecialchars($payment['reference_number']); ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($payment['notes'])): ?>
            <div class="info-row">
                <div class="info-label">Notes:</div>
                <div class="info-value"><?php echo htmlspecialchars($payment['notes']); ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Amount Paid -->
        <div class="amount-box">
            <div class="label">AMOUNT PAID</div>
            <div class="amount"><?php echo formatCurrency($payment['amount_paid']); ?></div>
            <div class="label mt-2">(<?php echo ucwords(numberToWords($payment['amount_paid'])); ?> <?php echo CURRENCY_NAME; ?>)</div>
        </div>

        <!-- Fee Summary -->
        <div class="balance-info">
            <h6><i class="fas fa-calculator"></i> Fee Summary</h6>
            <div class="row text-center">
                <div class="col-4">
                    <small class="text-muted">Total Fee</small><br>
                    <strong><?php echo formatCurrency($payment['amount_due']); ?></strong>
                </div>
                <div class="col-4">
                    <small class="text-muted">Total Paid</small><br>
                    <strong class="text-success"><?php echo formatCurrency($payment['total_paid']); ?></strong>
                </div>
                <div class="col-4">
                    <small class="text-muted">Balance</small><br>
                    <strong class="<?php echo $payment['balance'] > 0 ? 'text-danger' : 'text-success'; ?>">
                        <?php echo formatCurrency($payment['balance']); ?>
                    </strong>
                </div>
            </div>
        </div>

        <!-- Received By -->
        <div class="info-section">
            <h5><i class="fas fa-user-check"></i> Received By</h5>
            <div class="info-row">
                <div class="info-label">Name:</div>
                <div class="info-value"><?php echo htmlspecialchars($payment['received_by_name']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Contact:</div>
                <div class="info-value"><?php echo htmlspecialchars($payment['received_by_contact']); ?></div>
            </div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Received By</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Authorized Signature</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="receipt-footer">
            <p><strong>Thank you for your payment!</strong></p>
            <p class="mb-0">
                <i class="fas fa-shield-alt"></i> This is an official receipt from <?php echo APP_NAME; ?><br>
                <small>For inquiries, please contact the school administration.</small>
            </p>
        </div>

        <!-- Print Button (No Print) -->
        <div class="text-center mt-4 no-print">
            <button onclick="window.print()" class="btn btn-success btn-lg me-2">
                <i class="fas fa-print"></i> Print Receipt
            </button>
            <button onclick="window.close()" class="btn btn-secondary btn-lg">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus for printing (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
