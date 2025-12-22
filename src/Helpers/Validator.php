<?php
/**
 * Validator Class
 * Input validation with various rules
 * 
 * @package ICOGroup
 */

namespace App\Helpers;

class Validator
{
    private array $errors = [];
    private array $data = [];

    /**
     * Validate data against rules
     * 
     * @param array $data Data to validate
     * @param array $rules Validation rules
     * @return bool True if valid
     */
    public function validate(array $data, array $rules): bool
    {
        $this->data = $data;
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $rulesArray = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);

            foreach ($rulesArray as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Apply single validation rule
     */
    private function applyRule(string $field, mixed $value, string $rule): void
    {
        // Parse rule with parameters (e.g., "min:5" or "between:1,10")
        $params = [];
        if (strpos($rule, ':') !== false) {
            [$rule, $paramStr] = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
        }

        $methodName = 'validate' . str_replace('_', '', ucwords($rule, '_'));
        
        if (method_exists($this, $methodName)) {
            $this->$methodName($field, $value, $params);
        }
    }

    /**
     * Required field
     */
    private function validateRequired(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, "Trường {$field} là bắt buộc");
        }
    }

    /**
     * Email format
     */
    private function validateEmail(string $field, mixed $value, array $params): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "Email không hợp lệ");
        }
    }

    /**
     * Minimum length
     */
    private function validateMin(string $field, mixed $value, array $params): void
    {
        $min = (int) ($params[0] ?? 0);
        
        if ($value !== null && $value !== '' && strlen($value) < $min) {
            $this->addError($field, "Trường {$field} phải có ít nhất {$min} ký tự");
        }
    }

    /**
     * Maximum length
     */
    private function validateMax(string $field, mixed $value, array $params): void
    {
        $max = (int) ($params[0] ?? 255);
        
        if ($value !== null && strlen($value) > $max) {
            $this->addError($field, "Trường {$field} không được vượt quá {$max} ký tự");
        }
    }

    /**
     * Between length
     */
    private function validateBetween(string $field, mixed $value, array $params): void
    {
        $min = (int) ($params[0] ?? 0);
        $max = (int) ($params[1] ?? 255);
        $length = strlen($value ?? '');
        
        if ($value !== null && $value !== '' && ($length < $min || $length > $max)) {
            $this->addError($field, "Trường {$field} phải có từ {$min} đến {$max} ký tự");
        }
    }

    /**
     * Numeric value
     */
    private function validateNumeric(string $field, mixed $value, array $params): void
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->addError($field, "Trường {$field} phải là số");
        }
    }

    /**
     * Integer value
     */
    private function validateInteger(string $field, mixed $value, array $params): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, "Trường {$field} phải là số nguyên");
        }
    }

    /**
     * Phone number format (Vietnam)
     */
    private function validatePhone(string $field, mixed $value, array $params): void
    {
        if ($value !== null && $value !== '') {
            $pattern = '/^(0|\+84)[0-9]{9,10}$/';
            if (!preg_match($pattern, $value)) {
                $this->addError($field, "Số điện thoại không hợp lệ");
            }
        }
    }

    /**
     * Date format
     */
    private function validateDate(string $field, mixed $value, array $params): void
    {
        $format = $params[0] ?? 'Y-m-d';
        
        if ($value !== null && $value !== '') {
            $date = \DateTime::createFromFormat($format, $value);
            if (!$date || $date->format($format) !== $value) {
                $this->addError($field, "Ngày không hợp lệ (định dạng: {$format})");
            }
        }
    }

    /**
     * Year format (4 digits)
     */
    private function validateYear(string $field, mixed $value, array $params): void
    {
        if ($value !== null && $value !== '') {
            if (!preg_match('/^[12][0-9]{3}$/', $value)) {
                $this->addError($field, "Năm không hợp lệ");
            }
        }
    }

    /**
     * In list (whitelist)
     */
    private function validateIn(string $field, mixed $value, array $params): void
    {
        if ($value !== null && $value !== '' && !in_array($value, $params)) {
            $allowed = implode(', ', $params);
            $this->addError($field, "Trường {$field} phải là một trong: {$allowed}");
        }
    }

    /**
     * Not in list (blacklist)
     */
    private function validateNotIn(string $field, mixed $value, array $params): void
    {
        if ($value !== null && $value !== '' && in_array($value, $params)) {
            $this->addError($field, "Giá trị không được phép");
        }
    }

    /**
     * Regex pattern
     */
    private function validateRegex(string $field, mixed $value, array $params): void
    {
        $pattern = $params[0] ?? '';
        
        if ($value !== null && $value !== '' && !preg_match($pattern, $value)) {
            $this->addError($field, "Trường {$field} không đúng định dạng");
        }
    }

    /**
     * URL format
     */
    private function validateUrl(string $field, mixed $value, array $params): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, "URL không hợp lệ");
        }
    }

    /**
     * Alpha characters only
     */
    private function validateAlpha(string $field, mixed $value, array $params): void
    {
        if ($value !== null && $value !== '' && !preg_match('/^[\p{L}\s]+$/u', $value)) {
            $this->addError($field, "Trường {$field} chỉ được chứa chữ cái");
        }
    }

    /**
     * Alphanumeric characters
     */
    private function validateAlphaNum(string $field, mixed $value, array $params): void
    {
        if ($value !== null && $value !== '' && !preg_match('/^[\p{L}\p{N}\s]+$/u', $value)) {
            $this->addError($field, "Trường {$field} chỉ được chứa chữ và số");
        }
    }

    /**
     * Password strength
     */
    private function validatePassword(string $field, mixed $value, array $params): void
    {
        if ($value !== null && $value !== '') {
            $minLength = (int) ($params[0] ?? 8);
            $errors = [];

            if (strlen($value) < $minLength) {
                $errors[] = "ít nhất {$minLength} ký tự";
            }
            if (!preg_match('/[A-Z]/', $value)) {
                $errors[] = "ít nhất 1 chữ hoa";
            }
            if (!preg_match('/[a-z]/', $value)) {
                $errors[] = "ít nhất 1 chữ thường";
            }
            if (!preg_match('/[0-9]/', $value)) {
                $errors[] = "ít nhất 1 số";
            }

            if (!empty($errors)) {
                $this->addError($field, "Mật khẩu phải có: " . implode(', ', $errors));
            }
        }
    }

    /**
     * Confirmed field (field_confirmation must match)
     */
    private function validateConfirmed(string $field, mixed $value, array $params): void
    {
        $confirmField = $field . '_confirmation';
        $confirmValue = $this->data[$confirmField] ?? null;
        
        if ($value !== $confirmValue) {
            $this->addError($field, "Xác nhận không khớp");
        }
    }

    /**
     * Same as another field
     */
    private function validateSame(string $field, mixed $value, array $params): void
    {
        $otherField = $params[0] ?? '';
        $otherValue = $this->data[$otherField] ?? null;
        
        if ($value !== $otherValue) {
            $this->addError($field, "Trường {$field} phải giống với trường {$otherField}");
        }
    }

    /**
     * Different from another field
     */
    private function validateDifferent(string $field, mixed $value, array $params): void
    {
        $otherField = $params[0] ?? '';
        $otherValue = $this->data[$otherField] ?? null;
        
        if ($value === $otherValue) {
            $this->addError($field, "Trường {$field} phải khác với trường {$otherField}");
        }
    }

    /**
     * Nullable - allows null/empty values
     */
    private function validateNullable(string $field, mixed $value, array $params): void
    {
        // This rule doesn't add errors, it just indicates the field can be null
    }

    /**
     * Add error message
     */
    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Get all errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get first error for a field
     */
    public function getError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Check if field has error
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    /**
     * Get all error messages as flat array
     */
    public function getMessages(): array
    {
        $messages = [];
        foreach ($this->errors as $fieldErrors) {
            $messages = array_merge($messages, $fieldErrors);
        }
        return $messages;
    }

    /**
     * Static validation helper
     */
    public static function make(array $data, array $rules): Validator
    {
        $validator = new self();
        $validator->validate($data, $rules);
        return $validator;
    }
}
