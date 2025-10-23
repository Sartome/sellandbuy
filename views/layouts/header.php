<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php 
    $nonce = generateNonce();
    $_SESSION['csp_nonce'] = $nonce;
    ?>
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com; img-src 'self' data: blob:; connect-src 'self';">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
    <title><?php echo $pageTitle ?? 'Sell & Buy'; ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php if (!empty($_SESSION['message'])): ?>
        <div class="alert <?php echo $_SESSION['message_type'] ?? 'info'; ?>">
            <i class="fas fa-<?php echo ($_SESSION['message_type'] ?? 'info') === 'error' ? 'exclamation-triangle' : 'info-circle'; ?>"></i>
            <?php echo htmlspecialchars($_SESSION['message']); ?>
        </div>
        <?php 
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        ?>
    <?php endif; ?>
