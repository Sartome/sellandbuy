<?php $pageTitle = 'Tickets support'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <div class="admin-section">
        <div class="admin-header">
            <h1>🎫 Tickets support</h1>
        </div>
        <p>Gérez les demandes envoyées par les utilisateurs et vendeurs.</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert success">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="admin-section">
        <?php if (empty($tickets ?? [])): ?>
            <div class="card">
                <div class="body">
                    <p>Aucun ticket pour le moment.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilisateur</th>
                            <th>Sujet</th>
                            <th>Statut</th>
                            <th>Créé le</th>
                            <th>Message</th>
                            <th>Réponse admin</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td>#<?php echo (int)$ticket['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars(trim(($ticket['prenom'] ?? '') . ' ' . ($ticket['nom'] ?? ''))); ?></strong><br>
                                    <span style="color: var(--muted); font-size: 0.85rem;">
                                        <?php echo htmlspecialchars($ticket['email'] ?? ''); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = '';
                                    $label = '';
                                    if ($ticket['status'] === 'answered') {
                                        $badgeClass = 'active';
                                        $label = 'Répondu';
                                    } elseif ($ticket['status'] === 'closed') {
                                        $badgeClass = 'inactive';
                                        $label = 'Fermé';
                                    } else {
                                        $badgeClass = 'active';
                                        $label = 'Ouvert';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $badgeClass; ?>"><?php echo $label; ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($ticket['created_at']); ?></td>
                                <td style="max-width: 260px; white-space: pre-wrap; word-break: break-word;">
                                    <?php echo nl2br(htmlspecialchars($ticket['message'])); ?>
                                </td>
                                <td style="max-width: 260px; white-space: pre-wrap; word-break: break-word;">
                                    <?php if (!empty($ticket['admin_response'])): ?>
                                        <?php echo nl2br(htmlspecialchars($ticket['admin_response'])); ?>
                                    <?php else: ?>
                                        <span style="color: var(--muted);">Aucune réponse</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" style="margin-bottom: 8px;">
                                        <input type="hidden" name="ticket_id" value="<?php echo (int)$ticket['id']; ?>">
                                        <input type="hidden" name="action" value="answer">
                                        <div class="form-group">
                                            <textarea name="admin_response" rows="3" placeholder="Répondre au ticket..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-reply"></i> Répondre
                                        </button>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="ticket_id" value="<?php echo (int)$ticket['id']; ?>">
                                        <input type="hidden" name="action" value="close">
                                        <button type="submit" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-lock"></i> Fermer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
