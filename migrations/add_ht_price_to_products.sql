-- Add prix_ht and taux_tva columns to Produit table
ALTER TABLE Produit 
ADD COLUMN prix_ht DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN taux_tva DECIMAL(5,2) DEFAULT 20.00;
