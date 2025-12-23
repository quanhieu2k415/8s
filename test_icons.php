<?php
require_once __DIR__ . '/fonend/includes/content_helper.php';

echo "Testing icon URLs from database:\n\n";

$icons = [
    'global_facebook_icon',
    'global_youtube_icon',
    'global_zalo_icon'
];

foreach ($icons as $key) {
    $value = get_content($key, 'NOT_FOUND');
    echo "$key = $value\n";
}
?>
