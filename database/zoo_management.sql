-- ==========================
-- Base de données : Vente_groupe
-- ==========================

CREATE DATABASE vente_groupe;

USE vente_groupe;

-- Table centrale : tous les utilisateurs (clients, vendeurs, gestionnaires)
CREATE TABLE Utilisateur (
    id_user INT PRIMARY KEY AUTO_INCREMENT,  -- Identifiant unique de l'utilisateur
    nom VARCHAR(255),                        -- Nom
    prenom VARCHAR(255),                     -- Prénom
    adresse VARCHAR(255),                    -- Adresse postale
    phone VARCHAR(20),                       -- Numéro de téléphone
    email VARCHAR(255),                      -- Email                     
    motdepasse VARCHAR(255),                 -- Mot de passe (haché en pratique)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Vendeurs (entreprises liées à un utilisateur)
CREATE TABLE Vendeur (
    id_user INT PRIMARY KEY,                     -- Lien utilisateur
    nom_entreprise VARCHAR(100),                 -- Nom entreprise
    siret VARCHAR(14),                           -- Numéro SIRET
    adresse_entreprise VARCHAR(100),             -- Adresse
    email_pro VARCHAR(100),                      -- Email pro
    FOREIGN KEY (id_user) REFERENCES Utilisateur(id_user)
);

-- Gestionnaires (utilisateurs avec rôle spécial)
CREATE TABLE Gestionnaire (
    id_user INT PRIMARY KEY,  -- Identifiant gestionnaire
    FOREIGN KEY (id_user) REFERENCES Utilisateur(id_user) -- reference 
);

-- Clients (utilisateurs avec rôle client)
CREATE TABLE Client (
    id_user INT PRIMARY KEY,  -- Identifiant client
    FOREIGN KEY (id_user) REFERENCES Utilisateur(id_user)-- Lien utilisateur
);


-- Catégories de produits créées par les gestionnaires (créée AVANT Produit pour permettre la FK)
CREATE TABLE Categorie (
    id_categorie INT PRIMARY KEY AUTO_INCREMENT, -- Identifiant catégorie
    id_gestionnaire INT,                         -- Gestionnaire créateur
    lib VARCHAR(100),                            -- Libellé
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_gestionnaire) REFERENCES Gestionnaire(id_user)
);

-- Produits mis en vente par les vendeurs
CREATE TABLE Produit (
    id_produit INT PRIMARY KEY AUTO_INCREMENT,  -- Identifiant produit
    description VARCHAR(255),                   -- Description
    prix DECIMAL(10,2),                         -- Prix du produit
    image VARCHAR(255),                         -- Lien/chemin image
    id_vendeur INT,                             -- Référence vendeur
    id_categorie INT NULL,                      -- Catégorie du produit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_produit_vendeur (id_vendeur),
    INDEX idx_produit_categorie (id_categorie),
    FOREIGN KEY (id_vendeur) REFERENCES Vendeur(id_user), -- Un vendeur est un utilisateur
    FOREIGN KEY (id_categorie) REFERENCES Categorie(id_categorie)
);


-- Préventes liées à des produits
CREATE TABLE Prevente (
    id_prevente INT PRIMARY KEY AUTO_INCREMENT,  -- Identifiant prévente
    date_limite DATE,                            -- Date limite
    nombre_min INT,                              -- Nombre minimum requis
    statut VARCHAR(255),                         -- Statut (en cours, validée, annulée)
    prix_prevente DECIMAL(10,2),                 -- Prix proposé
    id_produit INT,                              -- Produit concerné
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_produit) REFERENCES Produit(id_produit)
);
 

-- Historique des déblocages effectués par les gestionnaires
CREATE TABLE Debloquer (
    id_debloquer INT PRIMARY KEY AUTO_INCREMENT,  -- Identifiant déblocage
    id_gestionnaire INT,                          -- Gestionnaire concerné
    id_vendeur INT,                               -- Vendeur débloqué
    date_deblocage TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_gestionnaire) REFERENCES Gestionnaire(id_user),
    FOREIGN KEY (id_vendeur) REFERENCES Vendeur(id_user)
);

-- Factures générées lors des achats
CREATE TABLE Facture (
    id_facture INT PRIMARY KEY AUTO_INCREMENT,  -- Identifiant facture
    date_facture DATE,                          -- Date
    pdf_facture VARCHAR(255),                   -- Lien fichier PDF
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
     
-- Historique des blocages effectués par les gestionnaires
CREATE TABLE Bloquer (
    id_bloquer INT PRIMARY KEY AUTO_INCREMENT,  -- Identifiant blocage
    id_gestionnaire INT,                        -- Gestionnaire concerné
    id_vendeur INT,                             -- Vendeur bloqué
    date_blocage TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (id_gestionnaire) REFERENCES Gestionnaire(id_user),
    FOREIGN KEY (id_vendeur) REFERENCES Vendeur(id_user)
);

-- Signalements faits par les utilisateurs sur des produits
CREATE TABLE Signaler (
    id_signal INT AUTO_INCREMENT PRIMARY KEY,   -- Identifiant signalement
    id_user INT,                                -- Utilisateur qui signale
    id_produit INT,                             -- Produit signalé
    date_signal DATE,                           -- Date du signalement
    FOREIGN KEY (id_user) REFERENCES Utilisateur(id_user),
    FOREIGN KEY (id_produit) REFERENCES Produit(id_produit)
);

-- Participation d'un client à une prévente (avec facture associée)
CREATE TABLE Participation (
    id_particiption INT AUTO_INCREMENT PRIMARY KEY, -- Identifiant participation
    id_client INT,                                  -- Client participant
    id_prevente INT,                                -- Prévente concernée
    id_facture INT,                                 -- Facture générée
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_client) REFERENCES Client(id_user),
    FOREIGN KEY (id_prevente) REFERENCES Prevente(id_prevente),
    FOREIGN KEY (id_facture) REFERENCES Facture(id_facture)
);