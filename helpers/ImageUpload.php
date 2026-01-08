<?php
// helpers/ImageUpload.php

class ImageUpload {
    private $uploadDir;
    private $maxFileSize;
    private $allowedTypes;
    private $recommendedSizes;
    private $allowedExtensions;
    
    public function __construct() {
        $this->uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/public/images/uploads/';
        $this->maxFileSize = 5 * 1024 * 1024; // 5MB
        $this->allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $this->recommendedSizes = [
            'thumbnail' => ['width' => 300, 'height' => 300],
            'medium' => ['width' => 800, 'height' => 600],
            'large' => ['width' => 1200, 'height' => 900]
        ];
        $this->allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        // Créer le dossier d'upload s'il n'existe pas
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Upload une image avec validation et redimensionnement
     */
    public function uploadImage($file, $productId = null) {
        $errors = $this->validateImage($file);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Générer un nom de fichier unique
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $this->uploadDir . $filename;
        
        // Obtenir les dimensions de l'image
        $imageInfo = getimagesize($file['tmp_name']);
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        
        // Déplacer le fichier uploadé
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Créer les différentes tailles
            $resizedImages = $this->createResizedImages($filepath, $filename);
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'webPath' => '/public/images/uploads/' . $filename,
                'size' => filesize($filepath),
                'width' => $originalWidth,
                'height' => $originalHeight,
                'resized' => $resizedImages
            ];
        }
        
