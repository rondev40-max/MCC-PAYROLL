<?php
// Quick database test without Laravel framework
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'mcc_payroll';

try {
    $conn = new mysqli($host, $user, $password, $database);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "✓ Connected to database: $database\n\n";
    
    // Check if attendances table exists
    $result = $conn->query("SHOW TABLES LIKE 'attendances'");
    
    if ($result && $result->num_rows > 0) {
        echo "✓ Attendances table exists\n\n";
        
        // Show table structure
        $columns = $conn->query("DESCRIBE attendances");
        echo "Table columns:\n";
        while ($col = $columns->fetch_assoc()) {
            printf("  %-20s %-30s %s\n", $col['Field'], $col['Type'], $col['Null'] == 'YES' ? 'nullable' : 'not null');
        }
        
        // Count records
        $countResult = $conn->query("SELECT COUNT(*) as cnt FROM attendances");
        $row = $countResult->fetch_assoc();
        echo "\nCurrent records: " . $row['cnt'] . "\n";
        
    } else {
        echo "✗ Attendances table does NOT exist - need to run migrations\n";
        echo "\nTip: Run 'php artisan migrate' in command line\n";
    }
    
    // List all tables
    echo "\n\nAll tables in database:\n";
    $tables = $conn->query("SHOW TABLES");
    while ($table = $tables->fetch_array()) {
        echo "  - " . $table[0] . "\n";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
