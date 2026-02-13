-- ========================================================================
-- QUICK REFERENCE: SQL QUERIES FOR C# SELLER CLIENT
-- Common queries you'll need for the seller management application
-- ========================================================================

-- ========================================================================
-- AUTHENTICATION & SELLER INFO
-- ========================================================================

-- Get seller information with user details
SELECT
    u.id_user,
    u.nom,
    u.prenom,
    u.email,
    u.phone,
    u.avatar,
    v.nom_entreprise,
    v.siret,
    v.email_pro,
    v.is_certified,
    v.is_blocked
FROM Utilisateur u
JOIN Vendeur v ON u.id_user = v.id_user
WHERE u.email = @email;

-- Check if seller is blocked
SELECT is_blocked
FROM Vendeur
WHERE id_user = @sellerId;

-- ========================================================================
-- PRODUCT MANAGEMENT
-- ========================================================================

-- Get all products for a seller with category info
SELECT
    p.id_produit,
    p.description,
    p.prix,
    p.prix_ht,
    p.taux_tva,
    p.quantity,
    p.image,
    p.sale_type,
    p.group_required_buyers,
    p.group_expires_at,
    p.created_at,
    c.lib AS categorie_name,
    c.id_categorie,
    COALESCE(COUNT(DISTINCT s.id), 0) AS sales_count,
    COALESCE(AVG(r.rating), 0) AS avg_rating
FROM Produit p
LEFT JOIN Categorie c ON p.id_categorie = c.id_categorie
LEFT JOIN sales s ON p.id_produit = s.product_id
LEFT JOIN reviews r ON p.id_produit = r.product_id
WHERE p.id_vendeur = @sellerId
GROUP BY p.id_produit
ORDER BY p.created_at DESC;

-- Get single product with full details
SELECT
    p.*,
    c.lib AS categorie_name,
    v.nom_entreprise
FROM Produit p
LEFT JOIN Categorie c ON p.id_categorie = c.id_categorie
LEFT JOIN Vendeur v ON p.id_vendeur = v.id_user
WHERE p.id_produit = @productId;

-- Get product images
SELECT *
FROM ProduitImages
WHERE id_produit = @productId
ORDER BY sort_order, is_primary DESC;

-- Insert new product
INSERT INTO Produit (
    description,
    prix,
    prix_ht,
    taux_tva,
    quantity,
    image,
    id_vendeur,
    id_categorie,
    sale_type,
    group_required_buyers,
    group_expires_at
)
VALUES (
    @description,
    @prix,
    @prixHT,
    @tauxTVA,
    @quantity,
    @image,
    @idVendeur,
    @idCategorie,
    @saleType,
    @groupRequiredBuyers,
    @groupExpiresAt
);

-- Get last inserted product ID
SELECT LAST_INSERT_ID();

-- Update product
UPDATE Produit
SET description = @description,
    prix = @prix,
    prix_ht = @prixHT,
    taux_tva = @tauxTVA,
    quantity = @quantity,
    id_categorie = @idCategorie,
    sale_type = @saleType,
    group_required_buyers = @groupRequiredBuyers,
    group_expires_at = @groupExpiresAt,
    updated_at = CURRENT_TIMESTAMP
WHERE id_produit = @productId
AND id_vendeur = @sellerId;  -- Security check!

-- Update product stock
UPDATE Produit
SET quantity = @quantity
WHERE id_produit = @productId
AND id_vendeur = @sellerId;

-- Decrease product stock (after sale)
UPDATE Produit
SET quantity = quantity - @soldQuantity
WHERE id_produit = @productId
AND quantity >= @soldQuantity
AND id_vendeur = @sellerId;

-- Delete product
DELETE FROM Produit
WHERE id_produit = @productId
AND id_vendeur = @sellerId;  -- Security check!

-- Add product image
INSERT INTO ProduitImages (
    id_produit,
    image_path,
    image_alt,
    is_primary,
    sort_order
)
VALUES (
    @idProduit,
    @imagePath,
    @imageAlt,
    @isPrimary,
    @sortOrder
);

-- Set primary image
UPDATE ProduitImages
SET is_primary = CASE WHEN id_image = @imageId THEN TRUE ELSE FALSE END
WHERE id_produit = @productId;

-- Delete product image
DELETE FROM ProduitImages
WHERE id_image = @imageId
AND id_produit IN (SELECT id_produit FROM Produit WHERE id_vendeur = @sellerId);

-- ========================================================================
-- STOCK MANAGEMENT
-- ========================================================================

