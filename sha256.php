<?php

// Utility to generate the value of SHA256 HMAC calculations, for comparison with other generators

// Get raw parameters
$key = $_GET['key'];
$message = $_GET['message'];

// Calculate SHA256 HMAC
$hmac = hash_hmac('sha256', $message, $key, FALSE);

// Echo output
echo $hmac;
exit;