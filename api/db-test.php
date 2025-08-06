<?php
// Database connection test
header('Content-Type: application/json');

try {
    // Test basic database connection
    $host = "localhost";
    $dbname = "student_portal";
    $username = "root";
    $password = "";
    
    $dsn = "mysql:host={$host};dbname={$dbname}";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test if tables exist
    $tables = [];
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'database' => $dbname,
        'tables' => $tables,
        'table_count' => count($tables)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'General error',
        'error' => $e->getMessage()
    ]);
}
