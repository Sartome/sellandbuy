# Zoo Management System

Système de gestion de zoo réorganisé selon l'architecture MVC.

## Installation

1. Placer le projet dans votre serveur web (htdocs/www)
2. Importer le fichier `database/zoo_management.sql` dans MySQL
3. Configurer les paramètres de connexion dans `config/database.php`
4. Copier le contenu du fichier CSS original dans `public/css/style.css`
5. Accéder à l'application via `http://localhost/zoo_management/public`

## Structure

- `config/` - Configuration de l'application
- `controllers/` - Logique métier
- `models/` - Accès aux données
- `views/` - Templates d'affichage
- `public/` - Fichiers accessibles publiquement
- `helpers/` - Fonctions utilitaires
- `api/` - Points d'entrée API

## Credentials par défaut

- **Directeur**: admin / admin
- **Salarié**: test / (voir BDD)

## Technologies

- PHP 7.4+
- MySQL/MariaDB
- Architecture MVC
