<?php
// Quick database query to find an employee account
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'mcc_payroll';

try {
    $conn = new mysqli($host, $user, $password, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "Querying users with role 'employee'...\n";
    $result = $conn->query("SELECT id, name, email, role FROM users WHERE role = 'employee' LIMIT 5");
    
    if ($result && $result->num_rows > 0) {
        echo "Found employee users:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  - Name: " . $row['name'] . " | Email: " . $row['email'] . " | Role: " . $row['role'] . "\n";
        }
    } else {
        echo "No users found with role = 'employee'. Listing first 5 users instead:\n";
        $resultAll = $conn->query("SELECT id, name, email, role FROM users LIMIT 5");
        if ($resultAll && $resultAll->num_rows > 0) {
            while ($row = $resultAll->fetch_assoc()) {
                echo "  - Name: " . $row['name'] . " | Email: " . $row['email'] . " | Role: " . $row['role'] . "\n";
            }
        } else {
            echo "No users found at all in the users table.\n";
        }
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
