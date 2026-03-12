# Quick Start - Sell & Buy

Guide court pour lancer le projet rapidement sur une machine de dev.
Etat valide au 12 mars 2026.

## 1) Prerequis

Option recommandee:
- DDEV installe
- Docker actif

Option locale classique:
- PHP 8.2+ (8.4 recommande)
- MariaDB/MySQL
- Composer

## 2) Demarrage en DDEV (recommande)

Depuis la racine du projet:

```bash
ddev start
ddev composer install
ddev import-db --file=database/vente_groupe_FINAL.sql
```

Optionnel (si vous voulez initialiser/mettre a jour des modules):

```bash
ddev exec php admin/init_all.php
ddev exec php admin/init_vente_groupe.php
ddev exec php admin/init_taxes.php
```

Ouvrir l'application:

```bash
ddev launch /index.php?controller=product&action=index
```

URL directes utiles:
- `https://sellandbuy.ddev.site/index.php?controller=product&action=index`
- `https://sellandbuy.ddev.site/index.php?controller=auth&action=login`
- `https://sellandbuy.ddev.site/index.php?controller=api&action=health`

## 3) Demarrage local (sans DDEV)

Installer les dependances:

```bash
composer install
```

Importer le schema SQL:

```bash
mysql -u <user> -p <database> < database/vente_groupe_FINAL.sql
```

Demarrer le serveur PHP:

```bash
php -S localhost:8000
```

Ouvrir:

- `http://localhost:8000/index.php?controller=product&action=index`

Important:
- Le fichier `config/database.php` est preconfigure pour DDEV (`db`/`db`/`db`).
- Si vous lancez hors DDEV, adaptez ce fichier a vos identifiants DB.

## 4) Smoke test (2 minutes)

Verifier ces pages:

1. `index.php?controller=product&action=index`
2. `index.php?controller=auth&action=login`
3. `index.php?controller=api&action=health`

Si vous avez un compte admin, verifier aussi:

1. `index.php?controller=admin&action=index`
2. `index.php?controller=admin&action=debug`
3. `index.php?controller=admin&action=erDiagram`

## 5) Lancer les tests

En DDEV:

```bash
ddev exec ./vendor/bin/phpunit --bootstrap tests/bootstrap.php tests/Unit
```

En local:

```bash
./vendor/bin/phpunit --bootstrap tests/bootstrap.php tests/Unit
```

## 6) Depannage rapide

- Erreur DB en local: verifier `config/database.php`.
- Erreur route: verifier `controller` et `action` dans l'URL.
- Erreur 500: consulter logs PHP et la page admin debug.
- Dossiers a verifier en ecriture: `public/images/uploads` et `logs`.

## 7) Securite (a lire apres demarrage)

- Vue globale de la securite de tous les projets du workspace: `../security.md`
- Vue specifique Sell & Buy: section "sellandbuy" dans `../security.md`
