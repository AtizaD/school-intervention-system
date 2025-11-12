<?php
/**
 * Bulk Receipts PDF Generation
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Get receipt numbers from URL
$receiptsParam = $_GET['receipts'] ?? '';

if (empty($receiptsParam)) {
    die('Error: No receipts specified');
}

// Split comma-separated receipt numbers
$receiptNumbers = explode(',', $receiptsParam);
$receiptNumbers = array_filter(array_map('trim', $receiptNumbers));

if (empty($receiptNumbers)) {
    die('Error: No valid receipt numbers provided');
}

// Limit to 100 receipts per request
if (count($receiptNumbers) > 100) {
    die('Error: Maximum 100 receipts per batch. Please select fewer receipts.');
}

// Get payment details for all receipts
$paymentModel = new Payment();
$payments = [];

foreach ($receiptNumbers as $receiptNumber) {
    $payment = $paymentModel->getByReceiptNumber($receiptNumber);
    if ($payment) {
        $payments[] = $payment;
    }
}

if (empty($payments)) {
    die('Error: No valid receipts found');
}

// Build report data
$appName = APP_NAME;
$generatedOn = date('d/m/Y H:i:s');
$totalReceipts = count($payments);

// Generate HTML for all receipts
$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bulk Receipts</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        .receipt-page {
            page-break-after: always;
            margin-bottom: 20mm;
        }

        .receipt-page:last-child {
            page-break-after: auto;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px solid #198754;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .receipt-header h1 {
            color: #198754;
            font-size: 24px;
            margin: 0;
            font-weight: bold;
        }

        .receipt-header .subtitle {
            color: #666;
            margin-top: 5px;
            font-size: 11px;
        }

        .receipt-number {
            background: #198754;
            color: white;
            padding: 8px 16px;
            display: inline-block;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
            margin: 10px 0;
        }

        .date-section {
            text-align: right;
            margin-bottom: 15px;
            font-size: 10px;
            color: #666;
        }

        .info-section {
            margin-bottom: 20px;
        }

        .info-section h5 {
            color: #198754;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .info-row {
            display: flex;
            padding: 6px 0;
            border-bottom: 1px dotted #ddd;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: bold;
            min-width: 140px;
            color: #495057;
            font-size: 11px;
        }

        .info-value {
            flex: 1;
            color: #212529;
            font-size: 11px;
        }

        .amount-box {
            background: #f8f9fa;
            border: 2px solid #198754;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
        }

        .amount-box .label {
            color: #666;
            font-size: 10px;
            margin-bottom: 5px;
        }

        .amount-box .amount {
            color: #198754;
            font-size: 28px;
            font-weight: bold;
        }

        .amount-box .words {
            font-size: 10px;
            margin-top: 5px;
            font-style: italic;
        }

        .balance-info {
            background: #e7f3ff;
            border-left: 4px solid #0d6efd;
            padding: 12px;
            margin: 15px 0;
        }

        .balance-info h6 {
            color: #0d6efd;
            margin: 0 0 10px 0;
            font-size: 12px;
        }

        .balance-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            text-align: center;
        }

        .balance-item small {
            display: block;
            color: #666;
            font-size: 9px;
        }

        .balance-item strong {
            display: block;
            font-size: 12px;
            margin-top: 3px;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 15px;
        }

        .signature-box {
            text-align: center;
            min-width: 150px;
        }

        .signature-line {
            border-top: 1px solid #212529;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 10px;
        }

        .receipt-footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 10px;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-success {
            background: #198754;
            color: white;
        }

        .badge-info {
            background: #0dcaf0;
            color: black;
        }

        .badge-secondary {
            background: #6c757d;
            color: white;
        }

        .text-success {
            color: #198754 !important;
        }

        .text-danger {
            color: #dc2626 !important;
        }
    </style>
</head>
<body>';

// Generate each receipt
foreach ($payments as $index => $payment) {
    $html .= '<div class="receipt-page">';

    // Header
    $html .= '<div class="receipt-header">
        <h1>' . $appName . '</h1>
        <div class="subtitle">School Money Collection System</div>
        <div class="receipt-number">Receipt: ' . htmlspecialchars($payment['receipt_number']) . '</div>
    </div>';

    // Date
    $html .= '<div class="date-section">
        Payment Date: <strong>' . formatDate($payment['payment_date']) . '</strong><br>
        Recorded: ' . formatDate($payment['created_at'], DATETIME_FORMAT) . '
    </div>';

    // Student Information
    $html .= '<div class="info-section">
        <h5>Student Information</h5>
        <div class="info-row">
            <div class="info-label">Student ID:</div>
            <div class="info-value">' . htmlspecialchars($payment['student_id']) . '</div>
        </div>
        <div class="info-row">
            <div class="info-label">Student Name:</div>
            <div class="info-value">' . htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) . '</div>
        </div>
        <div class="info-row">
            <div class="info-label">Class:</div>
            <div class="info-value">' . htmlspecialchars($payment['class']) . '</div>
        </div>';

    if ($payment['house']) {
        $html .= '<div class="info-row">
            <div class="info-label">House:</div>
            <div class="info-value">' . htmlspecialchars($payment['house']) . '</div>
        </div>';
    }

    $html .= '</div>';

    // Payment Details
    $badgeClass = $payment['payment_method'] === 'cash' ? 'badge-success' :
                  ($payment['payment_method'] === 'mobile_money' ? 'badge-info' : 'badge-secondary');

    $html .= '<div class="info-section">
        <h5>Payment Details</h5>
        <div class="info-row">
            <div class="info-label">Payment Method:</div>
            <div class="info-value">
                <span class="badge ' . $badgeClass . '">' .
                    strtoupper(str_replace('_', ' ', $payment['payment_method'])) .
                '</span>
            </div>
        </div>';

    if (!empty($payment['reference_number'])) {
        $html .= '<div class="info-row">
            <div class="info-label">Reference Number:</div>
            <div class="info-value">' . htmlspecialchars($payment['reference_number']) . '</div>
        </div>';
    }

    if (!empty($payment['notes'])) {
        $html .= '<div class="info-row">
            <div class="info-label">Notes:</div>
            <div class="info-value">' . htmlspecialchars($payment['notes']) . '</div>
        </div>';
    }

    $html .= '</div>';

    // Amount Paid
    $html .= '<div class="amount-box">
        <div class="label">AMOUNT PAID</div>
        <div class="amount">' . formatCurrency($payment['amount_paid']) . '</div>
        <div class="words">(' . ucwords(numberToWords($payment['amount_paid'])) . ' ' . CURRENCY_NAME . ')</div>
    </div>';

    // Fee Summary
    $balanceClass = $payment['balance'] > 0 ? 'text-danger' : 'text-success';

    $html .= '<div class="balance-info">
        <h6>Fee Summary</h6>
        <div class="balance-grid">
            <div class="balance-item">
                <small>Total Fee</small>
                <strong>' . formatCurrency($payment['amount_due']) . '</strong>
            </div>
            <div class="balance-item">
                <small>Total Paid</small>
                <strong class="text-success">' . formatCurrency($payment['total_paid']) . '</strong>
            </div>
            <div class="balance-item">
                <small>Balance</small>
                <strong class="' . $balanceClass . '">' . formatCurrency($payment['balance']) . '</strong>
            </div>
        </div>
    </div>';

    // Received By
    $html .= '<div class="info-section">
        <h5>Received By</h5>
        <div class="info-row">
            <div class="info-label">Name:</div>
            <div class="info-value">' . htmlspecialchars($payment['received_by_name']) . '</div>
        </div>
        <div class="info-row">
            <div class="info-label">Contact:</div>
            <div class="info-value">' . htmlspecialchars($payment['received_by_contact']) . '</div>
        </div>
    </div>';

    // Signature Section
    $html .= '<div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">Received By</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Authorized Signature</div>
        </div>
    </div>';

    // Footer
    $html .= '<div class="receipt-footer">
        <strong>Thank you for your payment!</strong><br>
        This is an official receipt from ' . $appName . '<br>
        <small>For inquiries, please contact the school administration.</small>
    </div>';

    $html .= '</div>'; // End receipt-page
}

$html .= '</body></html>';

// Generate PDF
$tempHtml = tempnam(sys_get_temp_dir(), 'bulk_receipt_') . '.html';
$pdfFile = tempnam(sys_get_temp_dir(), 'bulk_receipt_') . '.pdf';

file_put_contents($tempHtml, $html);

$command = "wkhtmltopdf --page-size A4 --orientation Portrait --margin-top 10mm --margin-bottom 10mm --margin-left 10mm --margin-right 10mm " . escapeshellarg($tempHtml) . " " . escapeshellarg($pdfFile) . " 2>&1";
exec($command, $output, $returnCode);

// Clean up HTML file
unlink($tempHtml);

if ($returnCode !== 0 || !file_exists($pdfFile)) {
    error_log("Bulk receipt PDF generation failed: " . implode("\n", $output));
    die('Error: Failed to generate PDF receipts');
}

// Output PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="bulk_receipts_' . date('Y-m-d') . '.pdf"');
header('Content-Length: ' . filesize($pdfFile));
readfile($pdfFile);

// Clean up PDF file
unlink($pdfFile);
exit;
