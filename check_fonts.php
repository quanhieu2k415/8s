<?php
/**
 * Check Font Settings in Database
 */

require_once __DIR__ . '/backend_api/db_config.php';

echo "=== CURRENT FONT SETTINGS ===\n\n";

if ($conn) {
    $sql = "SELECT text_key, text_value FROM site_texts WHERE text_key LIKE 'global_font%'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo $row['text_key'] . " = " . $row['text_value'] . "\n";
        }
    } else {
        echo "NO FONT SETTINGS FOUND IN DATABASE\n\n";
        echo "You need to go to Admin Panel -> Settings and configure fonts.\n";
    }
    
    $conn->close();
} else {
    echo "Database connection failed\n";
}
?>
