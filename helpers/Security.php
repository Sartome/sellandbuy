<?php
/**
 * Security Helper Class
 * Provides CSRF protection, input validation, and security utilities
 */

class Security {
    
    /**
     * Generate CSRF token and store in session
     * @return string The generated token
     */
    public static function generateCsrfToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
    }
    
    /**
     * Validate CSRF token from request
     * @param string|null $token Token to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateCsrfToken(?string $token): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        
        // Check if token has expired (1 hour default)
        $expireTime = $_SESSION['csrf_token_time'] ?? 0;
        if (time() - $expireTime > 3600) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Get CSRF token from session or generate new one
     * @return string The CSRF token
     */
    public static function getCsrfToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            return self::generateCsrfToken();
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Generate CSRF token input field for forms
     * @return string HTML input field
     */
    public static function csrfField(): string {
        $token = self::getCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Validate email address
     * @param string $email Email to validate
     * @return bool True if valid
     */
    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate password strength
     * @param string $password Password to validate
     * @param int $minLength Minimum length required
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validatePassword(string $password, int $minLength = 8): array {
        $errors = [];
        
        if (strlen($password) < $minLength) {
            $errors[] = "Le mot de passe doit contenir au moins {$minLength} caractères";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une lettre minuscule";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une lettre majuscule";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un chiffre";
        }
        
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un caractère spécial";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Sanitize string input
     * @param string $input Input to sanitize
     * @return string Sanitized input
     */
    public static function sanitizeString(string $input): string {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }
    
    /**
     * Sanitize integer input
     * @param mixed $input Input to sanitize
     * @return int Sanitized integer
     */
    public static function sanitizeInt($input): int {
        return (int)filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }
    
    /**
     * Sanitize float input
     * @param mixed $input Input to sanitize
     * @return float Sanitized float
     */
    public static function sanitizeFloat($input): float {
        return (float)filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
    
    /**
     * Check if request is HTTPS
     * @return bool True if HTTPS
     */
    public static function isHttps(): bool {
        return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }
    
    /**
     * Generate secure random string
     * @param int $length Length of string
     * @return string Random string
     */
    public static function generateRandomString(int $length = 32): string {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Rate limiting check (simple implementation)
     * @param string $action Action identifier
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $timeWindow Time window in seconds
     * @return bool True if allowed, false if rate limited
     */
    public static function checkRateLimit(string $action, int $maxAttempts = 5, int $timeWindow = 300): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $key = 'rate_limit_' . $action;
        $now = time();
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }
        
        // Remove old attempts
        $_SESSION[$key] = array_filter($_SESSION[$key], function($timestamp) use ($now, $timeWindow) {
            return ($now - $timestamp) < $timeWindow;
        });
        
        if (count($_SESSION[$key]) >= $maxAttempts) {
            return false;
        }
        
        $_SESSION[$key][] = $now;
        return true;
    }
    
    /**
     * Validate file upload
     * @param array $file File from $_FILES
     * @param array $allowedTypes Allowed MIME types
     * @param int $maxSize Maximum file size in bytes
     * @return array ['valid' => bool, 'error' => string]
     */
    public static function validateFileUpload(array $file, array $allowedTypes, int $maxSize): array {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['valid' => false, 'error' => 'Paramètres invalides'];
        }
        
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['valid' => false, 'error' => 'Le fichier est trop volumineux'];
            case UPLOAD_ERR_NO_FILE:
                return ['valid' => false, 'error' => 'Aucun fichier envoyé'];
            default:
                return ['valid' => false, 'error' => 'Erreur inconnue'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'Le fichier dépasse la taille maximale autorisée'];
        }
        
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, $allowedTypes)) {
            return ['valid' => false, 'error' => 'Type de fichier non autorisé'];
        }
        
        return ['valid' => true, 'error' => ''];
    }
    
    /**
     * Hash password using modern algorithm
     * @param string $password Password to hash
     * @return string Hashed password
     */
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_ARGON2ID);
    }
    
    /**
     * Verify password against hash
     * @param string $password Plain password
     * @param string $hash Hashed password
     * @return bool True if matches
     */
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if password needs rehashing
     * @param string $hash Current hash
     * @return bool True if needs rehashing
     */
    public static function needsRehash(string $hash): bool {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID);
    }
}
