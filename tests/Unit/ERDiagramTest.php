<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../bootstrap.php';

class ERDiagramTest extends TestCase {
    public function testGenerateSvgContainsSvgTag() {
        require_once MODELS_PATH . '/Database.php';
        require_once HELPERS_PATH . '/ERDiagram.php';

        $er = new ERDiagram();
        $svg = $er->generateSvg();

        $this->assertIsString($svg);
        $this->assertStringContainsString('<svg', $svg);
        // If the DB has the Produit table, ensure its name appears
        $this->assertTrue(strpos($svg, 'Produit') !== false || strpos($svg, 'Utilisateur') !== false);
    }
}
?>