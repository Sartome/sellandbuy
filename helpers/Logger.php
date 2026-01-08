<?php
/**
 * Logger Helper Class
 * Provides application logging functionality
 */

class Logger {
    
    private const LOG_DIR = ROOT_PATH . '/logs';
    private const DATE_FORMAT = 'Y-m-d H:i:s';
    
    /**
     * Log levels
     */
    public const DEBUG = 'DEBUG';
    public const INFO = 'INFO';
    public const WARNING = 'WARNING';
    public const ERROR = 'ERROR';
    public const CRITICAL = 'CRITICAL';
    
    /**
     * Log a message
     * @param string $level Log level
     * @param string $message Message to log
     * @param array $context Additional context
     */
    private static function log(string $level, string $message, array $context = []): void {
        // Ensure log directory exists
        if (!is_dir(self::LOG_DIR)) {
            mkdir(self::LOG_DIR, 0755, true);
        }
        
        $date = date('Y-m-d');
        $logFile = self::LOG_DIR . "/app-{$date}.log";
        
        $timestamp = date(self::DATE_FORMAT);
        $contextStr = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        
        $logEntry = "[{$timestamp}] [{$level}] {$message}";
        if ($contextStr) {
            $logEntry .= " Context: {$contextStr}";
        }
        $logEntry .= PHP_EOL;
        
        error_log($logEntry, 3, $logFile);
    }
    
    /**
     * Log debug message
     * @param string $message Message
     * @param array $context Context
     */
    public static function debug(string $message, array $context = []): void {
        self::log(self::DEBUG, $message, $context);
    }
    
    /**
     * Log info message
     * @param string $message Message
     * @param array $context Context
     */
    public static function info(string $message, array $context = []): void {
        self::log(self::INFO, $message, $context);
    }
    
    /**
     * Log warning message
     * @param string $message Message
     * @param array $context Context
     */
    public static function warning(string $message, array $context = []): void {
        self::log(self::WARNING, $message, $context);
    }
    
    /**
     * Log error message
     * @param string $message Message
     * @param array $context Context
     */
    public static function error(string $message, array $context = []): void {
        self::log(self::ERROR, $message, $context);
    }
    
    /**
     * Log critical message
     * @param string $message Message
     * @param array $context Context
     */
    public static function critical(string $message, array $context = []): void {
        self::log(self::CRITICAL, $message, $context);
    }
    
    /**
     * Log exception
     * @param Throwable $exception Exception to log
     */
    public static function exception(Throwable $exception): void {
        $message = sprintf(
            'Exception: %s in %s:%d',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        
        $context = [
            'trace' => $exception->getTraceAsString(),
            'code' => $exception->getCode()
        ];
        
        self::log(self::ERROR, $message, $context);
    }
    
    /**
     * Log user activity
     * @param string $action Action performed
     * @param int|null $userId User ID
     * @param array $data Additional data
     */
    public static function activity(string $action, ?int $userId = null, array $data = []): void {
        $userId = $userId ?? ($_SESSION['user_id'] ?? null);
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $context = array_merge([
            'user_id' => $userId,
            'ip' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ], $data);
        
        self::log(self::INFO, "User Activity: {$action}", $context);
    }
    
    /**
     * Log security event
     * @param string $event Event description
     * @param array $context Context
     */
    public static function security(string $event, array $context = []): void {
        $date = date('Y-m-d');
        $logFile = self::LOG_DIR . "/security-{$date}.log";
        
        $timestamp = date(self::DATE_FORMAT);
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $defaultContext = [
            'ip' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => $_SESSION['user_id'] ?? null
        ];
        
        $context = array_merge($defaultContext, $context);
        $contextStr = json_encode($context, JSON_UNESCAPED_UNICODE);
        
        $logEntry = "[{$timestamp}] [SECURITY] {$event} Context: {$contextStr}" . PHP_EOL;
        
        if (!is_dir(self::LOG_DIR)) {
            mkdir(self::LOG_DIR, 0755, true);
        }
        
        error_log($logEntry, 3, $logFile);
    }
}
