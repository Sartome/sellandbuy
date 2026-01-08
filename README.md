# 🛒 Sell & Buy Marketplace - Enhanced Edition

Un marketplace moderne et sécurisé avec système d'upload d'images avancé, enchères, API REST, et fonctionnalités complètes.

## ✨ Nouveautés - Version Enhanced

### 🔒 Sécurité Renforcée
- **Protection CSRF** - Tokens de sécurité sur tous les formulaires
- **Validation avancée** - Framework de validation complet avec règles personnalisables
- **Hachage sécurisé** - Argon2ID pour les mots de passe
- **Limitation de débit** - Protection contre les attaques par force brute
- **Journalisation sécurisée** - Logs détaillés des événements de sécurité
- **Configuration par environnement** - Support des fichiers .env

### 🎯 Nouvelles Fonctionnalités
- **API REST complète** - Endpoints JSON pour intégrations tierces
- **Recherche avancée** - Recherche en temps réel avec filtres multiples
- **Pagination intelligente** - Navigation optimisée pour grandes listes
- **Notifications Toast** - Messages élégants et non-intrusifs
- **Validation en temps réel** - Feedback immédiat sur les formulaires
- **Prévisualisation d'images** - Aperçu instantané avant upload

### 🎨 Améliorations UI/UX
- **Design moderne** - Interface utilisateur améliorée avec animations
- **CSS modulaire** - Nouvelles classes utilitaires et composants
- **JavaScript avancé** - Classes ES6+ pour fonctionnalités interactives
- **Responsive amélioré** - Optimisé pour tous les appareils
- **États de chargement** - Indicateurs visuels pour actions asynchrones

## 📚 Documentation Complète

- 📋 **[Statement of Work](STATEMENT_OF_WORK.md)** - Vue d'ensemble du projet et roadmap
- 🚀 **[Implementation Guide](IMPLEMENTATION_GUIDE.md)** - Guide d'installation et configuration détaillé
- 📖 **README.md** - Ce fichier (overview et quick start)

## 🏗️ Architecture du Projet

### 📁 Structure des Dossiers

```
sellandbuy/
├── admin/                      # Outils d'administration
│   ├── init_categories.php     # Initialisation des catégories
│   └── README.md               # Documentation admin
├── config/                     # Configuration
│   ├── Config.php             # 🆕 Gestionnaire de configuration
│   ├── constants.php           # Constantes et chemins
│   └── database.php            # Configuration base de données
├── controllers/                # Contrôleurs MVC
│   ├── AdminController.php     # Administration et debug
│   ├── ApiController.php       # 🆕 API REST
│   ├── AuthController.php      # Authentification
│   ├── ProductController.php   # Gestion produits
│   └── AuctionController.php   # Gestion enchères
├── helpers/                    # Fonctions utilitaires
│   ├── Security.php           # 🆕 Utilitaires de sécurité
│   ├── Validator.php          # 🆕 Validation des entrées
│   ├── Logger.php             # 🆕 Système de journalisation
│   ├── functions.php          # ✨ Amélioré
│   ├── ImageUpload.php        # Upload d'images
│   └── InvoicePdf.php         # Génération PDF
├── models/                     # Modèles de données
│   ├── Database.php            # Singleton de connexion
│   ├── Utilisateur.php         # Gestion utilisateurs
│   ├── Produit.php            # ✨ Amélioré (recherche & pagination)
│   ├── Categorie.php           # Gestion catégories
│   └── ...
├── views/                      # Vues (templates)
│   ├── layouts/               # Layouts communs
│   ├── auth/                  # Pages d'authentification
│   ├── products/              # Pages produits
│   ├── admin/                 # Interface administration
│   └── ...
├── public/                     # Assets publics
│   ├── css/
│   │   ├── style.css          # Styles principaux
│   │   └── enhanced.css       # 🆕 Styles modernes
│   ├── js/
│   │   ├── app.js             # JavaScript principal
│   │   └── enhanced.js        # 🆕 JS avancé
│   └── images/uploads/        # Images uploadées
├── logs/                       # 🆕 Journaux d'application
├── .env.example               # 🆕 Template de configuration
├── .gitignore                 # 🆕 Exclusions Git
├── STATEMENT_OF_WORK.md       # 🆕 Document projet
├── IMPLEMENTATION_GUIDE.md    # 🆕 Guide d'installation
├── composer.json              # Dépendances PHP
└── index.php                  # Point d'entrée
```

## 🗄️ Base de Données

### 📋 Tables Principales

