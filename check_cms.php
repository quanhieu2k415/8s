<?php
// Quick script to check CMS content
require_once 'backend_api/db_config.php';

$keys = [
    'xkldhan_title',
    'xkldchauau_title', 
    'xklddailoan_title',
    'xkldjp_title',
    'nhat_title',
    'han_title',
    'duc_title'
];

echo "=== CMS Content Check ===\n\n";

foreach ($keys as $key) {
    $stmt = $conn->prepare('SELECT text_value FROM site_texts WHERE text_key = ?');
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row) {
        echo "$key: " . $row['text_value'] . "\n";
    } else {
        echo "$key: (not found - using default)\n";
    }
}
?>
