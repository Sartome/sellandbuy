<?php
/**
 * Script pour extraire tout le CSS des fichiers PHP et l'unifier dans style.css
 */

require_once __DIR__ . '/../config/constants.php';

$cssFiles = [
    'views/admin/index.php',
    'views/admin/settings.php', 
    'views/admin/vendors.php',
    'views/admin/categories.php',
    'views/admin/debug.php',
    'views/admin/ads.php',
    'views/products/index.php',
    'views/acquisition/index.php',
    'views/products/create.php',
    'views/admin/analytics.php',
    'views/auth/register.php',
    'views/auth/login.php',
    'views/products/add_images.php'
];

$unifiedCSS = "\n/* ========================================\n   UNIFIED CSS FROM ALL PHP FILES\n   ======================================== */\n\n";

foreach ($cssFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Extraire le CSS entre les balises <style> et </style>
        if (preg_match('/<style>(.*?)<\/style>/s', $content, $matches)) {
            $css = $matches[1];
            $unifiedCSS .= "/* CSS from " . $file . " */\n";
            $unifiedCSS .= $css . "\n\n";
            
            echo "✅ Extracted CSS from: " . $file . "\n";
        } else {
            echo "⚠️  No CSS found in: " . $file . "\n";
        }
    } else {
        echo "❌ File not found: " . $file . "\n";
    }
}

// Ajouter le CSS unifié au fichier style.css
$styleFile = 'public/css/style.css';
$currentCSS = file_get_contents($styleFile);

// Supprimer l'ancienne section unifiée si elle existe
$currentCSS = preg_replace('/\/\* ========================================\s+UNIFIED CSS FROM ALL PHP FILES\s+======================================== \*\/.*$/s', '', $currentCSS);

// Ajouter la nouvelle section
$newCSS = $currentCSS . $unifiedCSS;

file_put_contents($styleFile, $newCSS);

echo "\n🎉 CSS unified successfully in " . $styleFile . "\n";
echo "📊 Total files processed: " . count($cssFiles) . "\n";
?>
