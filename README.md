# 🛒 Sell & Buy Marketplace - Enhanced

Un marketplace moderne avec système d'upload d'images avancé et fonctionnalités cool.

## ✨ Nouvelles fonctionnalités

### 🖼️ Système d'upload d'images avancé

- **Upload multiple d'images** avec drag & drop
- **Redimensionnement automatique** (thumbnail, moyenne, grande)
- **Validation de taille** avec recommandations
- **Prévisualisation en temps réel**
- **Gestion des images principales**
- **Optimisation automatique** des images

### 🎨 Interface utilisateur améliorée

- **Animations fluides** et micro-interactions
- **Design moderne** avec thème sombre
- **Galerie d'images interactive** avec lightbox
- **Navigation au clavier** pour l'accessibilité
- **Responsive design** optimisé mobile
- **Validation de formulaires** en temps réel

### 🔧 Fonctionnalités techniques

- **Lazy loading** des images
- **Compression automatique** des images
- **Gestion des métadonnées** (alt text, dimensions)
- **Système de permissions** pour la gestion d'images
- **Statistiques d'images** dans l'admin
- **Nettoyage automatique** des fichiers orphelins

## 🚀 Installation

1. **Base de données** : Exécutez le script SQL mis à jour
```sql
-- Les nouvelles tables sont ajoutées automatiquement
-- ProduitImages pour les images multiples
-- Champs étendus dans Produit pour les métadonnées
```

2. **Dossier d'upload** : Créez le dossier pour les images
```bash
mkdir -p public/images/uploads
chmod 755 public/images/uploads
```

3. **Configuration PHP** : Vérifiez les extensions requises
```php
// Extensions nécessaires
extension=gd
extension=fileinfo
extension=exif
```

## 📁 Structure des fichiers

```
├── helpers/
│   └── ImageUpload.php          # Gestionnaire d'upload d'images
├── models/
│   └── ProduitImage.php         # Modèle pour les images de produits
├── views/
│   ├── products/
│   │   ├── create.php           # Formulaire de création avec upload
│   │   ├── show.php             # Affichage produit avec galerie
│   │   └── add_images.php       # Ajout d'images supplémentaires
│   └── admin/
│       └── analytics.php        # Tableau de bord admin amélioré
└── public/
    ├── css/style.css            # Styles avec animations
    └── js/app.js                # JavaScript avancé
```

## 🎯 Utilisation

### Pour les vendeurs

1. **Créer un produit** :
   - Glissez-déposez vos images
   - Consultez les recommandations de taille
   - Prévisualisez avant publication

2. **Gérer les images** :
   - Définir l'image principale
   - Ajouter des images supplémentaires
   - Supprimer des images indésirables

### Pour les administrateurs

1. **Tableau de bord** :
   - Statistiques des images
   - Optimisation en masse
   - Nettoyage des fichiers orphelins

2. **Gestion** :
   - Rapports d'utilisation
   - Monitoring des performances
   - Maintenance automatique

## 🔧 Configuration

### Tailles d'images recommandées

```php
// Dans ImageUpload.php
'recommendedSizes' => [
    'thumbnail' => ['width' => 300, 'height' => 300],
    'medium' => ['width' => 800, 'height' => 600],
    'large' => ['width' => 1200, 'height' => 900]
]
```

### Limites de fichiers

```php
// Configuration par défaut
'maxFileSize' => 5 * 1024 * 1024, // 5MB
'allowedTypes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif']
```

## 🎨 Personnalisation

### Thème et couleurs

```css
:root {
    --primary: #7c3aed;      /* Couleur principale */
    --success: #10b981;      /* Couleur de succès */
    --danger: #ef4444;       /* Couleur de danger */
    --bg: #0f172a;           /* Arrière-plan */
}
```

### Animations

```css
/* Personnaliser les animations */
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-8px) scale(1.02);
}
```

## 🔒 Sécurité

- **Validation des types MIME** avec `finfo`
- **Vérification des dimensions** d'images
- **Limitation de taille** des fichiers
- **Noms de fichiers sécurisés** avec `uniqid()`
- **Permissions de fichiers** appropriées

## 📱 Responsive Design

- **Mobile-first** approach
- **Breakpoints** optimisés
- **Touch-friendly** interactions
- **Performance** mobile optimisée

## 🚀 Performance

- **Lazy loading** des images
- **Compression** automatique
- **Cache** des images redimensionnées
- **Optimisation** des requêtes SQL

