# 🔧 Outils d'Administration

Ce dossier contient les outils et scripts d'administration pour le marketplace.

## 📁 Contenu

### 🏷️ `init_categories.php`
**Script d'initialisation des catégories**

- **Fonction** : Crée les catégories par défaut si elles n'existent pas
- **Utilisation** : `http://localhost/sellandbuy/admin/init_categories.php`
- **Fonctionnalités** :
  - Vérification des catégories existantes
  - Création de la catégorie "Acquisition" par défaut
  - Ajout de catégories supplémentaires (Électronique, Vêtements, etc.)
  - Affichage de la liste finale des catégories

### 🎯 **Utilisation Recommandée**

1. **Première installation** : Exécuter `init_categories.php` pour initialiser les catégories
2. **Maintenance** : Utiliser l'interface de debug admin intégrée
3. **Debug** : Accéder via `index.php?controller=admin&action=debug`

### 🔗 **Liens Utiles**

- **Debug Admin** : `index.php?controller=admin&action=debug`
- **Tableau de bord** : `index.php?controller=admin&action=index`
- **Analyses** : `index.php?controller=admin&action=analytics`

### ⚠️ **Sécurité**

Ces outils sont destinés aux administrateurs uniquement. Assurez-vous que :
- L'accès est restreint aux administrateurs
- Les scripts ne sont pas accessibles publiquement
- Les permissions sont correctement configurées
