<?php
/**
 * API Controller
 * Handles REST API endpoints
 */

class ApiController {
    
    private array $allowedOrigins = [];
    
    public function __construct() {
        // Set JSON content type
        header('Content-Type: application/json');
        
        // Handle CORS
        $this->handleCors();
    }
    
    /**
     * Handle CORS headers
     */
    private function handleCors(): void {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array($origin, $this->allowedOrigins) || $origin === BASE_URL) {
            header("Access-Control-Allow-Origin: $origin");
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Max-Age: 86400');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
    
    /**
     * Send JSON response
     * @param mixed $data Response data
     * @param int $statusCode HTTP status code
     */
    protected function jsonResponse($data, int $statusCode = 200): void {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Send success response
     * @param mixed $data Response data
     * @param string $message Success message
     */
    protected function success($data = null, string $message = 'Success'): void {
        $response = [
            'success' => true,
            'message' => $message,
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $this->jsonResponse($response);
    }
    
    /**
     * Send error response
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param array $errors Additional errors
     */
    protected function error(string $message, int $statusCode = 400, array $errors = []): void {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        $this->jsonResponse($response, $statusCode);
    }
    
    /**
     * Get JSON input data
     * @return array|null Decoded JSON data
     */
    protected function getJsonInput(): ?array {
        $input = file_get_contents('php://input');
        return json_decode($input, true);
    }
    
    /**
     * Validate API key (if needed)
     * @return bool True if valid
     */
    protected function validateApiKey(): bool {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
        // Implement your API key validation logic
        return !empty($apiKey);
    }
    
    /**
     * API: Get all products
     */
    public function products(): void {
        try {
            require_once MODELS_PATH . '/Produit.php';
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $limit = min($limit, 100); // Max 100 items per page
            
            $productModel = new Produit();
            $products = $productModel->getAll();
            
            // Pagination
            $total = count($products);
            $offset = ($page - 1) * $limit;
            $products = array_slice($products, $offset, $limit);
            
            $this->success([
                'products' => $products,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
        } catch (Exception $e) {
            Logger::exception($e);
            $this->error('Erreur lors de la récupération des produits', 500);
        }
    }
    
    /**
     * API: Get product by ID
     */
    public function product(): void {
        try {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            
            if ($id <= 0) {
                $this->error('ID produit invalide', 400);
            }
            
            require_once MODELS_PATH . '/Produit.php';
            $productModel = new Produit();
            $product = $productModel->findById($id);
            
            if (!$product) {
                $this->error('Produit introuvable', 404);
            }
            
            $this->success($product);
        } catch (Exception $e) {
            Logger::exception($e);
            $this->error('Erreur lors de la récupération du produit', 500);
        }
    }
    
    /**
     * API: Search products
     */
    public function search(): void {
        try {
            $query = $_GET['q'] ?? '';
            $category = isset($_GET['category']) ? (int)$_GET['category'] : null;
            
            if (empty($query) && !$category) {
                $this->error('Paramètres de recherche manquants', 400);
            }
            
            require_once MODELS_PATH . '/Produit.php';
            $productModel = new Produit();
            $products = $productModel->search($query, $category);
            
            $this->success([
                'query' => $query,
                'category' => $category,
                'results' => $products,
                'count' => count($products)
            ]);
        } catch (Exception $e) {
            Logger::exception($e);
            $this->error('Erreur lors de la recherche', 500);
        }
    }
    
    /**
     * API: Get categories
     */
    public function categories(): void {
        try {
            require_once MODELS_PATH . '/Categorie.php';
            $categoryModel = new Categorie();
            $categories = $categoryModel->getAll();
            
            $this->success($categories);
        } catch (Exception $e) {
            Logger::exception($e);
            $this->error('Erreur lors de la récupération des catégories', 500);
        }
    }
    
    /**
     * API: Health check
     */
    public function health(): void {
        $this->success([
            'status' => 'ok',
            'timestamp' => time(),
            'version' => '1.0.0'
        ]);
    }
}
