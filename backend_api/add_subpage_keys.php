<?php
// backend_api/add_subpage_keys.php
include 'db_config.php';

$newImages = [
    [
        'image_key' => 'nhat_header_bg',
        'image_url' => '', 
        'alt_text' => 'Background header trang Nhật',
        'page' => 'nhat',
        'section' => 'header'
    ],
    [
        'image_key' => 'duc_header_bg',
        'image_url' => '', 
        'alt_text' => 'Background header trang Đức',
        'page' => 'duc',
        'section' => 'header'
    ],
    [
        'image_key' => 'han_header_bg',
        'image_url' => '', 
        'alt_text' => 'Background header trang Hàn',
        'page' => 'han',
        'section' => 'header'
    ],
    [
        'image_key' => 'xkldjp_header_bg',
        'image_url' => '', 
        'alt_text' => 'Background header trang XKLĐ Nhật',
        'page' => 'xkldjp',
        'section' => 'header'
    ],
    [
        'image_key' => 'about_header_bg', // ve-icogroup.php
        'image_url' => '', 
        'alt_text' => 'Background header trang Về ICOGroup',
        'page' => 'about',
        'section' => 'header'
    ],
    [
        'image_key' => 'contact_header_bg', // lienhe.php
        'image_url' => '', 
        'alt_text' => 'Background header trang Liên hệ',
        'page' => 'contact',
        'section' => 'header'
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
