<?php
/**
 * Fonts API
 * Trả về danh sách fonts có sẵn (system + custom)
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

$fontsDir = __DIR__ . '/../storage/fonts/';

// System fonts phổ biến
$systemFonts = [
    ['name' => 'Arial', 'family' => 'Arial, sans-serif'],
    ['name' => 'Times New Roman', 'family' => '"Times New Roman", serif'],
    ['name' => 'Georgia', 'family' => 'Georgia, serif'],
    ['name' => 'Verdana', 'family' => 'Verdana, sans-serif'],
    ['name' => 'Tahoma', 'family' => 'Tahoma, sans-serif'],
    ['name' => 'Trebuchet MS', 'family' => '"Trebuchet MS", sans-serif'],
    ['name' => 'Courier New', 'family' => '"Courier New", monospace'],
    ['name' => 'Impact', 'family' => 'Impact, sans-serif'],
];

// Google Fonts phổ biến
$googleFonts = [
    ['name' => 'Roboto', 'family' => 'Roboto, sans-serif', 'url' => 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap'],
    ['name' => 'Open Sans', 'family' => '"Open Sans", sans-serif', 'url' => 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap'],
    ['name' => 'Montserrat', 'family' => 'Montserrat, sans-serif', 'url' => 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap'],
    ['name' => 'Playfair Display', 'family' => '"Playfair Display", serif', 'url' => 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap'],
    ['name' => 'Dancing Script', 'family' => '"Dancing Script", cursive', 'url' => 'https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap'],
];

// Custom fonts từ thư mục storage/fonts/
$customFonts = [];
if (is_dir($fontsDir)) {
    $extensions = ['ttf', 'otf', 'woff', 'woff2'];
    
    foreach ($extensions as $ext) {
        $files = glob($fontsDir . '*.' . $ext);
        foreach ($files as $file) {
            $fontName = pathinfo($file, PATHINFO_FILENAME);
            // Kiểm tra trùng lặp
            $exists = false;
            foreach ($customFonts as $f) {
                if ($f['name'] === $fontName) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $customFonts[] = [
                    'name' => $fontName,
                    'family' => '"' . $fontName . '", sans-serif',
                    'url' => '../storage/fonts/' . basename($file),
                    'format' => $ext
                ];
            }
        }
    }
}

echo json_encode([
    'status' => true,
    'system_fonts' => $systemFonts,
    'google_fonts' => $googleFonts,
    'custom_fonts' => $customFonts,
    'fonts_dir' => realpath($fontsDir) ?: $fontsDir
]);
?>
