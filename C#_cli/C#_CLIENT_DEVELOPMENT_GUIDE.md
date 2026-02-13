# C# Client Development Guide for Sell & Buy Platform

## Overview
This document provides critical guidance for developing a C# desktop client for sellers to manage their stock, sales, and products in the Sell & Buy platform.

---

## Database Schema Summary

### Main Tables You'll Interact With
1. **Utilisateur** - User authentication and profile
2. **Vendeur** - Seller-specific information
3. **Produit** - Product catalog (with quantity, prices HT/TTC, sale types)
4. **ProduitImages** - Multiple images per product
5. **Categorie** - Product categories
6. **sales** - Sales transaction history
7. **auctions** - Auction management
8. **reviews** - Customer reviews
9. **pre_purchases** - Group sale reservations
10. **site_settings** - Tax rates and configuration

---

## Critical Warnings & Security Considerations

### 1. PASSWORD SECURITY
```csharp
// ⚠️ NEVER store passwords in plain text!
// The database stores bcrypt hashed passwords

// For authentication, hash passwords before sending to API
using BCrypt.Net;

string hashedPassword = BCrypt.Net.BCrypt.HashPassword(plainTextPassword);
bool isValid = BCrypt.Net.BCrypt.Verify(plainTextPassword, hashedPasswordFromDB);
```

**Recommended Package**: `BCrypt.Net-Next`

### 2. SQL INJECTION PROTECTION
```csharp
// ❌ NEVER do this:
string query = $"SELECT * FROM Produit WHERE id_vendeur = {sellerId}";

// ✅ ALWAYS use parameterized queries:
using var cmd = new MySqlCommand("SELECT * FROM Produit WHERE id_vendeur = @sellerId", connection);
cmd.Parameters.AddWithValue("@sellerId", sellerId);
```

**Recommended Package**: `MySql.Data` or `Dapper` for cleaner syntax

### 3. CONNECTION STRING SECURITY
```csharp
// ⚠️ NEVER hardcode database credentials in code!
// Use app.config or appsettings.json with encryption

<connectionStrings>
  <add name="VenteGroupeDB"
       connectionString="Server=localhost;Database=vente_groupe;Uid=your_user;Pwd=your_password;charset=utf8mb4;"
       providerName="MySql.Data.MySqlClient" />
</connectionStrings>

// Or use User Secrets for development:
// dotnet user-secrets set "ConnectionStrings:VenteGroupeDB" "your_connection_string"
```

### 4. FILE UPLOAD SECURITY
```csharp
// When uploading product images:
// - Validate file extensions (jpg, png, gif only)
// - Check file size limits (e.g., max 5MB)
// - Sanitize filenames
// - Use antivirus scanning if possible
// - Store outside web root or in cloud storage (AWS S3, Azure Blob)

private bool IsValidImageFile(string fileName, long fileSize)
{
    var allowedExtensions = new[] { ".jpg", ".jpeg", ".png", ".gif" };
    var extension = Path.GetExtension(fileName).ToLowerInvariant();

    return allowedExtensions.Contains(extension) && fileSize <= 5 * 1024 * 1024; // 5MB
}
```

---

## Database Connection Setup

### Recommended NuGet Packages
```bash
dotnet add package MySql.Data
dotnet add package Dapper
dotnet add package BCrypt.Net-Next
dotnet add package Newtonsoft.Json  # For JSON handling
```

### Basic Connection Class
```csharp
using MySql.Data.MySqlClient;
using Dapper;

public class DatabaseConnection
{
    private readonly string _connectionString;

    public DatabaseConnection(string connectionString)
    {
        _connectionString = connectionString;
    }

    public MySqlConnection GetConnection()
    {
        var connection = new MySqlConnection(_connectionString);
        connection.Open();
        return connection;
    }
}
```

---

## Important Business Rules

### 1. Product Management

#### Price Management (HT/TTC)
```csharp
public class PriceCalculator
{
    // French TVA is typically 20%
    public static decimal CalculateTTC(decimal prixHT, decimal tauxTVA = 20.00m)
    {
        return prixHT * (1 + (tauxTVA / 100));
    }

    public static decimal CalculateHT(decimal prixTTC, decimal tauxTVA = 20.00m)
    {
        return prixTTC / (1 + (tauxTVA / 100));
    }
}

// Example usage:
decimal prixHT = 100.00m;
decimal prixTTC = PriceCalculator.CalculateTTC(prixHT, 20.00m); // = 120.00
```

