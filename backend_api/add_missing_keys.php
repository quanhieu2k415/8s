<?php
// backend_api/add_missing_keys.php
include 'db_config.php';

$newImages = [
    [
        'image_key' => 'index_programs_bg',
        'image_url' => '', 
        'alt_text' => 'Programs Section Background',
        'page' => 'index',
        'section' => 'programs'
    ],
    [
        'image_key' => 'index_news_bg',
        'image_url' => '',
        'alt_text' => 'News Section Background',
        'page' => 'index',
        'section' => 'news'
    ]
];

foreach ($newImages as $img) {
    // Check if key exists
    $check = $conn->prepare("SELECT id FROM site_images WHERE image_key = ?");
    $check->bind_param("s", $img['image_key']);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO site_images (image_key, image_url, alt_text, page, section) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $img['image_key'], $img['image_url'], $img['alt_text'], $img['page'], $img['section']);
        if ($stmt->execute()) {
            echo "Added key: " . $img['image_key'] . "<br>";
        } else {
            echo "Error adding key: " . $img['image_key'] . " - " . $conn->error . "<br>";
        }
    } else {
        echo "Key already exists: " . $img['image_key'] . "<br>";
    }
}

echo "Done.";
?>
