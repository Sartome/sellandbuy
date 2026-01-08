<?php
/**
 * Validator Helper Class
 * Provides comprehensive input validation
 */

class Validator {
    
    private array $errors = [];
    private array $data = [];
    
    /**
     * Constructor
     * @param array $data Data to validate
     */
    public function __construct(array $data = []) {
        $this->data = $data;
    }
    
    /**
     * Validate required field
     * @param string $field Field name
     * @param string $message Error message
     * @return self
     */
    public function required(string $field, string $message = ''): self {
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field][] = $message ?: "Le champ {$field} est requis";
        }
        return $this;
    }
    
    /**
     * Validate email
     * @param string $field Field name
     * @param string $message Error message
     * @return self
     */
    public function email(string $field, string $message = ''): self {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = $message ?: "L'email {$field} est invalide";
        }
        return $this;
    }
    
    /**
     * Validate minimum length
     * @param string $field Field name
     * @param int $min Minimum length
     * @param string $message Error message
     * @return self
     */
    public function minLength(string $field, int $min, string $message = ''): self {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field][] = $message ?: "Le champ {$field} doit contenir au moins {$min} caractères";
        }
        return $this;
    }
    
    /**
     * Validate maximum length
     * @param string $field Field name
     * @param int $max Maximum length
     * @param string $message Error message
     * @return self
     */
    public function maxLength(string $field, int $max, string $message = ''): self {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field][] = $message ?: "Le champ {$field} ne doit pas dépasser {$max} caractères";
        }
        return $this;
    }
    
    /**
     * Validate numeric value
     * @param string $field Field name
     * @param string $message Error message
     * @return self
     */
    public function numeric(string $field, string $message = ''): self {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field][] = $message ?: "Le champ {$field} doit être numérique";
        }
        return $this;
    }
    
    /**
     * Validate minimum value
     * @param string $field Field name
     * @param float $min Minimum value
     * @param string $message Error message
     * @return self
     */
    public function min(string $field, float $min, string $message = ''): self {
        if (isset($this->data[$field]) && (float)$this->data[$field] < $min) {
            $this->errors[$field][] = $message ?: "Le champ {$field} doit être au moins {$min}";
        }
        return $this;
    }
    
    /**
     * Validate maximum value
     * @param string $field Field name
     * @param float $max Maximum value
     * @param string $message Error message
     * @return self
     */
    public function max(string $field, float $max, string $message = ''): self {
        if (isset($this->data[$field]) && (float)$this->data[$field] > $max) {
            $this->errors[$field][] = $message ?: "Le champ {$field} ne doit pas dépasser {$max}";
        }
        return $this;
    }
    
    /**
     * Validate field matches another field
     * @param string $field Field name
     * @param string $matchField Field to match
     * @param string $message Error message
     * @return self
     */
    public function matches(string $field, string $matchField, string $message = ''): self {
        if (isset($this->data[$field]) && isset($this->data[$matchField]) 
            && $this->data[$field] !== $this->data[$matchField]) {
            $this->errors[$field][] = $message ?: "Le champ {$field} ne correspond pas à {$matchField}";
        }
        return $this;
    }
    
    /**
     * Validate URL
     * @param string $field Field name
     * @param string $message Error message
     * @return self
     */
    public function url(string $field, string $message = ''): self {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_URL)) {
            $this->errors[$field][] = $message ?: "Le champ {$field} doit être une URL valide";
        }
        return $this;
    }
    
    /**
     * Validate regex pattern
     * @param string $field Field name
     * @param string $pattern Regex pattern
     * @param string $message Error message
     * @return self
     */
    public function pattern(string $field, string $pattern, string $message = ''): self {
        if (isset($this->data[$field]) && !preg_match($pattern, $this->data[$field])) {
            $this->errors[$field][] = $message ?: "Le champ {$field} n'est pas au bon format";
        }
        return $this;
    }
    
    /**
     * Validate value is in array
     * @param string $field Field name
     * @param array $values Allowed values
     * @param string $message Error message
     * @return self
     */
    public function in(string $field, array $values, string $message = ''): self {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values, true)) {
            $this->errors[$field][] = $message ?: "Le champ {$field} contient une valeur invalide";
        }
        return $this;
    }
    
    /**
     * Check if validation passed
     * @return bool True if no errors
     */
    public function isValid(): bool {
        return empty($this->errors);
    }
    
    /**
     * Get validation errors
     * @return array Errors array
     */
    public function getErrors(): array {
        return $this->errors;
    }
    
    /**
     * Get first error for a field
     * @param string $field Field name
     * @return string|null First error or null
     */
    public function getFirstError(string $field): ?string {
        return $this->errors[$field][0] ?? null;
    }
    
    /**
     * Get all errors as flat array
     * @return array Flat errors array
     */
    public function getAllErrors(): array {
        $allErrors = [];
        foreach ($this->errors as $field => $errors) {
            $allErrors = array_merge($allErrors, $errors);
        }
        return $allErrors;
    }
    
    /**
     * Add custom error
     * @param string $field Field name
     * @param string $message Error message
     * @return self
     */
    public function addError(string $field, string $message): self {
        $this->errors[$field][] = $message;
        return $this;
    }
}