#### Stock Management
```csharp
// Before allowing a sale, ALWAYS check stock
public async Task<bool> IsInStockAsync(int productId, int requestedQuantity)
{
    using var conn = _db.GetConnection();
    var sql = "SELECT quantity FROM Produit WHERE id_produit = @productId";
    var quantity = await conn.QueryFirstOrDefaultAsync<int?>(sql, new { productId });

    return quantity.HasValue && quantity.Value >= requestedQuantity;
}

// After a sale, decrease stock
public async Task<bool> DecraseStockAsync(int productId, int soldQuantity)
{
    using var conn = _db.GetConnection();
    var sql = @"UPDATE Produit
                SET quantity = quantity - @soldQuantity
                WHERE id_produit = @productId
                AND quantity >= @soldQuantity";

    var affected = await conn.ExecuteAsync(sql, new { productId, soldQuantity });
    return affected > 0;
}
```

### 2. Sale Type Handling

```csharp
public enum SaleType
{
    Buy,      // Direct purchase
    Auction,  // Auction sale
    Group     // Group purchase (requires minimum buyers)
}

public class Product
{
    public int Id { get; set; }
    public string Description { get; set; }
    public decimal Prix { get; set; }
    public decimal PrixHT { get; set; }
    public decimal TauxTVA { get; set; }
    public int Quantity { get; set; }
    public SaleType SaleType { get; set; }
    public int? GroupRequiredBuyers { get; set; }
    public DateTime? GroupExpiresAt { get; set; }
}
```

### 3. Multi-Image Support

```csharp
// When creating/editing a product, handle multiple images:
public class ProductImage
{
    public int IdImage { get; set; }
    public int IdProduit { get; set; }
    public string ImagePath { get; set; }
    public string ImageAlt { get; set; }
    public bool IsPrimary { get; set; }
    public int SortOrder { get; set; }
}

public async Task<List<ProductImage>> GetProductImagesAsync(int productId)
{
    using var conn = _db.GetConnection();
    var sql = @"SELECT * FROM ProduitImages
                WHERE id_produit = @productId
                ORDER BY sort_order";

    return (await conn.QueryAsync<ProductImage>(sql, new { productId })).ToList();
}
```

---

## Key Features for Your C# Client

### 1. Seller Dashboard
```csharp
// Display seller statistics
public class SellerDashboard
{
    public int TotalProducts { get; set; }
    public int TotalSales { get; set; }
    public decimal TotalRevenue { get; set; }
    public decimal AverageRating { get; set; }
    public int LowStockProducts { get; set; }  // quantity < 5
}

public async Task<SellerDashboard> GetDashboardAsync(int sellerId)
{
    using var conn = _db.GetConnection();
    var result = await conn.QueryFirstOrDefaultAsync<SellerDashboard>(@"
        CALL sp_get_seller_dashboard(@sellerId)",
        new { sellerId });

    return result;
}
```

### 2. Product Management UI Features

**Essential Features:**
- Create/Edit/Delete products
- Upload multiple images with drag-and-drop
- Set product type (Buy/Auction/Group)
- Configure group sale parameters (min buyers, expiration)
- Manage stock quantities with low-stock alerts
- Price calculator (auto-calculate HT ↔ TTC)
- Category selection dropdown
- Image gallery with sort order

**UI Recommendations:**
- Use DataGridView for product list
- Use TabControl for product details sections
- Implement search/filter by category, sale type, stock level
- Show real-time stock alerts

### 3. Sales History
```csharp
public class SaleRecord
{
    public int Id { get; set; }
    public int ProductId { get; set; }
    public string ProductDescription { get; set; }
    public int BuyerId { get; set; }
    public string BuyerName { get; set; }
    public decimal Amount { get; set; }
    public DateTime CreatedAt { get; set; }
}

public async Task<List<SaleRecord>> GetSellerSalesAsync(int sellerId, DateTime? fromDate = null, DateTime? toDate = null)
{
    using var conn = _db.GetConnection();
    var sql = @"
        SELECT s.*, p.description AS ProductDescription,
               CONCAT(u.prenom, ' ', u.nom) AS BuyerName
        FROM sales s
        JOIN Produit p ON s.product_id = p.id_produit
        JOIN Utilisateur u ON s.buyer_id = u.id_user
        WHERE p.id_vendeur = @sellerId
        AND (@fromDate IS NULL OR s.created_at >= @fromDate)
        AND (@toDate IS NULL OR s.created_at <= @toDate)
        ORDER BY s.created_at DESC";

    return (await conn.QueryAsync<SaleRecord>(sql, new { sellerId, fromDate, toDate })).ToList();
}
```

