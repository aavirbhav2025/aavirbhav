<?php
session_start();
require __DIR__ . '/config.php';
require __DIR__ .'/vendor/autoload.php';

use Razorpay\Api\Api;

header('Content-Type: application/json');

try {
    // Amount must come from session (set during registration)
    if (empty($_SESSION['registration']['amount'])) {
        throw new Exception('Amount not set in session. Please register first.');
    }
    $amountRupees = (int) $_SESSION['registration']['amount'];
    if ($amountRupees <= 0) {
        throw new Exception('Invalid amount.');
    }
    $amountPaise = $amountRupees * 100;

    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

    // Create order
    $order = $api->order->create([
        'amount'          => $amountPaise,
        'currency'        => CURRENCY,
        'receipt'         => 'rcpt_' . time(),
        'payment_capture' => 1, // auto-capture
        'notes'           => [
            'name'    => $_SESSION['registration']['name']    ?? '',
            'contact' => $_SESSION['registration']['contact'] ?? '',
            'email'   => $_SESSION['registration']['email']   ?? '',
        ]
    ]);

    echo json_encode($order->toArray());
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
