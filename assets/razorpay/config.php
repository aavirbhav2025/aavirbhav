<?php
include '../forms/db.php'; 
// === Razorpay credentials & config ===
// TODO: replace with your real keys from Razorpay Dashboard.
define('RAZORPAY_KEY_ID',     'rzp_test_RDROwhMbTqO2iV');
define('RAZORPAY_KEY_SECRET', 'kYPO5BPKqoitRuvpUSSHjeiR');
define('CURRENCY', 'INR');

// Optional: only if you use webhooks (set the same value on Razorpay dashboard)
define('WEBHOOK_SECRET', 'set_a_random_long_string_here');

// Composer autoload for Razorpay PHP SDK
//   composer require razorpay/razorpay:^2
require _DIR_ . '/vendor/autoload.php';