| Table | Description | Fichiers Utilisateurs |
|-------|-------------|----------------------|
| `Utilisateur` | Utilisateurs du système | `models/Utilisateur.php`, `controllers/AuthController.php` |
| `Produit` | Produits à vendre | `models/Produit.php`, `controllers/ProductController.php` |
| `Categorie` | Catégories de produits | `models/Categorie.php`, `views/products/create.php` |
| `Client` | Profils clients | `models/Client.php`, `controllers/AuthController.php` |
| `Vendeur` | Profils vendeurs | `models/Vendeur.php`, `controllers/AuthController.php` |
| `Gestionnaire` | Profils administrateurs | `models/Gestionnaire.php`, `controllers/AdminController.php` |
| `ProduitImages` | Images des produits | `models/ProduitImage.php`, `helpers/ImageUpload.php` |
| `Prevente` | Système de prévente | `models/Prevente.php`, `controllers/PrepurchaseController.php` |
| `Participation` | Participation aux enchères | `models/Participation.php`, `controllers/AuctionController.php` |

### 🔧 Scripts SQL Importants

- **`database/vente_groupe.sql`** - Script de création complet de la base de données
- **`init_categories.php`** - Initialisation des catégories par défaut

## 🚀 Quick Start

Voir le **[Implementation Guide](IMPLEMENTATION_GUIDE.md)** pour les instructions détaillées d'installation.

### Installation Rapide

```bash
# 1. Cloner/extraire le projet
cd /path/to/htdocs

# 2. Installer les dépendances
composer install

# 3. Configurer l'environnement
cp .env.example .env
# Éditer .env avec vos paramètres

# 4. Créer la base de données
mysql -u root -p < database/vente_groupe.sql

# 5. Initialiser les catégories
php admin/init_categories.php

# 5.b. Initialiser le module "vente_groupe" (tables supplémentaires: Facture, Bloquer, Debloquer, Signaler)
php admin/init_vente_groupe.php

# 5.c. Générer le diagramme ER (visualisation)
En tant qu'administrateur, ouvrez :

- `index.php?controller=admin&action=erDiagram` — visualiser le diagramme ER généré à partir du schéma actuel
- `index.php?controller=admin&action=downloadDiagram` — télécharger le diagramme au format SVG

# 6. Créer les dossiers nécessaires
mkdir -p logs public/images/uploads
chmod 755 logs public/images/uploads

# 7. Créer un admin
php helpers/create_admin.php
```

## 🎨 Fonctionnalités

## 🎨 Fonctionnalités

### 🔐 **Authentification & Sécurité**
- **Connexion/Inscription** avec validation renforcée
- **Protection CSRF** sur tous les formulaires
- **Hachage Argon2ID** pour mots de passe
- **Limitation de débit** anti-force brute
- **Gestion des rôles** - Client, Vendeur, Administrateur
- **Sessions sécurisées** avec cookies HttpOnly
- **Journalisation** des événements de sécurité

### 📦 **Gestion des Produits**
- **Création simplifiée** avec formulaires validés
- **Upload d'images multiples** avec prévisualisation
- **Système de catégories** dynamique
- **Recherche avancée** avec filtres en temps réel
- **Pagination intelligente** pour grandes listes
- **Gestion des stocks** avec quantités

### 🏷️ **Système d'Enchères**
- **Création d'enchères** avec dates de fin
- **Participation** en temps réel
- **Gestion automatique** des offres
- **Notifications** des événements

### 🌐 **API REST**
- **Endpoints JSON** pour intégrations
- **Health check** - `/api/health`
- **Liste produits** - `/api/products?page=1&limit=20`
- **Recherche** - `/api/search?q=term&category=1`
- **Catégories** - `/api/categories`
- **Documentation** complète dans Implementation Guide

### 🎯 **Interface Utilisateur**
- **Design moderne** avec animations fluides
- **Toast notifications** élégantes
- **Validation en temps réel** des formulaires
- **Prévisualisation** des images avant upload
- **États de chargement** visuels
- **Responsive** optimisé mobile/tablette

### 🔧 **Administration**
- **Tableau de bord** complet
- **Debug système** intégré
- **Statistiques** et analytics
- **Gestion utilisateurs** avancée
- **Configuration** centralisée

## 🛠️ Debug et Maintenance

### 🔧 **Interface de Debug Admin**

Accès : `index.php?controller=admin&action=debug`

