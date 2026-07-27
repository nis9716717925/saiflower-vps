<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h3>Testing Database Connection...</h3>";

$host = "localhost";
$db_user = "u977002836_Omsaifactor123";
$db_pass = "Omsaifactor123";
$db_name = "u977002836_Omsaifactor123";

try {
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);
    echo "<p style='color:green;'><b>SUCCESS!</b> Connected successfully to MySQL.</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'><b>CONNECTION FAILED!</b></p>";
    echo "<p><b>Error Message:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    
    echo "<h4>How to fix this in Hostinger:</h4>";
    echo "<ul>
        <li><b>Access denied for user:</b> Make sure you clicked 'Add User to Database' in the Hostinger MySQL panel to link this user and database together with ALL Privileges. Also double-check the password.</li>
        <li><b>Unknown database:</b> Verify that the database name '$db_name' is spelled exactly like this in Hostinger.</li>
    </ul>";
}
?>
