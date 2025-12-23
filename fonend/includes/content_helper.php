<?php
/**
 * Content Helper - ICOGroup CMS
 * 
 * Loads dynamic content from database for frontend pages.
 * Works with the admin CMS to allow content editing without code changes.
 * 
 * Usage in templates:
 *   <?= get_image('index_hero_banner', 'default-banner.jpg') ?>
 *   <?= get_text('index_about_title', 'Về ICOGroup') ?>
 *   <?= get_content('header_phone', '0123456789') ?>
 */

// Load database config
$db_config_path = __DIR__ . '/../../backend_api/db_config.php';
if (file_exists($db_config_path)) {
    include_once $db_config_path;
} else {
    error_log("CMS Error: Cannot find db_config.php at $db_config_path");
}

// Cache to avoid multiple DB queries per page
$cms_content_cache = [];
$cms_loaded = false;

/**
 * Load all CMS content from database into cache
 */
function load_all_cms_content() {
    global $conn, $content_table, $cms_content_cache, $cms_loaded;
    
    if ($cms_loaded) return;
    if (!$conn) return;

    $table = isset($content_table) ? $content_table : 'content_pages';
    
    $sql = "SELECT section_key, content_value FROM $table";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cms_content_cache[$row['section_key']] = $row['content_value'];
        }
    }

    $cms_loaded = true;
}

/**
 * Get content by key
 * 
 * @param string $key The content key (e.g., 'index_hero_title')
 * @param string $default Default value if key not found
 * @return string The content value
 */
function get_content($key, $default = '') {
    global $cms_loaded, $cms_content_cache;
    
    if (!$cms_loaded) {
        load_all_cms_content();
    }

    if (isset($cms_content_cache[$key]) && $cms_content_cache[$key] !== '') {
        return $cms_content_cache[$key];
    }
    
    return $default;
}

/**
 * Get image URL by key
 * Alias for get_content, specifically for image URLs
 * 
 * @param string $key The image key (e.g., 'index_hero_banner')
 * @param string $default_url Default image URL if not found
 * @return string The image URL
 */
function get_image($key, $default_url = '') {
    return get_content($key, $default_url);
}

/**
 * Get text content by key
 * Alias for get_content, specifically for text
 * 
 * @param string $key The text key (e.g., 'index_about_title')
 * @param string $default_text Default text if not found
 * @return string The text content
 */
function get_text($key, $default_text = '') {
    return get_content($key, $default_text);
}

/**
 * Echo content with HTML escaping
 * 
 * @param string $key The content key
 * @param string $default Default value
 */
function echo_text($key, $default = '') {
    echo htmlspecialchars(get_content($key, $default), ENT_QUOTES, 'UTF-8');
}

/**
 * Echo image with src attribute
 * 
 * @param string $key The image key
 * @param string $default Default image URL
 * @param string $alt Alt text
 * @param string $class CSS class
 */
function echo_image($key, $default = '', $alt = '', $class = '') {
    $url = get_content($key, $default);
    $alt_text = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');
    $class_attr = $class ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : '';
    echo '<img src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" alt="' . $alt_text . '"' . $class_attr . '>';
}

/**
 * Check if content exists and is not empty
 * 
 * @param string $key The content key
 * @return bool
 */
function has_content($key) {
    global $cms_loaded, $cms_content_cache;
    
    if (!$cms_loaded) {
        load_all_cms_content();
    }

    return isset($cms_content_cache[$key]) && $cms_content_cache[$key] !== '';
}

/**
 * Get all content keys that match a prefix
 * Useful for getting all items in a section
 * 
 * @param string $prefix The key prefix (e.g., 'index_programs_')
 * @return array Matching content items
 */
function get_content_by_prefix($prefix) {
    global $cms_loaded, $cms_content_cache;
    
    if (!$cms_loaded) {
        load_all_cms_content();
    }

    $results = [];
    foreach ($cms_content_cache as $key => $value) {
        if (strpos($key, $prefix) === 0) {
            $results[$key] = $value;
        }
    }
    
    return $results;
}

/**
 * Get all content blocks for a page
 * 
 * @param string $page_key The page key (e.g., 'duc', 'nhat', 'xkldjp')
 * @return array List of content blocks
 */
function get_content_blocks($page_key) {
    global $conn;
    
    if (!$conn) return [];
    
    $sql = "SELECT * FROM content_blocks WHERE page_key = ? AND is_active = 1 ORDER BY block_order ASC";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) return [];
    
    $stmt->bind_param("s", $page_key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $blocks = [];
    while ($row = $result->fetch_assoc()) {
        $blocks[] = $row;
    }
    
    return $blocks;
}

/**
 * Render HTML content safely (allowing formatting tags)
 * 
 * @param string $content The HTML content
 * @param string $default Default value if empty
 * @return string Sanitized HTML content
 */
function render_html($content, $default = '') {
    if (empty($content)) return $default;
    
    // Cho phép các tag định dạng cơ bản và inline styles
    $allowed_tags = '<b><i><u><s><strong><em><span><br><p><h1><h2><h3><h4><h5><h6><font><div><ul><ol><li><a>';
    return strip_tags($content, $allowed_tags);
}

/**
 * Get text content and render as HTML (allowing safe formatting)
 * 
 * @param string $key The text key
 * @param string $default_text Default text if not found
 * @return string The HTML content (sanitized)
 */
function get_html_text($key, $default_text = '') {
    $content = get_content($key, $default_text);
    return render_html($content, $default_text);
}

/**
 * Echo HTML content block
 * 
 * @param string $page_key The page key
 * @param string $block_type Filter by block type (optional)
 */
function echo_content_blocks($page_key, $block_type = null) {
    $blocks = get_content_blocks($page_key);
    
    foreach ($blocks as $block) {
        if ($block_type && $block['block_type'] !== $block_type) continue;
        
        echo '<div class="content-block content-block-' . htmlspecialchars($block['block_type']) . '">';
        
        if (!empty($block['title'])) {
            echo '<h3 class="block-title">' . render_html($block['title']) . '</h3>';
        }
        
        if (!empty($block['image_url'])) {
            echo '<div class="block-image"><img src="' . htmlspecialchars($block['image_url']) . '" alt=""></div>';
        }
        
        if (!empty($block['content'])) {
            echo '<div class="block-content">' . render_html($block['content']) . '</div>';
        }
        
        echo '</div>';
    }
}
?>