## 🔧 Pages de Diagnostic

### Outils de débogage disponibles

1. **`debug_advanced.php`** - Diagnostic complet du système de connexion
   - Test de tous les composants
   - Détection des erreurs silencieuses
   - Vérification des headers et redirections

2. **`test_final.php`** - Test interactif de connexion
   - Simulation du processus de connexion
   - Test de création de session
   - Vérification des redirections

3. **`debug_login_complete.php`** - Diagnostic système complet
   - Vérification de la configuration PHP
   - Test de la base de données
   - Validation des fonctions utilitaires

4. **`test_login_process.php`** - Simulation du processus de connexion
   - Test étape par étape
   - Création de session simulée
   - Test des redirections

5. **`create_test_user.php`** - Création d'utilisateurs de test
   - Création automatique d'utilisateurs
   - Test des différents rôles
   - Vérification des profils

6. **`check_db.php`** - Vérification rapide de la base de données
   - État de la base de données
   - Nombre d'utilisateurs
   - Liens vers les outils de création

### Utilisation des outils de diagnostic

```bash
# 1. Vérifier l'état de la base de données
http://localhost/sellandbuy/check_db.php

# 2. Créer des utilisateurs de test
http://localhost/sellandbuy/create_test_user.php

# 3. Diagnostic complet du système
http://localhost/sellandbuy/debug_advanced.php

# 4. Test de connexion interactif
http://localhost/sellandbuy/test_final.php
```

## 🗄️ Concepts SQL Utilisés

### Structure de la base de données

#### Tables principales

