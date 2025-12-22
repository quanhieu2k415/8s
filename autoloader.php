<?php
/**
 * PSR-4 Autoloader
 * Automatically loads classes from the src/ directory
 * 
 * @package ICOGroup
 */

spl_autoload_register(function ($class) {
    // Project namespace prefix
    $prefix = 'App\\';
    
    // Base directory for the namespace prefix
    $baseDir = __DIR__ . '/src/';
    
    // Check if the class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // Move to the next registered autoloader
        return;
    }
    
    // Get the relative class name
    $relativeClass = substr($class, $len);
    
    // Replace namespace separators with directory separators
    // and append with .php
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