### 4. Review Management
```csharp
public class ProductReview
{
    public int Id { get; set; }
    public int ProductId { get; set; }
    public string UserName { get; set; }
    public int Rating { get; set; }  // 1-5
    public string Comment { get; set; }
    public DateTime CreatedAt { get; set; }
}

public async Task<List<ProductReview>> GetProductReviewsAsync(int productId)
{
    using var conn = _db.GetConnection();
    var sql = @"
        SELECT r.*, CONCAT(u.prenom, ' ', u.nom) AS UserName
        FROM reviews r
        JOIN Utilisateur u ON r.user_id = u.id_user
        WHERE r.product_id = @productId
        ORDER BY r.created_at DESC";

    return (await conn.QueryAsync<ProductReview>(sql, new { productId })).ToList();
}
```

---

## Data Validation Rules

### Seller Registration
- **SIRET**: Must be exactly 14 digits (French business registration)
- **Email**: Must be unique and valid format
- **Company Name**: Required, max 100 characters
- **Password**: Minimum 8 characters, at least 1 uppercase, 1 lowercase, 1 digit

```csharp
public class ValidationRules
{
    public static bool IsValidSIRET(string siret)
    {
        return !string.IsNullOrWhiteSpace(siret) &&
               siret.Length == 14 &&
               siret.All(char.IsDigit);
    }

    public static bool IsValidEmail(string email)
    {
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
}
```

### Product Data
- **Description**: Required, max 255 characters
- **Price**: Must be > 0
- **Quantity**: Must be >= 0
- **Group Required Buyers**: Required if sale_type = 'group', must be > 1
- **Group Expires At**: Required if sale_type = 'group', must be future date

---

## Performance Optimization Tips

### 1. Use Connection Pooling
```xml
<!-- In connection string -->
Server=localhost;Database=vente_groupe;Uid=user;Pwd=pass;
Pooling=true;Min Pool Size=5;Max Pool Size=20;
```

### 2. Implement Caching
```csharp
// Cache categories, site settings, etc.
private static List<Category> _cachedCategories;
private static DateTime _categoriesCacheExpiry;

public async Task<List<Category>> GetCategoriesAsync()
{
    if (_cachedCategories != null && DateTime.Now < _categoriesCacheExpiry)
    {
        return _cachedCategories;
    }

    using var conn = _db.GetConnection();
    _cachedCategories = (await conn.QueryAsync<Category>("SELECT * FROM Categorie")).ToList();
    _categoriesCacheExpiry = DateTime.Now.AddMinutes(30);

    return _cachedCategories;
}
```

### 3. Use Async/Await Throughout
```csharp
// Always use async methods for database operations
// to keep UI responsive

private async void btnLoadProducts_Click(object sender, EventArgs e)
{
    try
    {
        btnLoadProducts.Enabled = false;
        lblStatus.Text = "Loading...";

        var products = await _productService.GetProductsBySeller(sellerId);

        dgvProducts.DataSource = products;
        lblStatus.Text = $"Loaded {products.Count} products";
    }
    catch (Exception ex)
    {
        MessageBox.Show($"Error: {ex.Message}", "Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
    }
    finally
    {
        btnLoadProducts.Enabled = true;
    }
}
```

---

## Common Pitfalls to Avoid

### 1. Typo in Database Column
⚠️ The `Participation` table has a typo: `id_particiption` (should be `id_participation`)
```csharp
// Use the actual column name from the database:
public class Participation
{
    // Note the typo in the actual database column
    public int Id_particiption { get; set; }  // Not id_participation!
    public int IdClient { get; set; }
    public int IdPrevente { get; set; }
    public int? IdFacture { get; set; }
}
```

### 2. Blocking Status Check
```csharp
// ALWAYS check if seller is blocked before allowing operations
public async Task<bool> IsSellerBlockedAsync(int sellerId)
{
    using var conn = _db.GetConnection();
    var sql = "SELECT is_blocked FROM Vendeur WHERE id_user = @sellerId";
    return await conn.QueryFirstOrDefaultAsync<bool>(sql, new { sellerId });
}
```

### 3. Character Encoding
```csharp
// Ensure UTF-8 encoding for French characters (é, è, à, ç, etc.)
// Add to connection string:
"charset=utf8mb4;"
```