1. **`Utilisateur`** - Table centrale des utilisateurs
```sql
CREATE TABLE Utilisateur (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255),
    prenom VARCHAR(255),
    adresse VARCHAR(255),
    phone VARCHAR(20),
    email VARCHAR(255),
    motdepasse VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

2. **`Vendeur`** - Profils vendeurs (héritage de Utilisateur)
```sql
CREATE TABLE Vendeur (
    id_user INT PRIMARY KEY,
    nom_entreprise VARCHAR(100),
    siret VARCHAR(14),
    adresse_entreprise VARCHAR(100),
    email_pro VARCHAR(100),
    FOREIGN KEY (id_user) REFERENCES Utilisateur(id_user)
);
```

3. **`Client`** - Profils clients (héritage de Utilisateur)
```sql
CREATE TABLE Client (
    id_user INT PRIMARY KEY,
    FOREIGN KEY (id_user) REFERENCES Utilisateur(id_user)
);
```

4. **`Gestionnaire`** - Profils administrateurs (héritage de Utilisateur)
```sql
CREATE TABLE Gestionnaire (
    id_user INT PRIMARY KEY,
    FOREIGN KEY (id_user) REFERENCES Utilisateur(id_user)
);
```

#### Tables de produits

5. **`Categorie`** - Catégories de produits
```sql
CREATE TABLE Categorie (
    id_categorie INT PRIMARY KEY AUTO_INCREMENT,
    id_gestionnaire INT,
    lib VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_gestionnaire) REFERENCES Gestionnaire(id_user)
);
```

6. **`Produit`** - Produits en vente
```sql
CREATE TABLE Produit (
    id_produit INT PRIMARY KEY AUTO_INCREMENT,
    description VARCHAR(255),
    prix DECIMAL(10,2),
    image VARCHAR(255),
    image_alt VARCHAR(255),
    image_size INT,
    image_width INT,
    image_height INT,
    id_vendeur INT,
    id_categorie INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_vendeur) REFERENCES Vendeur(id_user),
    FOREIGN KEY (id_categorie) REFERENCES Categorie(id_categorie)
);
```

7. **`ProduitImages`** - Images multiples par produit
```sql
CREATE TABLE ProduitImages (
    id_image INT PRIMARY KEY AUTO_INCREMENT,
    id_produit INT,
    image_path VARCHAR(255),
    image_alt VARCHAR(255),
    image_size INT,
    image_width INT,
    image_height INT,
    is_primary BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_produit) REFERENCES Produit(id_produit) ON DELETE CASCADE
);
```

#### Tables de gestion

8. **`Prevente`** - Système de préventes
```sql
CREATE TABLE Prevente (
    id_prevente INT PRIMARY KEY AUTO_INCREMENT,
    date_limite DATE,
    nombre_min INT,
    statut VARCHAR(255),
    prix_prevente DECIMAL(10,2),
    id_produit INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_produit) REFERENCES Produit(id_produit)
);
```

9. **`Facture`** - Factures générées
```sql
CREATE TABLE Facture (
    id_facture INT PRIMARY KEY AUTO_INCREMENT,
    date_facture DATE,
    pdf_facture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

10. **`Participation`** - Participation aux préventes
```sql
CREATE TABLE Participation (
    id_particiption INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT,
    id_prevente INT,
    id_facture INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_client) REFERENCES Client(id_user),
    FOREIGN KEY (id_prevente) REFERENCES Prevente(id_prevente),
    FOREIGN KEY (id_facture) REFERENCES Facture(id_facture)
);
```

#### Tables de modération

11. **`Signaler`** - Signalements de produits
```sql
CREATE TABLE Signaler (
    id_signal INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT,
    id_produit INT,
    date_signal DATE,
    FOREIGN KEY (id_user) REFERENCES Utilisateur(id_user),
    FOREIGN KEY (id_produit) REFERENCES Produit(id_produit)
);
```

12. **`Bloquer`** - Blocage de vendeurs
```sql
CREATE TABLE Bloquer (
    id_bloquer INT AUTO_INCREMENT PRIMARY KEY,
    id_gestionnaire INT,
    id_vendeur INT,
    date_blocage TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_gestionnaire) REFERENCES Gestionnaire(id_user),
    FOREIGN KEY (id_vendeur) REFERENCES Vendeur(id_user)
);
```

13. **`Debloquer`** - Déblocage de vendeurs
```sql
CREATE TABLE Debloquer (
    id_debloquer INT AUTO_INCREMENT PRIMARY KEY,
    id_gestionnaire INT,
    id_vendeur INT,
    date_deblocage TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_gestionnaire) REFERENCES Gestionnaire(id_user),
    FOREIGN KEY (id_vendeur) REFERENCES Vendeur(id_user)
);
```

### Concepts SQL avancés utilisés

#### 1. **Héritage de tables** (Pattern Table per Type)
- `Utilisateur` comme table de base
- `Vendeur`, `Client`, `Gestionnaire` comme tables spécialisées
- Clés étrangères vers la table de base

#### 2. **Relations Many-to-Many**
- `Participation` : Client ↔ Prevente
- `Signaler` : Utilisateur ↔ Produit

#### 3. **Relations One-to-Many**
- `Produit` → `ProduitImages` (images multiples)
- `Vendeur` → `Produit` (produits du vendeur)
- `Categorie` → `Produit` (catégorisation)

#### 4. **Audit Trail**
- `created_at` et `updated_at` sur toutes les tables
- Historique des actions (blocage/déblocage)

#### 5. **Contraintes d'intégrité**
- Clés étrangères avec `ON DELETE CASCADE`
- Contraintes de validation (SIRET, email)
- Index pour les performances

#### 6. **Types de données optimisés**
- `DECIMAL(10,2)` pour les prix (précision monétaire)
- `TIMESTAMP` pour les dates automatiques
- `VARCHAR` avec tailles appropriées

## 🐛 Dépannage

### Problèmes courants

1. **Erreur d'upload** :
   - Vérifiez les permissions du dossier `uploads/`
   - Vérifiez la configuration PHP `upload_max_filesize`

2. **Images non affichées** :
   - Vérifiez les chemins dans la base de données
   - Vérifiez les permissions de lecture des fichiers

3. **Performance lente** :
   - Activez la compression d'images
   - Utilisez le lazy loading

4. **Problèmes de connexion** :
   - Utilisez `debug_advanced.php` pour diagnostiquer
   - Vérifiez que la base de données n'est pas vide
   - Créez des utilisateurs de test avec `create_test_user.php`

### Identifiants de test par défaut

- **Client** : `test@example.com` / `password123`
- **Admin** : `admin@example.com` / `admin123`
- **Vendeur** : `vendeur@example.com` / `vendeur123`

## 📈 Améliorations futures

- [ ] **CDN** pour les images
- [ ] **Watermarking** automatique
- [ ] **IA** pour l'optimisation d'images
- [ ] **Upload** par chunks pour gros fichiers
- [ ] **Compression** WebP automatique

## 🤝 Contribution

1. Fork le projet
2. Créez une branche feature
3. Committez vos changements
4. Poussez vers la branche
5. Ouvrez une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

---

**Développé avec ❤️ pour une expérience utilisateur exceptionnelle**