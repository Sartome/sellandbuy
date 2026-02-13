// ========================================================================
// C# MODEL CLASSES FOR SELL & BUY PLATFORM
// Ready-to-use entity classes matching the database schema
// ========================================================================

using System;
using System.Collections.Generic;

namespace SellerClient.Core.Models
{
    // ========================================================================
    // USERS & ROLES
    // ========================================================================

    public class Utilisateur
    {
        public int IdUser { get; set; }
        public string Nom { get; set; }
        public string Prenom { get; set; }
        public string Adresse { get; set; }
        public string Phone { get; set; }
        public string Avatar { get; set; }
        public string Email { get; set; }
        public string MotDePasse { get; set; }  // Hashed password
        public DateTime CreatedAt { get; set; }
        public DateTime UpdatedAt { get; set; }

        // Computed property
        public string FullName => $"{Prenom} {Nom}";
    }

    public class Vendeur
    {
        public int IdUser { get; set; }
        public string NomEntreprise { get; set; }
        public string Siret { get; set; }
        public string AdresseEntreprise { get; set; }
        public string EmailPro { get; set; }
        public bool IsCertified { get; set; }
        public bool IsBlocked { get; set; }
        public DateTime CreatedAt { get; set; }
        public DateTime UpdatedAt { get; set; }

        // Navigation property
        public Utilisateur User { get; set; }
    }

    public class Client
    {
        public int IdUser { get; set; }

        // Navigation property
        public Utilisateur User { get; set; }
    }

    public class Gestionnaire
    {
        public int IdUser { get; set; }

        // Navigation property
        public Utilisateur User { get; set; }
    }

    // ========================================================================
    // PRODUCTS & CATEGORIES
    // ========================================================================

    public enum SaleType
    {
        Buy,
        Auction,
        Group
    }

    public class Categorie
    {
        public int IdCategorie { get; set; }
        public int IdGestionnaire { get; set; }
        public string Lib { get; set; }
        public DateTime CreatedAt { get; set; }
        public DateTime UpdatedAt { get; set; }
    }

    public class Produit
    {
        public int IdProduit { get; set; }
        public string Description { get; set; }
        public decimal Prix { get; set; }               // TTC price
        public decimal PrixHT { get; set; }             // HT price
        public decimal TauxTVA { get; set; }            // VAT rate (%)
        public int Quantity { get; set; }
        public string Image { get; set; }               // Primary image
        public string ImageAlt { get; set; }
        public int? ImageSize { get; set; }
        public int? ImageWidth { get; set; }
        public int? ImageHeight { get; set; }
        public int IdVendeur { get; set; }
        public int? IdCategorie { get; set; }
        public SaleType SaleType { get; set; }
        public int? GroupRequiredBuyers { get; set; }
        public DateTime? GroupExpiresAt { get; set; }
        public DateTime CreatedAt { get; set; }
        public DateTime UpdatedAt { get; set; }

        // Navigation properties
        public Vendeur Vendeur { get; set; }
        public Categorie Categorie { get; set; }
        public List<ProduitImage> Images { get; set; }
        public List<Review> Reviews { get; set; }

        // Computed properties
        public bool IsLowStock => Quantity <= 5;
        public bool IsOutOfStock => Quantity <= 0;
        public string SaleTypeDisplay => SaleType.ToString();
    }

    public class ProduitImage
    {
        public int IdImage { get; set; }
        public int IdProduit { get; set; }
        public string ImagePath { get; set; }
        public string ImageAlt { get; set; }
        public int? ImageSize { get; set; }
        public int? ImageWidth { get; set; }
        public int? ImageHeight { get; set; }
        public bool IsPrimary { get; set; }
        public int SortOrder { get; set; }
        public DateTime CreatedAt { get; set; }

        // Navigation property
        public Produit Produit { get; set; }
    }

    // ========================================================================
    // SALES & TRANSACTIONS
    // ========================================================================

    public class Sale
    {
        public int Id { get; set; }
        public int ProductId { get; set; }
        public int BuyerId { get; set; }
        public decimal Amount { get; set; }
        public DateTime CreatedAt { get; set; }

        // Navigation properties
        public Produit Product { get; set; }
        public Utilisateur Buyer { get; set; }
    }

    public class Facture
    {
        public int IdFacture { get; set; }
        public DateTime DateFacture { get; set; }
        public string PdfFacture { get; set; }
        public DateTime CreatedAt { get; set; }
        public DateTime UpdatedAt { get; set; }
    }

    // ========================================================================
    // GROUP SALES / PRE-SALES
    // ========================================================================

    public class Prevente
    {
        public int IdPrevente { get; set; }
        public DateTime DateLimite { get; set; }
        public int NombreMin { get; set; }
        public string Statut { get; set; }              // 'en cours', 'validée', 'annulée'
        public decimal PrixPrevente { get; set; }
        public int IdProduit { get; set; }
        public DateTime CreatedAt { get; set; }
        public DateTime UpdatedAt { get; set; }

