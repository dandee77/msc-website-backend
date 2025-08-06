<?php
/**
 * Comprehensive API Check
 * Check all components of the API system
 */

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set JSON content type
header('Content-Type: application/json');

echo "<pre>";
echo "MSC API System Check\n";
echo "===================\n\n";

// 1. Check if all required files exist
echo "1. File Existence Check:\n";
$requiredFiles = [
    'api/index.php',
    'api/config/cors.php',
    'api/config/database.php',
    'api/controllers/AuthController.php',
    'api/models/StudentModel.php',
    'api/routes/auth.php',
    'api/utils/Response.php',
    'api/utils/Validator.php'
];

foreach ($requiredFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    $exists = file_exists($fullPath);
    echo "   {$file}: " . ($exists ? "✓ EXISTS" : "✗ MISSING") . "\n";
    if (!$exists) {
        echo "     Full path: {$fullPath}\n";
    }
}

echo "\n2. PHP Syntax Check:\n";
// Check main API files for syntax errors
$filesToCheck = [
    'api/index.php',
    'api/config/cors.php',
    'api/controllers/AuthController.php',
    'api/routes/auth.php'
];

foreach ($filesToCheck as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        $output = [];
        $return_var = 0;
        exec("php -l \"{$fullPath}\" 2>&1", $output, $return_var);
        
        if ($return_var === 0) {
            echo "   {$file}: ✓ SYNTAX OK\n";
        } else {
            echo "   {$file}: ✗ SYNTAX ERROR\n";
            echo "     " . implode("\n     ", $output) . "\n";
        }
    }
}

echo "\n3. Database Connection Test:\n";
try {
    require_once __DIR__ . '/api/config/database.php';
    $db = DatabaseConnection::getInstance()->getConnection();
    echo "   Database: ✓ CONNECTED\n";
    echo "   Host: " . $db->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "\n";
} catch (Exception $e) {
    echo "   Database: ✗ CONNECTION FAILED\n";
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n4. CORS Configuration Test:\n";
try {
    require_once __DIR__ . '/api/config/cors.php';
    echo "   CORS Config: ✓ LOADED\n";
} catch (Exception $e) {
    echo "   CORS Config: ✗ FAILED TO LOAD\n";
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n5. Request Information:\n";
echo "   Request Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "\n";
echo "   Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "   Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "   Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "   Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";

echo "\n6. Path Analysis:\n";
$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$pathSegments = explode('/', trim($path, '/'));
echo "   Raw Path: {$path}\n";
echo "   Path Segments: " . json_encode($pathSegments) . "\n";

$apiIndex = array_search('api', $pathSegments);
if ($apiIndex !== false) {
    $resource = $pathSegments[$apiIndex + 1] ?? '';
    echo "   API Index: {$apiIndex}\n";
    echo "   Resource: '{$resource}'\n";
} else {
    echo "   API Index: NOT FOUND\n";
}

echo "\n7. Test API Endpoint:\n";
try {
    // Simulate an API call
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/msc-website-backend-main/api/';
    
    ob_start();
    include __DIR__ . '/api/index.php';
    $apiOutput = ob_get_clean();
    
    echo "   API Response: " . (strlen($apiOutput) > 0 ? "✓ GOT RESPONSE" : "✗ NO RESPONSE") . "\n";
    echo "   Response Length: " . strlen($apiOutput) . " bytes\n";
    
    if (strlen($apiOutput) > 0) {
        echo "   Response Preview: " . substr($apiOutput, 0, 200) . "...\n";
        
        // Check if it's valid JSON
        $decoded = json_decode($apiOutput);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "   JSON Status: ✓ VALID JSON\n";
        } else {
            echo "   JSON Status: ✗ INVALID JSON\n";
            echo "   JSON Error: " . json_last_error_msg() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "   API Test: ✗ FAILED\n";
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n========================================\n";
echo "Check completed at: " . date('Y-m-d H:i:s') . "\n";
echo "</pre>";
?>