### 4. Decimal Precision
```csharp
// Use decimal for prices, NOT float or double
decimal prix = 19.99m;  // ✅ Correct
float prix = 19.99f;    // ❌ Will lose precision
```

---

## Testing Recommendations

### 1. Unit Test Critical Business Logic
```csharp
[TestClass]
public class PriceCalculatorTests
{
    [TestMethod]
    public void CalculateTTC_WithStandardVAT_ReturnsCorrectPrice()
    {
        // Arrange
        decimal prixHT = 100.00m;
        decimal tauxTVA = 20.00m;

        // Act
        decimal prixTTC = PriceCalculator.CalculateTTC(prixHT, tauxTVA);

        // Assert
        Assert.AreEqual(120.00m, prixTTC);
    }
}
```

### 2. Integration Tests with Test Database
```csharp
[TestInitialize]
public void Setup()
{
    // Use a separate test database
    _connectionString = "Server=localhost;Database=vente_groupe_test;...";
    // Run migrations/setup scripts
}

[TestCleanup]
public void Cleanup()
{
    // Clean up test data after each test
}
```

---

## Recommended Architecture

### Layered Architecture
```
┌─────────────────────────────┐
│   Presentation Layer (UI)   │  ← Windows Forms / WPF
├─────────────────────────────┤
│   Business Logic Layer      │  ← Services, Validators
├─────────────────────────────┤
│   Data Access Layer (DAL)   │  ← Repositories, Dapper
├─────────────────────────────┤
│   Database (MySQL)          │  ← vente_groupe
└─────────────────────────────┘
```

### Example Project Structure
```
SellerClient/
├── SellerClient.UI/              (Windows Forms project)
│   ├── Forms/
│   │   ├── MainDashboard.cs
│   │   ├── ProductManagement.cs
│   │   ├── SalesHistory.cs
│   │   └── Settings.cs
│   └── Program.cs
├── SellerClient.Core/            (Class Library)
│   ├── Models/
│   │   ├── Product.cs
│   │   ├── Seller.cs
│   │   └── Sale.cs
│   ├── Services/
│   │   ├── ProductService.cs
│   │   ├── SaleService.cs
│   │   └── AuthService.cs
│   └── Validators/
│       └── ProductValidator.cs
└── SellerClient.Data/            (Class Library)
    ├── Repositories/
    │   ├── ProductRepository.cs
    │   └── SaleRepository.cs
    └── DatabaseConnection.cs
```

---

## Additional Features to Consider

### 1. Offline Mode
- Cache product data locally (SQLite)
- Queue changes and sync when online
- Visual indicator of sync status

### 2. Export Functionality
```csharp
// Export sales history to CSV/Excel
public void ExportSalesToCSV(List<SaleRecord> sales, string filePath)
{
    using var writer = new StreamWriter(filePath, false, Encoding.UTF8);
    using var csv = new CsvWriter(writer, CultureInfo.InvariantCulture);

    csv.WriteRecords(sales);
}
```

### 3. Notifications
- Low stock alerts
- New orders notifications
- New reviews notifications

### 4. Analytics Dashboard
- Sales trends (daily/weekly/monthly)
- Best-selling products
- Revenue charts
- Average ratings over time

---

## Support & Resources

### MySQL Connector/NET Documentation
https://dev.mysql.com/doc/connector-net/en/

### Dapper Documentation
https://github.com/DapperLib/Dapper

### BCrypt.NET Documentation
https://github.com/BcryptNet/bcrypt.net

---

## Final Recommendations

1. **Use an ORM or Micro-ORM**: Dapper is recommended for performance and simplicity
2. **Implement Logging**: Use NLog or Serilog to track errors and operations
3. **Error Handling**: Always wrap database operations in try-catch blocks
4. **User Feedback**: Show loading indicators for long operations
5. **Validation**: Validate on both client and server side
6. **Backup**: Regularly backup the database
7. **Version Control**: Use Git to track code changes
8. **Documentation**: Document your API calls and business logic
9. **Testing**: Write unit tests for critical business logic
10. **Security First**: Never trust user input, always validate and sanitize

---

## Contact & Questions

For database schema questions or API integration:
- Review the SQL comments in vente_groupe_FINAL.sql
- Check the PHP models in `/models/` directory
- Test queries in MySQL Workbench before implementing in C#

Good luck with your C# client development!
