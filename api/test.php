<?php
// Simple PHP test
header('Content-Type: application/json');
echo json_encode([
    'message' => 'PHP is working!',
    'time' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION
]);
