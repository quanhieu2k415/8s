<?php
/**
 * Sanitizer Class
 * Input/Output sanitization for XSS prevention
 * 
 * @package ICOGroup
 */

namespace App\Helpers;

class Sanitizer
{
    /**
     * Escape HTML special characters (XSS prevention)
     */
    public static function escape(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Alias for escape
     */
    public static function e(mixed $value): string
    {
        return self::escape($value);
    }

    /**
     * Escape for JavaScript context
     */
    public static function escapeJs(mixed $value): string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    /**
     * Escape for URL
     */
    public static function escapeUrl(string $value): string
    {
        return urlencode($value);
    }

    /**
     * Escape for CSS
     */
    public static function escapeCss(string $value): string
    {
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', $value);
    }

    /**
     * Strip all tags
     */
    public static function stripTags(string $value): string
    {
        return strip_tags($value);
    }

    /**
     * Strip tags but allow certain ones
     */
    public static function stripTagsExcept(string $value, array $allowedTags): string
    {
        $allowedStr = '<' . implode('><', $allowedTags) . '>';
        return strip_tags($value, $allowedStr);
    }

    /**
     * Sanitize for safe HTML (allows basic formatting)
     */
    public static function safeHtml(string $value): string
    {
        // Allow only basic formatting tags
        $allowed = ['b', 'i', 'u', 'strong', 'em', 'p', 'br', 'ul', 'ol', 'li', 'a'];
        $value = self::stripTagsExcept($value, $allowed);
        
        // Clean href attributes to prevent javascript: URLs
        $value = preg_replace_callback(
            '/<a\s+([^>]*href\s*=\s*["\'])([^"\']+)(["\'][^>]*)>/i',
            function ($matches) {
                $href = $matches[2];
                // Only allow http, https, mailto
                if (!preg_match('/^(https?:|mailto:|\/)/i', $href)) {
                    $href = '#';
                }
                return '<a ' . $matches[1] . self::escape($href) . $matches[3] . '>';
            },
            $value
        );
        
        return $value;
    }

    /**
     * Sanitize filename
     */
    public static function filename(string $filename): string
    {
        // Remove path info
        $filename = basename($filename);
        
        // Replace unsafe characters
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $filename);
        
        // Remove multiple dots (prevent extension spoofing)
        $filename = preg_replace('/\.+/', '.', $filename);
        
        // Limit length
        if (strlen($filename) > 255) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $filename = substr($name, 0, 250 - strlen($ext)) . '.' . $ext;
        }
        
        return $filename;
    }

    /**
     * Sanitize email
     */
    public static function email(string $value): string
    {
        return filter_var($value, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize URL
     */
    public static function url(string $value): string
    {
        return filter_var($value, FILTER_SANITIZE_URL);
    }

    /**
     * Sanitize integer
     */
    public static function int(mixed $value): int
    {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize float
     */
    public static function float(mixed $value): float
    {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Sanitize string (trim and normalize whitespace)
     */
    public static function string(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        
        $value = (string) $value;
        
        // Trim
        $value = trim($value);
        
        // Normalize whitespace
        $value = preg_replace('/\s+/', ' ', $value);
        
        // Remove null bytes
        $value = str_replace("\0", '', $value);
        
        return $value;
    }

    /**
     * Sanitize phone number
     */
    public static function phone(string $value): string
    {
        // Keep only digits and + sign
        return preg_replace('/[^0-9+]/', '', $value);
    }

    /**
     * Sanitize slug
     */
    public static function slug(string $value): string
    {
        // Convert to lowercase
        $value = mb_strtolower($value, 'UTF-8');
        
        // Vietnamese character conversion
        $vietnamese = [
            'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
            'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
            'ì', 'í', 'ị', 'ỉ', 'ĩ',
            'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
            'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
            'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
            'đ'
        ];
        $ascii = [
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y',
            'd'
        ];
        
        $value = str_replace($vietnamese, $ascii, $value);
        
        // Remove non-alphanumeric characters
        $value = preg_replace('/[^a-z0-9\s-]/', '', $value);
        
        // Replace spaces with dashes
        $value = preg_replace('/[\s-]+/', '-', $value);
        
        // Trim dashes
        $value = trim($value, '-');
        
        return $value;
    }

    /**
     * Sanitize array recursively
     */
    public static function array(array $data, callable $sanitizer = null): array
    {
        $sanitizer = $sanitizer ?? [self::class, 'string'];
        
        return array_map(function ($value) use ($sanitizer) {
            if (is_array($value)) {
                return self::array($value, $sanitizer);
            }
            return call_user_func($sanitizer, $value);
        }, $data);
    }

    /**
     * Clean input data from request
     */
    public static function cleanInput(array $data): array
    {
        $cleaned = [];
        
        foreach ($data as $key => $value) {
            // Sanitize key
            $key = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
            
            if (is_array($value)) {
                $cleaned[$key] = self::cleanInput($value);
            } else {
                $cleaned[$key] = self::string($value);
            }
        }
        
        return $cleaned;
    }
}
