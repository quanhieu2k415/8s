<?php
/**
 * File Upload Service
 * Secure file upload handling
 * 
 * @package ICOGroup
 */

namespace App\Services;

use App\Config\Config;
use App\Core\Logger;

class FileUploader
{
    private Config $config;
    private Logger $logger;
    private array $allowedTypes = [];
    private int $maxSize;
    private string $uploadPath;

    public function __construct()
    {
        $this->config = Config::getInstance();
        $this->logger = Logger::getInstance();
        
        $this->maxSize = (int) $this->config->get('UPLOAD_MAX_SIZE', 5242880); // 5MB
        $this->uploadPath = dirname(__DIR__, 2) . '/' . $this->config->get('UPLOAD_PATH', 'storage/uploads');
        
        $allowedTypesStr = $this->config->get('UPLOAD_ALLOWED_TYPES', 'jpg,jpeg,png,gif,webp');
        $this->allowedTypes = array_map('trim', explode(',', $allowedTypesStr));
        
        $this->ensureUploadDirectory();
    }

    /**
     * Ensure upload directory exists
     */
    private function ensureUploadDirectory(): void
    {
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    /**
     * Upload a file
     * 
     * @return array ['success' => bool, 'path' => string|null, 'error' => string|null]
     */
    public function upload(array $file, string $subdirectory = ''): array
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->errorResponse($this->getUploadErrorMessage($file['error']));
        }

        // Check file size
        if ($file['size'] > $this->maxSize) {
            $maxMB = $this->maxSize / 1048576;
            return $this->errorResponse("File quá lớn. Tối đa {$maxMB}MB");
        }

        // Validate MIME type using finfo
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!$this->isAllowedMimeType($mimeType)) {
            return $this->errorResponse("Loại file không được phép: {$mimeType}");
        }

        // Validate extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            return $this->errorResponse("Phần mở rộng không được phép: {$extension}");
        }

        // For images, verify it's actually an image
        if (strpos($mimeType, 'image/') === 0) {
            if (!$this->isValidImage($file['tmp_name'])) {
                return $this->errorResponse("File không phải là hình ảnh hợp lệ");
            }
        }

        // Generate unique filename
        $newFilename = $this->generateFilename($extension);
        
        // Build target path
        $targetDir = $this->uploadPath;
        if ($subdirectory) {
            $subdirectory = preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $subdirectory);
            $targetDir .= '/' . trim($subdirectory, '/');
            
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
        }
        
        $targetPath = $targetDir . '/' . $newFilename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->logger->error("Failed to move uploaded file", [
                'original' => $file['name'],
                'target' => $targetPath
            ]);
            return $this->errorResponse("Không thể lưu file");
        }

        // Set appropriate permissions
        chmod($targetPath, 0644);

        // Calculate relative path for URL
        $relativePath = $this->config->get('UPLOAD_PATH', 'storage/uploads');
        if ($subdirectory) {
            $relativePath .= '/' . $subdirectory;
        }
        $relativePath .= '/' . $newFilename;

        $this->logger->info("File uploaded successfully", [
            'original' => $file['name'],
            'saved_as' => $newFilename,
            'size' => $file['size']
        ]);

        return [
            'success' => true,
            'path' => $relativePath,
            'filename' => $newFilename,
            'original_name' => $file['name'],
            'size' => $file['size'],
            'mime_type' => $mimeType
        ];
    }

    /**
     * Delete a file
     */
    public function delete(string $path): bool
    {
        $fullPath = dirname(__DIR__, 2) . '/' . ltrim($path, '/');
        
        if (file_exists($fullPath)) {
            if (unlink($fullPath)) {
                $this->logger->info("File deleted", ['path' => $path]);
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if MIME type is allowed
     */
    private function isAllowedMimeType(string $mimeType): bool
    {
        $allowedMimes = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif'],
            'image/webp' => ['webp']
        ];

        if (!isset($allowedMimes[$mimeType])) {
            return false;
        }

        // Check if any of the extensions for this MIME type are allowed
        foreach ($allowedMimes[$mimeType] as $ext) {
            if (in_array($ext, $this->allowedTypes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify file is a valid image using getimagesize
     */
    private function isValidImage(string $filePath): bool
    {
        $imageInfo = @getimagesize($filePath);
        
        if ($imageInfo === false) {
            return false;
        }

        // Check for valid image types
        $validTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
        
        return in_array($imageInfo[2], $validTypes);
    }

    /**
     * Generate unique filename
     */
    private function generateFilename(string $extension): string
    {
        $timestamp = date('Ymd_His');
        $random = bin2hex(random_bytes(8));
        
        return "{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File vượt quá kích thước cho phép',
            UPLOAD_ERR_FORM_SIZE => 'File vượt quá kích thước form cho phép',
            UPLOAD_ERR_PARTIAL => 'File chỉ được upload một phần',
            UPLOAD_ERR_NO_FILE => 'Không có file nào được upload',
            UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm',
            UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file lên đĩa',
            UPLOAD_ERR_EXTENSION => 'Upload bị chặn bởi extension'
        ];

        return $errors[$errorCode] ?? 'Lỗi upload không xác định';
    }

    /**
     * Error response helper
     */
    private function errorResponse(string $message): array
    {
        return [
            'success' => false,
            'path' => null,
            'error' => $message
        ];
    }

    /**
     * Get max upload size in bytes
     */
    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    /**
     * Get allowed file types
     */
    public function getAllowedTypes(): array
    {
        return $this->allowedTypes;
    }
}