        // Navigation property
        public Produit Produit { get; set; }
        public List<Participation> Participations { get; set; }
    }

    public class Participation
    {
        // ⚠️ NOTE: There's a typo in the actual database column name
        public int Id_particiption { get; set; }        // Actual column name (with typo)
        public int IdClient { get; set; }
        public int IdPrevente { get; set; }
        public int? IdFacture { get; set; }
        public DateTime CreatedAt { get; set; }
        public DateTime UpdatedAt { get; set; }

        // Navigation properties
        public Client Client { get; set; }
        public Prevente Prevente { get; set; }
        public Facture Facture { get; set; }
    }

    public enum PrePurchaseStatus
    {
        Pending,
        Confirmed,
        Cancelled,
        Expired
    }

    public class PrePurchase
    {
        public int Id { get; set; }
        public int IdProduit { get; set; }
        public int IdClient { get; set; }
        public int Quantity { get; set; }
        public DateTime? ExpiresAt { get; set; }
        public PrePurchaseStatus Status { get; set; }
        public DateTime CreatedAt { get; set; }

        // Navigation properties
        public Produit Produit { get; set; }
        public Client Client { get; set; }
    }

    // ========================================================================
    // AUCTIONS
    // ========================================================================

    public enum AuctionStatus
    {
        Active,
        Ended
    }

    public class Auction
    {
        public int Id { get; set; }
        public int IdProduit { get; set; }
        public decimal StartingPrice { get; set; }
        public decimal CurrentPrice { get; set; }
        public DateTime EndsAt { get; set; }
        public AuctionStatus Status { get; set; }
        public DateTime CreatedAt { get; set; }

        // Navigation properties
        public Produit Produit { get; set; }
        public List<Bid> Bids { get; set; }

        // Computed properties
        public bool IsActive => Status == AuctionStatus.Active && EndsAt > DateTime.Now;
        public TimeSpan TimeRemaining => EndsAt - DateTime.Now;
    }

    public class Bid
    {
        public int Id { get; set; }
        public int AuctionId { get; set; }
        public int UserId { get; set; }
        public decimal Amount { get; set; }
        public DateTime CreatedAt { get; set; }

        // Navigation properties
        public Auction Auction { get; set; }
        public Utilisateur User { get; set; }
    }

    // ========================================================================
    // REVIEWS & RATINGS
    // ========================================================================

    public class Review
    {
        public int Id { get; set; }
        public int ProductId { get; set; }
        public int UserId { get; set; }
        public int Rating { get; set; }                 // 1-5
        public string Comment { get; set; }
        public DateTime CreatedAt { get; set; }
        public DateTime UpdatedAt { get; set; }

        // Navigation properties
        public Produit Product { get; set; }
        public Utilisateur User { get; set; }

        // Validation
        public bool IsValidRating => Rating >= 1 && Rating <= 5;
    }

    // ========================================================================
    // SUPPORT & MODERATION
    // ========================================================================

    public enum TicketStatus
    {
        Open,
        Answered,
        Closed
    }

    public class Ticket
    {
        public int Id { get; set; }
        public int UserId { get; set; }
        public string Subject { get; set; }
        public string Message { get; set; }
        public string AdminResponse { get; set; }
        public TicketStatus Status { get; set; }
        public DateTime CreatedAt { get; set; }
        public DateTime UpdatedAt { get; set; }

        // Navigation property
        public Utilisateur User { get; set; }
    }

    public enum SignalStatus
    {
        Pending,
        Reviewed,
        Resolved
    }

    public class Signaler
    {
        public int IdSignal { get; set; }
        public int IdUser { get; set; }
        public int IdProduit { get; set; }
        public DateTime DateSignal { get; set; }
        public string Reason { get; set; }
        public SignalStatus Status { get; set; }

        // Navigation properties
        public Utilisateur User { get; set; }
        public Produit Produit { get; set; }
    }

    public class Bloquer
    {
        public int IdBloquer { get; set; }
        public int IdGestionnaire { get; set; }
        public int IdVendeur { get; set; }
        public string Raison { get; set; }
        public DateTime DateBlocage { get; set; }

        // Navigation properties
        public Gestionnaire Gestionnaire { get; set; }
        public Vendeur Vendeur { get; set; }
    }

    public class Debloquer
    {
        public int IdDebloquer { get; set; }
        public int IdGestionnaire { get; set; }
        public int IdVendeur { get; set; }
        public string Raison { get; set; }
        public DateTime DateDeblocage { get; set; }

        // Navigation properties
        public Gestionnaire Gestionnaire { get; set; }
        public Vendeur Vendeur { get; set; }
    }

    // ========================================================================
    // CONFIGURATION
    // ========================================================================

