<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase
{
    /**
     * Vérifie que les tests fonctionnent
     */
    public function testBasicAssertion(): void
    {
        $this->assertTrue(true);
    }

    /**
     * Vérifie que la connexion à la base de données fonctionne
     */
    public function testDatabaseConnection(): void
    {
        global $db;
        $this->assertInstanceOf(PDO::class, $db, 'La connexion à la base de données a échoué');
        
        // Tester une requête simple
        $result = $db->query('SELECT 1 as test')->fetch();
        $this->assertEquals(1, $result['test']);
    }

    /**
     * Vérifie que les constantes sont définies
     */
    public function testConstantsAreDefined(): void
    {
        $this->assertTrue(defined('BASE_URL'), 'BASE_URL doit être définie');
        $this->assertTrue(defined('APP_ROOT'), 'APP_ROOT doit être définie');
        $this->assertTrue(defined('TEST_ROOT'), 'TEST_ROOT doit être définie');
    }
}