-- Get low stock products (quantity <= 5)
SELECT
    p.id_produit,
    p.description,
    p.quantity,
    p.prix,
    COALESCE(COUNT(DISTINCT s.id), 0) AS sales_count
FROM Produit p
LEFT JOIN sales s ON p.id_produit = s.product_id
WHERE p.id_vendeur = @sellerId
AND p.quantity <= 5
GROUP BY p.id_produit
ORDER BY p.quantity ASC;

-- Get out of stock products
SELECT
    p.id_produit,
    p.description,
    p.prix,
    p.created_at
FROM Produit p
WHERE p.id_vendeur = @sellerId
AND p.quantity = 0
ORDER BY p.created_at DESC;

-- Get stock summary
SELECT
    COUNT(*) AS total_products,
    SUM(quantity) AS total_stock,
    SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) AS out_of_stock,
    SUM(CASE WHEN quantity <= 5 AND quantity > 0 THEN 1 ELSE 0 END) AS low_stock
FROM Produit
WHERE id_vendeur = @sellerId;

-- ========================================================================
-- SALES HISTORY & ANALYTICS
-- ========================================================================

-- Get all sales for a seller with buyer info
SELECT
    s.id,
    s.product_id,
    s.buyer_id,
    s.amount,
    s.created_at,
    p.description AS product_description,
    p.prix AS product_price,
    CONCAT(u.prenom, ' ', u.nom) AS buyer_name,
    u.email AS buyer_email,
    u.phone AS buyer_phone
FROM sales s
JOIN Produit p ON s.product_id = p.id_produit
JOIN Utilisateur u ON s.buyer_id = u.id_user
WHERE p.id_vendeur = @sellerId
ORDER BY s.created_at DESC;

-- Get sales within date range
SELECT
    s.*,
    p.description,
    CONCAT(u.prenom, ' ', u.nom) AS buyer_name
FROM sales s
JOIN Produit p ON s.product_id = p.id_produit
JOIN Utilisateur u ON s.buyer_id = u.id_user
WHERE p.id_vendeur = @sellerId
AND s.created_at BETWEEN @fromDate AND @toDate
ORDER BY s.created_at DESC;

-- Get sales summary for seller
SELECT
    COUNT(*) AS total_sales,
    SUM(s.amount) AS total_revenue,
    AVG(s.amount) AS average_sale_amount,
    COUNT(DISTINCT s.buyer_id) AS unique_customers,
    COUNT(DISTINCT s.product_id) AS products_sold
FROM sales s
JOIN Produit p ON s.product_id = p.id_produit
WHERE p.id_vendeur = @sellerId;

-- Get daily sales for last 30 days
SELECT
    DATE(s.created_at) AS sale_date,
    COUNT(*) AS sales_count,
    SUM(s.amount) AS daily_revenue
FROM sales s
JOIN Produit p ON s.product_id = p.id_produit
WHERE p.id_vendeur = @sellerId
AND s.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY DATE(s.created_at)
ORDER BY sale_date DESC;

-- Get monthly sales summary
SELECT
    YEAR(s.created_at) AS year,
    MONTH(s.created_at) AS month,
    COUNT(*) AS sales_count,
    SUM(s.amount) AS monthly_revenue
FROM sales s
JOIN Produit p ON s.product_id = p.id_produit
WHERE p.id_vendeur = @sellerId
GROUP BY YEAR(s.created_at), MONTH(s.created_at)
ORDER BY year DESC, month DESC;

-- Get best-selling products
SELECT
    p.id_produit,
    p.description,
    p.prix,
    COUNT(s.id) AS sales_count,
    SUM(s.amount) AS total_revenue
FROM Produit p
JOIN sales s ON p.id_produit = s.product_id
WHERE p.id_vendeur = @sellerId
GROUP BY p.id_produit
ORDER BY sales_count DESC
LIMIT 10;

-- ========================================================================
-- REVIEWS & RATINGS
-- ========================================================================

-- Get all reviews for seller's products
SELECT
    r.id,
    r.product_id,
    r.rating,
    r.comment,
    r.created_at,
    r.updated_at,
    p.description AS product_description,
    CONCAT(u.prenom, ' ', u.nom) AS reviewer_name,
    u.avatar AS reviewer_avatar
FROM reviews r
JOIN Produit p ON r.product_id = p.id_produit
JOIN Utilisateur u ON r.user_id = u.id_user
WHERE p.id_vendeur = @sellerId
ORDER BY r.created_at DESC;

