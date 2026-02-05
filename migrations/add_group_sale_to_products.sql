-- Migration: add group sale columns to Produit
ALTER TABLE Produit
  ADD COLUMN sale_type ENUM('buy','auction','group') NOT NULL DEFAULT 'buy',
  ADD COLUMN group_required_buyers INT NULL DEFAULT NULL,
  ADD COLUMN group_expires_at DATETIME NULL DEFAULT NULL;

-- Index for faster lookups
ALTER TABLE Produit
  ADD INDEX (sale_type),
  ADD INDEX (group_expires_at);
