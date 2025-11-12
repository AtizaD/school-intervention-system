<?php
/**
 * SMS Class
 * Handles SMS sending via Africa's Talking API
 */

class SMS {
    private $apiKey;
    private $username;
    private $from;

    public function __construct() {
        // Get SMS settings from database
        $db = new Database();
        $db->query("SELECT setting_key, setting_value FROM settings WHERE category = 'sms'");
        $settings = $db->fetchAll();

        $smsConfig = [];
        foreach ($settings as $setting) {
            $smsConfig[$setting['setting_key']] = $setting['setting_value'];
        }

        $this->apiKey = $smsConfig['sms_api_key'] ?? '';
        $this->username = $smsConfig['sms_username'] ?? '';
        $this->from = $smsConfig['sms_sender_id'] ?? 'INTERVENTION';
    }

    /**
     * Send SMS
     */
    public function send($to, $message) {
        // Check if SMS is enabled
        if (empty($this->apiKey) || empty($this->username)) {
            return [
                'success' => false,
                'message' => 'SMS service not configured'
            ];
        }

        // Format phone number for Ghana
        $to = $this->formatPhoneNumber($to);

        if (!$to) {
            return [
                'success' => false,
                'message' => 'Invalid phone number format'
            ];
        }

        // Prepare API request
        $url = 'https://api.africastalking.com/version1/messaging';

        $data = [
            'username' => $this->username,
            'to' => $to,
            'message' => $message,
            'from' => $this->from
        ];

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'apiKey: ' . $this->apiKey,
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 201 || $httpCode === 200) {
                $result = json_decode($response, true);

                if (isset($result['SMSMessageData']['Recipients'][0]['status']) &&
                    $result['SMSMessageData']['Recipients'][0]['status'] === 'Success') {
                    return [
                        'success' => true,
                        'message' => 'SMS sent successfully',
                        'response' => $response
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Failed to send SMS',
                'response' => $response
            ];

        } catch (Exception $e) {
            error_log("SMS Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'SMS service error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send bulk SMS
     */
    public function sendBulk($recipients, $message) {
        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($recipients as $recipient) {
            $result = $this->send($recipient, $message);
            $results[] = [
                'recipient' => $recipient,
                'success' => $result['success'],
                'message' => $result['message']
            ];

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }

            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 seconds
        }

        return [
            'success' => $successCount > 0,
            'message' => "Sent {$successCount} SMS, {$failCount} failed",
            'total' => count($recipients),
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'results' => $results
        ];
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber($number) {
        // Remove all non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);

        // Ghana phone numbers are 10 digits starting with 0
        if (strlen($number) === 10 && substr($number, 0, 1) === '0') {
            // Replace leading 0 with country code +233
            return '+233' . substr($number, 1);
        }

        // If already in international format
        if (strlen($number) === 12 && substr($number, 0, 3) === '233') {
            return '+' . $number;
        }

        if (strlen($number) === 13 && substr($number, 0, 4) === '+233') {
            return $number;
        }

        // Invalid format
        return null;
    }

    /**
     * Get SMS balance (if supported by provider)
     */
    public function getBalance() {
        // This would call the API to get account balance
        // Implementation depends on Africa's Talking API
        return [
            'success' => true,
            'balance' => 'Check your Africa\'s Talking dashboard'
        ];
    }

    /**
     * Validate SMS configuration
     */
    public function isConfigured() {
        return !empty($this->apiKey) && !empty($this->username);
    }
}
