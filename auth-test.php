<?php
/**
 * Direct Auth Test
 * Test the auth endpoint directly without going through the main router
 */

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set JSON content type
header('Content-Type: application/json');

try {
    echo "Testing Auth Endpoint Directly\n";
    echo "==============================\n\n";
    
    // Test Login
    echo "TEST 1: Login\n";
    echo "-------------\n";
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/auth/login';
    
    // Set up the login request data
    $loginData = [
        'username' => 'testuser',
        'password' => 'password123'
    ];
    
    echo "Login data:\n";
    echo json_encode($loginData, JSON_PRETTY_PRINT) . "\n\n";
    
    // Simulate POST input
    $GLOBALS['HTTP_RAW_POST_DATA'] = json_encode($loginData);
    
    echo "Including auth.php for login...\n";
    
    // Capture output
    ob_start();
    $resource = 'auth';
    $pathSegments = ['auth', 'login'];
    require __DIR__ . '/api/routes/auth.php';
    $loginOutput = ob_get_clean();
    
    echo "Login response:\n";
    echo $loginOutput . "\n\n";
    
    // Test Registration  
    echo "TEST 2: Registration\n";
    echo "--------------------\n";
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/auth/register';
    
    // Set up the registration request data with ALL required fields
    $registerData = [
        'username' => 'newuser' . time(),
        'email' => 'newuser' . time() . '@example.com',
        'password' => 'password123',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'birthdate' => '2000-01-01',
        'gender' => 'Male',
        'student_no' => '2021-' . rand(10000, 99999),
        'year_level' => '4th Year',
        'college' => 'College of Engineering',
        'program' => 'Computer Science'
    ];
    
    echo "Registration data:\n";
    echo json_encode($registerData, JSON_PRETTY_PRINT) . "\n\n";
    
    // Simulate POST input
    $GLOBALS['HTTP_RAW_POST_DATA'] = json_encode($registerData);
    
    echo "Including auth.php for registration...\n";
    
    // Capture output
    ob_start();
    $resource = 'auth';
    $pathSegments = ['auth', 'register'];
    require __DIR__ . '/api/routes/auth.php';
    $registerOutput = ob_get_clean();
    
    echo "Registration response:\n";
    echo $registerOutput . "\n";
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Test failed',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT);
}
?>
