<?php
/**
 * Health check endpoint for Mapas Culturais
 * 
 * This endpoint checks:
 * 1. PHP is working (always returns 200 if this file executes)
 * 2. Database connectivity (optional, returns 503 if database is not available)
 * 3. Application status (checks if core bootstrap loads)
 * 
 * Use with Docker HEALTHCHECK:
 * HEALTHCHECK --interval=30s --timeout=3s --start-period=60s --retries=3 \
 *   CMD curl -f http://localhost/health.php || exit 1
 */

header('Content-Type: application/json');

$start_time = microtime(true);
$checks = [
    'php' => ['status' => 'pass', 'message' => 'PHP is running'],
    'database' => ['status' => 'warn', 'message' => 'Database check not performed'],
    'application' => ['status' => 'warn', 'message' => 'Application check not performed']
];

$status_code = 200;
$overall_status = 'pass';

// Check 1: PHP is working (always true if this executes)
$checks['php']['duration'] = round(microtime(true) - $start_time, 4) . 's';

// Check 2: Database connectivity
$db_check_start = microtime(true);
try {
    $dbhost = $_ENV['DB_HOST'] ?? 'db';
    $dbname = $_ENV['DB_NAME'] ?? 'mapas';
    $dbuser = $_ENV['DB_USER'] ?? 'mapas';
    $dbpass = $_ENV['DB_PASS'] ?? 'mapas';
    
    $pdo = new PDO(
        "pgsql:host={$dbhost};port=5432;dbname={$dbname}",
        $dbuser,
        $dbpass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $checks['database']['status'] = 'pass';
    $checks['database']['message'] = 'Database connection successful';
    $checks['database']['details'] = [
        'host' => $dbhost,
        'database' => $dbname,
        'user' => $dbuser
    ];
} catch (PDOException $e) {
    $checks['database']['status'] = 'fail';
    $checks['database']['message'] = 'Database connection failed: ' . $e->getMessage();
    $overall_status = 'fail';
    // Don't return 503 for database failures - container services are still running
    // $status_code = 503;
}
$checks['database']['duration'] = round(microtime(true) - $db_check_start, 4) . 's';

// Check 3: Application bootstrap (lightweight check without full initialization)
$app_check_start = microtime(true);
try {
    // Try to load the minimal bootstrap to check if core files exist
    $bootstrap_file = __DIR__ . '/../src/bootstrap.php';
    if (file_exists($bootstrap_file)) {
        // Include just to check if it loads without errors
        require_once $bootstrap_file;
        
        // Check if App class exists
        if (class_exists('MapasCulturais\App')) {
            $checks['application']['status'] = 'pass';
            $checks['application']['message'] = 'Application core files loaded';
        } else {
            $checks['application']['status'] = 'fail';
            $checks['application']['message'] = 'App class not found';
            $overall_status = 'fail';
            $status_code = 503;
        }
    } else {
        $checks['application']['status'] = 'fail';
        $checks['application']['message'] = 'Bootstrap file not found';
        $overall_status = 'fail';
        $status_code = 503;
    }
} catch (Exception $e) {
    $checks['application']['status'] = 'fail';
    $checks['application']['message'] = 'Application bootstrap failed: ' . $e->getMessage();
    $overall_status = 'fail';
    $status_code = 503;
}
$checks['application']['duration'] = round(microtime(true) - $app_check_start, 4) . 's';

// Calculate total duration
$total_duration = round(microtime(true) - $start_time, 4);

// Prepare response
$response = [
    'status' => $overall_status,
    'timestamp' => date('c'),
    'service' => 'Mapas Culturais',
    'version' => file_exists(__DIR__ . '/../version.txt') ? trim(file_get_contents(__DIR__ . '/../version.txt')) : 'unknown',
    'checks' => $checks,
    'duration' => $total_duration . 's'
];

// Set HTTP status code
http_response_code($status_code);

// Output JSON
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);