-- Get reviews for specific product
SELECT
    r.*,
    CONCAT(u.prenom, ' ', u.nom) AS reviewer_name,
    u.avatar AS reviewer_avatar
FROM reviews r
JOIN Utilisateur u ON r.user_id = u.id_user
WHERE r.product_id = @productId
ORDER BY r.created_at DESC;

-- Get average rating for seller's products
SELECT
    COUNT(DISTINCT r.product_id) AS products_reviewed,
    COUNT(r.id) AS total_reviews,
    AVG(r.rating) AS average_rating,
    SUM(CASE WHEN r.rating = 5 THEN 1 ELSE 0 END) AS five_star,
    SUM(CASE WHEN r.rating = 4 THEN 1 ELSE 0 END) AS four_star,
    SUM(CASE WHEN r.rating = 3 THEN 1 ELSE 0 END) AS three_star,
    SUM(CASE WHEN r.rating = 2 THEN 1 ELSE 0 END) AS two_star,
    SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) AS one_star
FROM reviews r
JOIN Produit p ON r.product_id = p.id_produit
WHERE p.id_vendeur = @sellerId;

-- Get recent negative reviews (rating <= 2)
SELECT
    r.*,
    p.description AS product_description,
    CONCAT(u.prenom, ' ', u.nom) AS reviewer_name
FROM reviews r
JOIN Produit p ON r.product_id = p.id_produit
JOIN Utilisateur u ON r.user_id = u.id_user
WHERE p.id_vendeur = @sellerId
AND r.rating <= 2
ORDER BY r.created_at DESC
LIMIT 10;

-- ========================================================================
-- CATEGORIES
-- ========================================================================

-- Get all categories
SELECT
    id_categorie,
    lib
FROM Categorie
ORDER BY lib ASC;

-- Get category with product count
SELECT
    c.id_categorie,
    c.lib,
    COUNT(p.id_produit) AS product_count
FROM Categorie c
LEFT JOIN Produit p ON c.id_categorie = p.id_categorie AND p.id_vendeur = @sellerId
GROUP BY c.id_categorie
ORDER BY c.lib ASC;

-- ========================================================================
-- AUCTIONS (if product sale_type = 'auction')
-- ========================================================================

-- Get active auctions for seller
SELECT
    a.*,
    p.description AS product_description,
    p.image AS product_image,
    COUNT(DISTINCT b.user_id) AS bidders_count,
    COUNT(b.id) AS total_bids
FROM auctions a
JOIN Produit p ON a.id_produit = p.id_produit
LEFT JOIN bids b ON a.id = b.auction_id
WHERE p.id_vendeur = @sellerId
AND a.status = 'active'
GROUP BY a.id
ORDER BY a.ends_at ASC;

-- Get auction details with bids
SELECT
    b.*,
    CONCAT(u.prenom, ' ', u.nom) AS bidder_name,
    u.email AS bidder_email
FROM bids b
JOIN Utilisateur u ON b.user_id = u.id_user
WHERE b.auction_id = @auctionId
ORDER BY b.amount DESC, b.created_at DESC;

-- Get highest bidder for auction
SELECT
    b.user_id,
    b.amount,
    CONCAT(u.prenom, ' ', u.nom) AS bidder_name,
    u.email AS bidder_email
FROM bids b
JOIN Utilisateur u ON b.user_id = u.id_user
WHERE b.auction_id = @auctionId
ORDER BY b.amount DESC, b.created_at DESC
LIMIT 1;

-- ========================================================================
-- GROUP SALES (if product sale_type = 'group')
-- ========================================================================

-- Get group sale progress
SELECT
    p.id_produit,
    p.description,
    p.group_required_buyers,
    p.group_expires_at,
    COALESCE(SUM(pp.quantity), 0) AS current_buyers,
    p.group_required_buyers - COALESCE(SUM(pp.quantity), 0) AS remaining_buyers,
    CASE
        WHEN COALESCE(SUM(pp.quantity), 0) >= p.group_required_buyers THEN 'SUCCESS'
        WHEN p.group_expires_at < NOW() THEN 'EXPIRED'
        ELSE 'IN_PROGRESS'
    END AS status
FROM Produit p
LEFT JOIN pre_purchases pp ON p.id_produit = pp.id_produit
    AND pp.status IN ('pending', 'confirmed')
WHERE p.id_vendeur = @sellerId
AND p.sale_type = 'group'
GROUP BY p.id_produit;

-- Get buyers for a group sale
SELECT
    pp.*,
    CONCAT(u.prenom, ' ', u.nom) AS buyer_name,
    u.email AS buyer_email
