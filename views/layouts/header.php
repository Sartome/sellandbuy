<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
