<?php
/**
 * PDF Generator Class
 * Handles PDF generation for receipts and reports
 */

class PDF {
    /**
     * Generate payment receipt
     */
    public function generateReceipt($paymentId) {
        // Get payment details
        $paymentModel = new Payment();
        $payment = $paymentModel->getById($paymentId);

        if (!$payment) {
            return [
                'success' => false,
                'message' => 'Payment not found'
            ];
        }

        // Get student details
        $studentModel = new Student();
        $student = $studentModel->getById($payment['student_id']);

        // Get fee details
        $feeModel = new Fee();
        $fee = $feeModel->getById($payment['fee_id']);

        // Create HTML for receipt
        $html = $this->getReceiptHTML($payment, $student, $fee);

        // Generate PDF filename
        $filename = 'receipt_' . $payment['receipt_no'] . '_' . date('Ymd') . '.pdf';
        $filepath = dirname(__DIR__) . '/uploads/receipts/' . $filename;

        // Ensure receipts directory exists
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        // Generate PDF using wkhtmltopdf (must be installed on server)
        $result = $this->htmlToPDF($html, $filepath);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Receipt generated successfully',
                'filename' => $filename,
                'filepath' => $filepath
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to generate PDF'
        ];
    }

    /**
     * Generate receipt HTML
     */
    private function getReceiptHTML($payment, $student, $fee) {
        $receiptNo = htmlspecialchars($payment['receipt_no']);
        $studentName = htmlspecialchars($student['first_name'] . ' ' . $student['last_name']);
        $studentClass = htmlspecialchars($student['class']);
        $feeType = htmlspecialchars($fee['fee_type']);
        $amount = number_format($payment['amount_paid'], 2);
        $paymentMethod = htmlspecialchars($payment['payment_method']);
        $paymentDate = date('d/m/Y H:i', strtotime($payment['payment_date']));
        $receivedBy = htmlspecialchars($payment['fullname'] ?? 'System');

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt - {$receiptNo}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .school-name {
            font-size: 28px;
            font-weight: bold;
            color: #0d6efd;
            margin: 0;
        }
        .receipt-title {
            font-size: 20px;
            color: #666;
            margin-top: 10px;
        }
        .receipt-no {
            font-size: 16px;
            color: #999;
            margin-top: 5px;
        }
        .info-section {
            margin: 30px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: bold;
            color: #666;
            flex: 0 0 40%;
        }
        .info-value {
            flex: 0 0 60%;
            text-align: right;
        }
        .amount-section {
            background: #f8f9fa;
            padding: 20px;
            margin: 30px 0;
            border-radius: 8px;
            border: 2px solid #0d6efd;
        }
        .amount-label {
            font-size: 18px;
            font-weight: bold;
            color: #666;
        }
        .amount-value {
            font-size: 32px;
            font-weight: bold;
            color: #0d6efd;
            text-align: right;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            text-align: center;
            color: #999;
            font-size: 12px;
        }
        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            border-top: 2px solid #333;
            padding-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="school-name">INTERVENTION SCHOOL</h1>
        <div class="receipt-title">PAYMENT RECEIPT</div>
        <div class="receipt-no">Receipt No: {$receiptNo}</div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Student Name:</span>
            <span class="info-value">{$studentName}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Class:</span>
            <span class="info-value">{$studentClass}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fee Type:</span>
            <span class="info-value">{$feeType}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Payment Method:</span>
            <span class="info-value">{$paymentMethod}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Payment Date:</span>
            <span class="info-value">{$paymentDate}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Received By:</span>
            <span class="info-value">{$receivedBy}</span>
        </div>
    </div>

    <div class="amount-section">
        <div class="amount-label">Amount Paid:</div>
        <div class="amount-value">GHS {$amount}</div>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <strong>Received By</strong>
        </div>
        <div class="signature-box">
            <strong>Authorized Signature</strong>
        </div>
    </div>

    <div class="footer">
        <p>This is an official receipt from INTERVENTION SCHOOL</p>
        <p>Generated on: {$paymentDate}</p>
        <p>Thank you for your payment!</p>
    </div>
</body>
</html>
HTML;

        return $html;
    }

    /**
     * Convert HTML to PDF using wkhtmltopdf
     */
    private function htmlToPDF($html, $outputPath) {
        // Create temporary HTML file
        $tempHtml = sys_get_temp_dir() . '/receipt_' . uniqid() . '.html';
        file_put_contents($tempHtml, $html);

        // Use wkhtmltopdf to convert HTML to PDF
        $command = "wkhtmltopdf --page-size A4 --margin-top 10mm --margin-bottom 10mm --margin-left 10mm --margin-right 10mm {$tempHtml} {$outputPath} 2>&1";

        exec($command, $output, $returnCode);

        // Clean up temp file
        unlink($tempHtml);

        // Check if PDF was created successfully
        if ($returnCode === 0 && file_exists($outputPath)) {
            return true;
        }

        // Log error
        error_log("PDF Generation Error: " . implode("\n", $output));
        return false;
    }

    /**
     * Generate payment summary report
     */
    public function generatePaymentReport($dateFrom, $dateTo, $class = null) {
        $paymentModel = new Payment();
        $payments = $paymentModel->getByDateRange($dateFrom, $dateTo, $class);

        $html = $this->getPaymentReportHTML($payments, $dateFrom, $dateTo, $class);

        $filename = 'payment_report_' . date('Ymd_His') . '.pdf';
        $filepath = dirname(__DIR__) . '/uploads/reports/' . $filename;

        // Ensure reports directory exists
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $result = $this->htmlToPDF($html, $filepath);

        if ($result) {
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to generate report'
        ];
    }

    /**
     * Generate payment report HTML
     */
    private function getPaymentReportHTML($payments, $dateFrom, $dateTo, $class) {
        $totalAmount = array_sum(array_column($payments, 'amount_paid'));
        $dateFromFormatted = date('d/m/Y', strtotime($dateFrom));
        $dateToFormatted = date('d/m/Y', strtotime($dateTo));
        $classFilter = $class ? " - Class: {$class}" : " - All Classes";

        $rows = '';
        foreach ($payments as $payment) {
            $date = date('d/m/Y', strtotime($payment['payment_date']));
            $amount = number_format($payment['amount_paid'], 2);
            $rows .= "<tr>
                <td>{$date}</td>
                <td>{$payment['receipt_no']}</td>
                <td>{$payment['student_name']}</td>
                <td>{$payment['class']}</td>
                <td>{$payment['fee_type']}</td>
                <td>{$payment['payment_method']}</td>
                <td style='text-align: right;'>GHS {$amount}</td>
            </tr>";
        }

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Summary Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .school-name { font-size: 24px; font-weight: bold; color: #0d6efd; }
        .report-title { font-size: 18px; margin-top: 10px; }
        .report-period { color: #666; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #0d6efd; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .total-row { background-color: #d4edda; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; color: #999; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">INTERVENTION SCHOOL</div>
        <div class="report-title">Payment Summary Report</div>
        <div class="report-period">Period: {$dateFromFormatted} to {$dateToFormatted}{$classFilter}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Receipt No</th>
                <th>Student</th>
                <th>Class</th>
                <th>Fee Type</th>
                <th>Method</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            {$rows}
            <tr class="total-row">
                <td colspan="6" style="text-align: right;">TOTAL:</td>
                <td style="text-align: right;">GHS " . number_format($totalAmount, 2) . "</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on: " . date('d/m/Y H:i') . "</p>
        <p>Total Payments: " . count($payments) . " | Total Amount: GHS " . number_format($totalAmount, 2) . "</p>
    </div>
</body>
</html>
HTML;

        return $html;
    }
}