    public class SiteSetting
    {
        public int Id { get; set; }
        public string SettingKey { get; set; }
        public string SettingValue { get; set; }
        public string Description { get; set; }
        public DateTime CreatedAt { get; set; }
        public DateTime UpdatedAt { get; set; }
    }

    // ========================================================================
    // DATA TRANSFER OBJECTS (DTOs)
    // ========================================================================

    // Use DTOs for complex queries and view models

    public class ProductListDTO
    {
        public int IdProduit { get; set; }
        public string Description { get; set; }
        public decimal Prix { get; set; }
        public int Quantity { get; set; }
        public string SaleType { get; set; }
        public string CategoryName { get; set; }
        public string Image { get; set; }
        public int SalesCount { get; set; }
        public decimal AverageRating { get; set; }
        public DateTime CreatedAt { get; set; }
    }

    public class SellerDashboardDTO
    {
        public int TotalProducts { get; set; }
        public int TotalSales { get; set; }
        public decimal TotalRevenue { get; set; }
        public decimal AverageRating { get; set; }
        public int LowStockProducts { get; set; }
        public int PendingTickets { get; set; }
    }

    public class SaleRecordDTO
    {
        public int Id { get; set; }
        public int ProductId { get; set; }
        public string ProductDescription { get; set; }
        public string BuyerName { get; set; }
        public string BuyerEmail { get; set; }
        public decimal Amount { get; set; }
        public DateTime CreatedAt { get; set; }
    }

    public class ProductReviewDTO
    {
        public int Id { get; set; }
        public int ProductId { get; set; }
        public string ProductDescription { get; set; }
        public string UserName { get; set; }
        public int Rating { get; set; }
        public string Comment { get; set; }
        public DateTime CreatedAt { get; set; }
    }

    // ========================================================================
    // VALIDATION MODELS
    // ========================================================================

    public class CreateProductRequest
    {
        public string Description { get; set; }
        public decimal PrixHT { get; set; }
        public decimal TauxTVA { get; set; }
        public int Quantity { get; set; }
        public int IdVendeur { get; set; }
        public int? IdCategorie { get; set; }
        public SaleType SaleType { get; set; }
        public int? GroupRequiredBuyers { get; set; }
        public DateTime? GroupExpiresAt { get; set; }
        public List<string> ImagePaths { get; set; }

        // Computed
        public decimal PrixTTC => PrixHT * (1 + (TauxTVA / 100));

        // Validation
        public bool IsValid()
        {
            if (string.IsNullOrWhiteSpace(Description) || Description.Length > 255)
                return false;

            if (PrixHT <= 0)
                return false;

            if (Quantity < 0)
                return false;

            if (SaleType == SaleType.Group)
            {
                if (!GroupRequiredBuyers.HasValue || GroupRequiredBuyers.Value < 2)
                    return false;

                if (!GroupExpiresAt.HasValue || GroupExpiresAt.Value <= DateTime.Now)
                    return false;
            }

            return true;
        }
    }

    public class UpdateProductRequest
    {
        public int IdProduit { get; set; }
        public string Description { get; set; }
        public decimal? PrixHT { get; set; }
        public decimal? TauxTVA { get; set; }
        public int? Quantity { get; set; }
        public int? IdCategorie { get; set; }
    }

    // ========================================================================
    // UTILITY CLASSES
    // ========================================================================

    public static class PriceCalculator
    {
        public static decimal CalculateTTC(decimal prixHT, decimal tauxTVA)
        {
            return prixHT * (1 + (tauxTVA / 100));
        }

        public static decimal CalculateHT(decimal prixTTC, decimal tauxTVA)
        {
            return prixTTC / (1 + (tauxTVA / 100));
        }

        public static decimal CalculateTVAAmount(decimal prixHT, decimal tauxTVA)
        {
            return prixHT * (tauxTVA / 100);
        }
    }

    public static class ValidationHelper
    {
        public static bool IsValidSIRET(string siret)
        {
            if (string.IsNullOrWhiteSpace(siret))
                return false;

            siret = siret.Replace(" ", "");
            return siret.Length == 14 && siret.All(char.IsDigit);
        }

        public static bool IsValidEmail(string email)
        {
            if (string.IsNullOrWhiteSpace(email))
                return false;

            try
            {
                var addr = new System.Net.Mail.MailAddress(email);
                return addr.Address == email;
            }
            catch
            {
                return false;
            }
        }

        public static bool IsValidPassword(string password)
        {
            if (string.IsNullOrWhiteSpace(password) || password.Length < 8)
                return false;

            bool hasUpperCase = password.Any(char.IsUpper);
            bool hasLowerCase = password.Any(char.IsLower);
            bool hasDigit = password.Any(char.IsDigit);

            return hasUpperCase && hasLowerCase && hasDigit;
        }
    }
}
