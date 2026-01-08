<?php
/**
 * Configuration Loader
 * Loads environment variables and application configuration
 */

class Config {
    
    private static ?array $config = null;
    
    /**
     * Load configuration
     */
    private static function load(): void {
        if (self::$config !== null) {
            return;
        }
        
        self::$config = [];
        
        // Load .env file if exists
        $envFile = ROOT_PATH . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // Skip comments
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                // Parse key=value
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    
                    // Remove quotes if present
                    if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                        $value = $matches[2];
                    }
                    
                    self::$config[$key] = $value;
                    
                    // Also set as environment variable
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                }
            }
        }
    }
    
    /**
     * Get configuration value
     * @param string $key Configuration key
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value
     */
    public static function get(string $key, $default = null) {
        self::load();
        
        // Check environment variable first
        $value = getenv($key);
        if ($value !== false) {
            return self::parseValue($value);
        }
        
        // Check loaded config
        if (isset(self::$config[$key])) {
            return self::parseValue(self::$config[$key]);
        }
        
        return $default;
    }
    
    /**
     * Parse configuration value
     * @param mixed $value Value to parse
     * @return mixed Parsed value
     */
    private static function parseValue($value) {
        if (!is_string($value)) {
            return $value;
        }
        
        $lower = strtolower($value);
        
        // Parse boolean values
        if ($lower === 'true') {
            return true;
        }
        if ($lower === 'false') {
            return false;
        }
        if ($lower === 'null') {
            return null;
        }
        
        return $value;
    }
    
    /**
     * Check if configuration key exists
     * @param string $key Configuration key
     * @return bool True if exists
     */
    public static function has(string $key): bool {
        self::load();
        return isset(self::$config[$key]) || getenv($key) !== false;
    }
    
    /**
     * Get all configuration
     * @return array All configuration
     */
    public static function all(): array {
        self::load();
        return self::$config;
    }
}
