# Sell & Buy Marketplace

Application marketplace en PHP (architecture MVC) avec:
- gestion des produits,
- ventes directes, encheres et pre-achats,
- espace administration,
- API JSON,
- generation de facture PDF.

Ce README decrit l'etat reel du repository au 12 mars 2026, dans un workspace multi-projets
(digital-zoo, portfolio, OLD-PORTFOLIO, sellandbuy).

## Etat actuel du projet

- Point d'entree unique: `index.php`
- Routage par query string: `?controller=...&action=...`
- Configuration DB active dans `config/database.php` (profil DDEV)
- Schema SQL principal: `database/vente_groupe_FINAL.sql`
- Tests unitaires disponibles dans `tests/Unit`
- Outils admin scripts dans `admin/`

## Stack technique

- PHP 8.4 (via DDEV, voir `.ddev/config.yaml`)
- MariaDB 11.8 (via DDEV)
- Composer 2
- TCPDF (`tecnickcom/tcpdf`)
- PHPUnit (`phpunit/phpunit`)

## Arborescence utile

```text
sellandbuy/
|- index.php
|- composer.json
|- config/
|- controllers/
|- models/
|- views/
|- helpers/
|- public/
|- admin/
|- database/
|  `- vente_groupe_FINAL.sql
`- tests/
```

## Demarrage rapide

Voir le guide court: `quickstart.md`.

## Installation

### Option A - DDEV (recommande)

```bash
ddev start
ddev composer install
ddev import-db --file=database/vente_groupe_FINAL.sql
```

Puis ouvrir:

- `https://sellandbuy.ddev.site/index.php`
- ou `https://sellandbuy.ddev.site/index.php?controller=product&action=index`

### Option B - Environnement local sans DDEV

Prerequis:
- PHP 8.2+ (8.4 recommande)
- MariaDB/MySQL
- Composer

Etapes:

```bash
composer install
# Import SQL dans votre base locale
mysql -u <user> -p <database> < database/vente_groupe_FINAL.sql
php -S localhost:8000
```

Ensuite ouvrir:

- `http://localhost:8000/index.php?controller=product&action=index`

Important:
- `config/database.php` est actuellement configure pour DDEV (`db`/`db`/`db`).
- En local hors DDEV, adapter ce fichier ou vos variables d'environnement de test.

## Routes principales

Routes publiques:
- `index.php?controller=product&action=index`
- `index.php?controller=product&action=show&id=<id>`
- `index.php?controller=auth&action=login`
- `index.php?controller=auth&action=register`
- `index.php?controller=auction&action=view&product_id=<id>`

Routes utilisateur connecte:
- `index.php?controller=acquisition&action=index`
- `index.php?controller=ticket&action=index`
- `index.php?controller=auth&action=account`

Routes administration (admin requis):
- `index.php?controller=admin&action=index`
- `index.php?controller=admin&action=debug`
- `index.php?controller=admin&action=analytics`
- `index.php?controller=admin&action=categories`
- `index.php?controller=admin&action=vendors`
- `index.php?controller=admin&action=settings`
- `index.php?controller=admin&action=tickets`
- `index.php?controller=admin&action=invoices`
- `index.php?controller=admin&action=signals`
- `index.php?controller=admin&action=erDiagram`
- `index.php?controller=admin&action=downloadDiagram`

## API JSON

Toutes les routes API passent par `ApiController`:

- `index.php?controller=api&action=health`
- `index.php?controller=api&action=products&page=1&limit=20`
- `index.php?controller=api&action=product&id=1`
- `index.php?controller=api&action=search&q=iphone&category=1`
- `index.php?controller=api&action=categories`

## Scripts admin disponibles

Scripts detectes dans `admin/`:
- `init_all.php`
- `init_taxes.php`
- `init_taxes_web.php`
- `init_vente_groupe.php`
- `update_database.php`
- `update_vendeur_table.php`
- `extract_css.php`
- `init_categories.php`

Exemples d'execution:

```bash
# DDEV
ddev exec php admin/init_all.php
ddev exec php admin/init_vente_groupe.php

# Local
php admin/init_all.php
php admin/init_vente_groupe.php
```

## Tests

Execution via DDEV:

```bash
ddev exec ./vendor/bin/phpunit --bootstrap tests/bootstrap.php tests/Unit
```

Execution locale:

```bash
./vendor/bin/phpunit --bootstrap tests/bootstrap.php tests/Unit
```

Note:
- `tests/bootstrap.php` tente une connexion DB de test. Verifier les variables DB avant execution.

## Securite

Le front controller applique deja:
- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`

Voir `index.php` pour le comportement complet et la gestion d'erreurs.

Important:
- Une classe `helpers/Security.php` existe (CSRF, validation, hachage, rate-limit),
  mais certaines protections sont encore partiellement integrees dans les controles metier.
- Un etat detaille, projet par projet, est disponible dans `../security.md`.

## Documentation associee

- Guide de demarrage rapide: `quickstart.md`
- Outils admin: `admin/README.md`
- Referentiel securite global: `../security.md`