**Fonctionnalités** :
- ✅ **Tests système** (PHP, extensions, base de données)
- ✅ **Tests des catégories** spécifiquement
- ✅ **Tests des dossiers** et permissions
- ✅ **Informations système** détaillées
- ✅ **Statistiques en temps réel** des tests

### 🧪 **Tests des Formulaires**

Les tests des formulaires sont intégrés dans l'interface de debug admin :
- Accès via l'interface admin : `index.php?controller=admin&action=debug`
- Tests automatiques des formulaires
- Debug JavaScript intégré
- Vérification des soumissions

### 🏷️ **Initialisation des Catégories**

Script : `admin/init_categories.php`
- Création automatique des catégories par défaut
- Catégories supplémentaires (Électronique, Vêtements, etc.)
- Vérification des catégories existantes
- Documentation complète dans `admin/README.md`

## 🎯 **Attributs HTML Spéciaux**

### 📝 **Formulaires**

| Attribut | Description | Utilisation |
|----------|-------------|-------------|
| `data-validate` | Active la validation JavaScript | Formulaires complexes |
| `data-loading` | Active l'animation de chargement | Formulaires de soumission |
| `autocomplete` | Améliore l'accessibilité | Tous les formulaires |

### 🔧 **JavaScript**

Le fichier `public/js/app.js` contient :

- **Validation des formulaires** - Validation en temps réel
- **Gestion des boutons** - Animation et états de chargement
- **Galerie d'images** - Lightbox et navigation
- **Animations** - Effets visuels et transitions
- **Accessibilité** - Navigation clavier et focus

## 🔒 **Sécurité**

### 🛡️ **Headers de Sécurité**
```php
// index.php
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
```

### 🔐 **Content Security Policy**
```html
<!-- views/layouts/header.php -->
<meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'nonce-<?php echo $nonce; ?>'; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com; img-src 'self' data: blob:; connect-src 'self';">
```

### 🧹 **Sanitisation**
- **Fonction `sanitize()`** - `helpers/functions.php`
- **Validation des entrées** - Tous les contrôleurs
- **Protection XSS** - `htmlspecialchars()` partout

## 📊 **Performance**

### ⚡ **Optimisations**
- **Lazy loading** des images
- **Compression automatique** des images
- **Cache des sessions**
- **Requêtes SQL optimisées**

### 🖼️ **Gestion des Images**
- **Upload multiple** avec drag & drop
- **Redimensionnement automatique** (thumbnail, moyenne, grande)
- **Validation de taille** avec recommandations
- **Optimisation automatique** des images

## 🚀 **Déploiement**

### 📋 **Checklist de Déploiement**

1. ✅ **Base de données** - Script SQL exécuté
2. ✅ **Extensions PHP** - Toutes les extensions requises
3. ✅ **Permissions** - Dossiers accessibles en écriture
4. ✅ **Configuration** - Constantes et chemins corrects
5. ✅ **Catégories** - Initialisation des catégories
6. ✅ **Tests** - Debug admin fonctionnel

### 🔧 **Maintenance**

- **Debug admin** - `index.php?controller=admin&action=debug`
- **Logs PHP** - Vérifier les erreurs
- **Base de données** - Vérifier l'intégrité
- **Images** - Nettoyer les fichiers orphelins

## 📚 **Documentation Technique**

### 🎯 **Points d'Entrée Principaux**

- **`index.php`** - Point d'entrée principal avec routage
- **`controllers/`** - Logique métier
- **`models/`** - Accès aux données
- **`views/`** - Interface utilisateur

### 🔄 **Flux de Données**

1. **Requête HTTP** → `index.php`
2. **Routage** → Contrôleur approprié
3. **Contrôleur** → Modèle + Vue
4. **Modèle** → Base de données
5. **Vue** → HTML + CSS + JavaScript

### 🎨 **Interface Utilisateur**

- **Design responsive** - Mobile-first
- **Animations fluides** - CSS3 + JavaScript
- **Accessibilité** - Labels, autocomplete, navigation clavier
- **Thème moderne** - Variables CSS, couleurs cohérentes

---

## 🎉 **Résumé des Améliorations**

Ce marketplace est maintenant **entièrement fonctionnel** avec :
- ✅ **Formulaires corrigés** - Plus de blocage des boutons
- ✅ **Debug intégré** - Interface admin complète
- ✅ **Sécurité renforcée** - Headers et CSP
- ✅ **Code documenté** - JavaScript entièrement commenté
- ✅ **Architecture claire** - Structure MVC bien définie
- ✅ **Maintenance facile** - Outils de debug intégrés

**Prêt pour la production !** 🚀