        return ['success' => false, 'errors' => ['Erreur lors de l\'upload du fichier']];
    }
    
    /**
     * Upload d'un avatar (photo de profil) recadré automatiquement en carré
     */
    public function uploadAvatar($file) {
        $errors = $this->validateImage($file);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'avatar_' . uniqid() . '_' . time() . '.' . $extension;
        $filepath = $this->uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'errors' => ["Erreur lors de l'upload du fichier"]];
        }

        // Recadrer en carré (si GD disponible)
        $this->cropToSquare($filepath, $filepath, 300);

        $imageInfo = @getimagesize($filepath);
        $width = $imageInfo[0] ?? null;
        $height = $imageInfo[1] ?? null;

        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'webPath' => '/public/images/uploads/' . $filename,
            'size' => @filesize($filepath) ?: 0,
            'width' => $width,
            'height' => $height,
        ];
    }
    
    /**
     * Valider une image uploadée
     */
    private function validateImage($file) {
        $errors = [];
        
        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Erreur lors de l\'upload du fichier';
            return $errors;
        }
        
        // Vérifier la taille
        if ($file['size'] > $this->maxFileSize) {
            $errors[] = 'Le fichier est trop volumineux (max 5MB)';
        }
        
        // Vérifier le type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            $errors[] = 'Type de fichier non autorisé (JPEG, PNG, WebP, GIF uniquement)';
        }
        
        // Vérifier que c'est bien une image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $errors[] = 'Le fichier n\'est pas une image valide';
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension === '' || !in_array($extension, $this->allowedExtensions, true)) {
            $errors[] = 'Extension de fichier non autorisée (jpg, jpeg, png, webp, gif uniquement)';
        }
        
        return $errors;
    }
    
    /**
     * Créer des versions redimensionnées de l'image
     */
    private function createResizedImages($originalPath, $filename) {
        $resizedImages = [];
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
        
        foreach ($this->recommendedSizes as $sizeName => $dimensions) {
            $newFilename = $nameWithoutExt . '_' . $sizeName . '.' . $extension;
            $newPath = $this->uploadDir . $newFilename;
            
            if ($this->resizeImage($originalPath, $newPath, $dimensions['width'], $dimensions['height'])) {
                $resizedImages[$sizeName] = [
                    'filename' => $newFilename,
                    'path' => $newPath,
                    'webPath' => '/public/images/uploads/' . $newFilename,
                    'width' => $dimensions['width'],
                    'height' => $dimensions['height']
                ];
            }
        }
        
        return $resizedImages;
    }
    
    /**
     * Redimensionner une image en gardant les proportions
     * Version sans GD - copie simplement l'image originale
     */
    private function resizeImage($sourcePath, $destPath, $maxWidth, $maxHeight) {
        // Vérifier si GD est disponible
        if (!extension_loaded('gd')) {
            // Si GD n'est pas disponible, copier simplement l'image originale
            return copy($sourcePath, $destPath);
        }
        
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) return false;
        
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];
        
        // Calculer les nouvelles dimensions en gardant les proportions
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $newWidth = intval($originalWidth * $ratio);
        $newHeight = intval($originalHeight * $ratio);
        
        // Créer l'image source selon le type
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }
        
        if (!$sourceImage) return false;
        
        // Créer l'image de destination
        $destImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Préserver la transparence pour PNG et GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($destImage, false);
            imagesavealpha($destImage, true);
            $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
            imagefilledrectangle($destImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Redimensionner
        imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        
        // Sauvegarder selon le type
        $success = false;
        switch ($mimeType) {
            case 'image/jpeg':
                $success = imagejpeg($destImage, $destPath, 85);
                break;
            case 'image/png':
                $success = imagepng($destImage, $destPath, 8);
                break;
            case 'image/webp':
                $success = imagewebp($destImage, $destPath, 85);
                break;
            case 'image/gif':
                $success = imagegif($destImage, $destPath);
                break;
        }
        
        // Libérer la mémoire
        imagedestroy($sourceImage);
        imagedestroy($destImage);
        
        return $success;
    }
    
    /**
     * Recadrer une image en carré centré puis la redimensionner à targetSize x targetSize
     */
    private function cropToSquare($sourcePath, $destPath, $targetSize = 300) {
        // Si GD n'est pas disponible, ne rien faire de spécial
        if (!extension_loaded('gd')) {
            return true;
        }

        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) return false;

        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        $side = min($originalWidth, $originalHeight);
        $srcX = (int)(($originalWidth - $side) / 2);
        $srcY = (int)(($originalHeight - $side) / 2);

        // Créer l'image source selon le type
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = @imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                $sourceImage = @imagecreatefromwebp($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = @imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }

        if (!$sourceImage) return false;

        $destImage = imagecreatetruecolor($targetSize, $targetSize);

        // Préserver la transparence pour PNG et GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($destImage, false);
            imagesavealpha($destImage, true);
            $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
            imagefilledrectangle($destImage, 0, 0, $targetSize, $targetSize, $transparent);
        }

        imagecopyresampled(
            $destImage,
            $sourceImage,
            0,
            0,
            $srcX,
            $srcY,
            $targetSize,
            $targetSize,
            $side,
            $side
        );

        $success = false;
        switch ($mimeType) {
            case 'image/jpeg':
                $success = imagejpeg($destImage, $destPath, 90);
                break;
            case 'image/png':
                $success = imagepng($destImage, $destPath, 8);
                break;
            case 'image/webp':
                $success = imagewebp($destImage, $destPath, 90);
                break;
            case 'image/gif':
                $success = imagegif($destImage, $destPath);
                break;
        }

        imagedestroy($sourceImage);
        imagedestroy($destImage);

        return $success;
    }
    
    /**
     * Supprimer une image et ses versions redimensionnées
     */
    public function deleteImage($filename) {
        $filepath = $this->uploadDir . $filename;
        $deleted = [];
        
        // Supprimer l'image originale
        if (file_exists($filepath)) {
            unlink($filepath);
            $deleted[] = $filename;
        }
        
        // Supprimer les versions redimensionnées
        $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        
        foreach ($this->recommendedSizes as $sizeName => $dimensions) {
            $resizedFilename = $nameWithoutExt . '_' . $sizeName . '.' . $extension;
            $resizedPath = $this->uploadDir . $resizedFilename;
            
            if (file_exists($resizedPath)) {
                unlink($resizedPath);
                $deleted[] = $resizedFilename;
            }
        }
        
        return $deleted;
    }
    
    /**
     * Obtenir les recommandations de taille pour l'upload
     */
    public function getSizeRecommendations() {
        return [
            'maxFileSize' => $this->maxFileSize,
            'maxFileSizeMB' => round($this->maxFileSize / (1024 * 1024), 1),
            'allowedTypes' => $this->allowedTypes,
            'recommendedSizes' => $this->recommendedSizes,
            'tips' => [
                'Pour de meilleures performances, utilisez des images de 800x600px maximum',
                'Les formats WebP et JPEG offrent le meilleur compromis qualité/taille',
                'Évitez les images trop lourdes pour améliorer le temps de chargement'
            ]
        ];
    }
    
    /**
     * Optimiser une image existante
     * Version sans GD - retourne simplement true
     */
    public function optimizeImage($filepath, $quality = 85) {
        // Vérifier si GD est disponible
        if (!extension_loaded('gd')) {
            // Si GD n'est pas disponible, retourner true (pas d'optimisation)
            return true;
        }
        
        $imageInfo = getimagesize($filepath);
        if (!$imageInfo) return false;
        
        $mimeType = $imageInfo['mime'];
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);
        
        // Créer l'image source
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($filepath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($filepath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($filepath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($filepath);
                break;
            default:
                return false;
        }
        
        if (!$sourceImage) return false;
        
        // Créer un fichier temporaire optimisé
        $tempPath = $filepath . '.tmp';
        $success = false;
        
        switch ($mimeType) {
            case 'image/jpeg':
                $success = imagejpeg($sourceImage, $tempPath, $quality);
                break;
            case 'image/png':
                $success = imagepng($sourceImage, $tempPath, 8);
                break;
            case 'image/webp':
                $success = imagewebp($sourceImage, $tempPath, $quality);
                break;
            case 'image/gif':
                $success = imagegif($sourceImage, $tempPath);
                break;
        }
        
        imagedestroy($sourceImage);
        
        if ($success) {
            // Remplacer l'original par l'optimisé
            rename($tempPath, $filepath);
            return true;
        }
        
        return false;
    }
}