FROM pre_purchases pp
JOIN Utilisateur u ON pp.id_client = u.id_user
WHERE pp.id_produit = @productId
ORDER BY pp.created_at ASC;

-- ========================================================================
-- DASHBOARD STATISTICS
-- ========================================================================

-- Complete seller dashboard stats
SELECT
    -- Products
    (SELECT COUNT(*) FROM Produit WHERE id_vendeur = @sellerId) AS total_products,
    (SELECT COUNT(*) FROM Produit WHERE id_vendeur = @sellerId AND quantity = 0) AS out_of_stock_products,
    (SELECT COUNT(*) FROM Produit WHERE id_vendeur = @sellerId AND quantity <= 5 AND quantity > 0) AS low_stock_products,

    -- Sales
    (SELECT COUNT(*) FROM sales s JOIN Produit p ON s.product_id = p.id_produit WHERE p.id_vendeur = @sellerId) AS total_sales,
    (SELECT COALESCE(SUM(s.amount), 0) FROM sales s JOIN Produit p ON s.product_id = p.id_produit WHERE p.id_vendeur = @sellerId) AS total_revenue,
    (SELECT COUNT(*) FROM sales s JOIN Produit p ON s.product_id = p.id_produit WHERE p.id_vendeur = @sellerId AND DATE(s.created_at) = CURDATE()) AS sales_today,
    (SELECT COALESCE(SUM(s.amount), 0) FROM sales s JOIN Produit p ON s.product_id = p.id_produit WHERE p.id_vendeur = @sellerId AND DATE(s.created_at) = CURDATE()) AS revenue_today,

    -- Reviews
    (SELECT COUNT(*) FROM reviews r JOIN Produit p ON r.product_id = p.id_produit WHERE p.id_vendeur = @sellerId) AS total_reviews,
    (SELECT COALESCE(AVG(r.rating), 0) FROM reviews r JOIN Produit p ON r.product_id = p.id_produit WHERE p.id_vendeur = @sellerId) AS average_rating,

    -- Active auctions
    (SELECT COUNT(*) FROM auctions a JOIN Produit p ON a.id_produit = p.id_produit WHERE p.id_vendeur = @sellerId AND a.status = 'active') AS active_auctions;

-- ========================================================================
-- SEARCH & FILTERING
-- ========================================================================

-- Search products by description
SELECT
    p.*,
    c.lib AS categorie_name
FROM Produit p
LEFT JOIN Categorie c ON p.id_categorie = c.id_categorie
WHERE p.id_vendeur = @sellerId
AND p.description LIKE CONCAT('%', @searchTerm, '%')
ORDER BY p.created_at DESC;

-- Filter products by multiple criteria
SELECT
    p.*,
    c.lib AS categorie_name
FROM Produit p
LEFT JOIN Categorie c ON p.id_categorie = c.id_categorie
WHERE p.id_vendeur = @sellerId
AND (@categoryId IS NULL OR p.id_categorie = @categoryId)
AND (@saleType IS NULL OR p.sale_type = @saleType)
AND (@minPrice IS NULL OR p.prix >= @minPrice)
AND (@maxPrice IS NULL OR p.prix <= @maxPrice)
AND (@inStockOnly = 0 OR p.quantity > 0)
ORDER BY p.created_at DESC;

-- ========================================================================
-- TAX SETTINGS (Site Configuration)
-- ========================================================================

-- Get tax settings
SELECT
    setting_key,
    setting_value
FROM site_settings
WHERE setting_key IN ('tax_rate', 'tax_enabled', 'tax_name');

-- Get specific tax rate
SELECT setting_value
FROM site_settings
WHERE setting_key = 'tax_rate';

-- ========================================================================
-- VALIDATION QUERIES
-- ========================================================================

-- Check if product belongs to seller (security check)
SELECT EXISTS(
    SELECT 1
    FROM Produit
    WHERE id_produit = @productId
    AND id_vendeur = @sellerId
) AS is_owner;

-- Check product stock availability
SELECT
    quantity >= @requestedQuantity AS is_available,
    quantity AS current_stock
FROM Produit
WHERE id_produit = @productId;

-- Check if email already exists
SELECT EXISTS(
    SELECT 1
    FROM Utilisateur
    WHERE email = @email
) AS email_exists;

-- Check if SIRET already exists
SELECT EXISTS(
    SELECT 1
    FROM Vendeur
    WHERE siret = @siret
) AS siret_exists;

-- ========================================================================
-- END OF QUICK REFERENCE
-- ========================================================================
