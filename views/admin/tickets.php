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
            <div class="tickets-chat-list">
                <?php foreach ($tickets as $ticket): ?>
                    <div class="ticket-card">
                        <div class="ticket-card-header">
                            <div class="ticket-card-title">
                                <span class="ticket-id">#<?php echo (int)$ticket['id']; ?></span>
                                <span class="ticket-subject"><?php echo htmlspecialchars($ticket['subject']); ?></span>
                            </div>
                            <div class="ticket-card-meta">
                                <div class="ticket-user">
                                    <strong><?php echo htmlspecialchars(trim(($ticket['prenom'] ?? '') . ' ' . ($ticket['nom'] ?? ''))); ?></strong>
                                    <span class="ticket-email"><?php echo htmlspecialchars($ticket['email'] ?? ''); ?></span>
                                </div>
                                <span class="ticket-date"><?php echo htmlspecialchars($ticket['created_at']); ?></span>
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
                            </div>
                        </div>

                        <div class="ticket-chat">
                            <div class="message-row user">
                                <div class="message-author">Utilisateur</div>
                                <div class="message-bubble">
                                    <?php echo nl2br(htmlspecialchars($ticket['message'])); ?>
                                </div>
                            </div>

                            <?php if (!empty($ticket['admin_response'])): ?>
                                <div class="message-row admin">
                                    <div class="message-author">Administration</div>
                                    <div class="message-bubble">
                                        <?php echo nl2br(htmlspecialchars($ticket['admin_response'])); ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="message-row system">
                                    <div class="message-bubble waiting">
                                        Aucune réponse envoyée pour le moment.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="ticket-actions">
                            <form method="POST" class="ticket-reply-form">
                                <input type="hidden" name="ticket_id" value="<?php echo (int)$ticket['id']; ?>">
                                <input type="hidden" name="action" value="answer">
                                <div class="form-group">
                                    <textarea name="admin_response" rows="3" placeholder="Répondre à ce ticket..."></textarea>
                                </div>
                                <div class="ticket-actions-buttons">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-reply"></i> Envoyer la réponse
                                    </button>
                                    <form method="POST" class="ticket-close-form">
                                        <input type="hidden" name="ticket_id" value="<?php echo (int)$ticket['id']; ?>">
                                        <input type="hidden" name="action" value="close">
                                        <button type="submit" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-lock"></i> Fermer le ticket
                                        </button>
                                    </form>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.tickets-chat-list {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.ticket-card {
    border-radius: var(--radius);
    border: 1px solid var(--border);
    background: var(--card);
    box-shadow: var(--shadow);
    padding: 18px 20px;
}

.ticket-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.ticket-card-title {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: baseline;
}

.ticket-id {
    font-weight: 600;
    color: var(--muted);
}

.ticket-subject {
    font-weight: 600;
    color: var(--text);
}

.ticket-card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    justify-content: flex-end;
    font-size: 0.85rem;
    color: var(--muted);
}

.ticket-user {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.ticket-email {
    font-size: 0.8rem;
}

.ticket-chat {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 8px;
}

.message-row {
    display: flex;
    flex-direction: column;
    max-width: 100%;
}

.message-row.user {
    align-items: flex-start;
}

.message-row.admin {
    align-items: flex-end;
}

.message-row.system {
    align-items: center;
}

.message-author {
    font-size: 0.8rem;
    color: var(--muted);
    margin-bottom: 2px;
}

.message-bubble {
    padding: 10px 12px;
    border-radius: 16px;
    font-size: 0.9rem;
    line-height: 1.4;
    max-width: 100%;
    word-wrap: break-word;
}

.message-row.user .message-bubble {
    background: rgba(37,99,235,0.08);
    border: 1px solid rgba(37,99,235,0.35);
}

.message-row.admin .message-bubble {
    background: rgba(16,185,129,0.08);
    border: 1px solid rgba(16,185,129,0.35);
}

.message-row.system .message-bubble.waiting {
    background: rgba(148,163,184,0.12);
    border: 1px solid rgba(148,163,184,0.4);
    color: var(--muted);
    font-style: italic;
}

.ticket-actions {
    margin-top: 12px;
    border-top: 1px solid var(--border);
    padding-top: 10px;
}

.ticket-reply-form textarea {
    width: 100%;
    resize: vertical;
}

.ticket-actions-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}

.ticket-close-form {
    display: inline-block;
}

@media (max-width: 640px) {
    .ticket-card-header {
        flex-direction: column;
        gap: 6px;
    }

    .ticket-card-meta {
        justify-content: flex-start;
    }
}
</style>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
