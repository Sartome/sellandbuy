<?php
/**
 * Initialise les tables spécifiques au module "vente_groupe"
 * (Facture, Bloquer, Debloquer, Signaler, Participation already present in migrations)
 */
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getInstance()->getConnection();
    echo "<h2>Initialisation vente_groupe</h2>";

    $sqls = [
        "CREATE TABLE IF NOT EXISTS Facture (
            id_facture INT PRIMARY KEY AUTO_INCREMENT,
            date_facture DATE,
            pdf_facture VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS Bloquer (
            id_bloquer INT PRIMARY KEY AUTO_INCREMENT,
            id_gestionnaire INT,
            id_vendeur INT,
            date_blocage TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_gestionnaire) REFERENCES Gestionnaire(id_user) ON DELETE SET NULL,
            FOREIGN KEY (id_vendeur) REFERENCES Vendeur(id_user) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS Debloquer (
            id_debloquer INT PRIMARY KEY AUTO_INCREMENT,
            id_gestionnaire INT,
            id_vendeur INT,
            date_deblocage TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_gestionnaire) REFERENCES Gestionnaire(id_user) ON DELETE SET NULL,
            FOREIGN KEY (id_vendeur) REFERENCES Vendeur(id_user) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS Signaler (
            id_signal INT AUTO_INCREMENT PRIMARY KEY,
            id_user INT,
            id_produit INT,
            date_signal DATE,
            FOREIGN KEY (id_user) REFERENCES Utilisateur(id_user) ON DELETE SET NULL,
            FOREIGN KEY (id_produit) REFERENCES Produit(id_produit) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];

    foreach ($sqls as $sql) {
        $pdo->exec($sql);
    }

    echo "<p>✅ Tables du module vente_groupe créées ou déjà existantes.</p>";
    echo "<p><a href='../index.php?controller=admin&action=index'>Retour au tableau de bord</a></p>";

} catch (Exception $e) {
    echo "<h3>Erreur :</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
?>