<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<div class="container">
    <div class="admin-section">
        <h1><i class="fas fa-life-ring"></i> Centre de tickets</h1>
        <p>Envoyez une demande à l'administration pour toute question ou problème.</p>
    </div>

    <div class="admin-section">
        <div class="card">
            <div class="body">
                <h2>Créer un ticket</h2>
                <?php if (!empty($error)): ?>
                    <div class="alert error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="subject">Sujet</label>
                        <input type="text" id="subject" name="subject" required maxlength="255">
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required rows="5"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Envoyer le ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="admin-section">
        <h2>Mes tickets</h2>
        <?php if (empty($tickets)): ?>
            <div class="card">
                <div class="body">
                    <p>Vous n'avez pas encore créé de ticket.</p>
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
                                <span class="ticket-date"><?php echo htmlspecialchars($ticket['created_at']); ?></span>
                                <?php
                                $statusClass = '';
                                $statusLabel = '';
                                if ($ticket['status'] === 'answered') {
                                    $statusClass = 'answered';
                                    $statusLabel = 'Répondu';
                                } elseif ($ticket['status'] === 'closed') {
                                    $statusClass = 'closed';
                                    $statusLabel = 'Fermé';
                                } else {
                                    $statusClass = 'open';
                                    $statusLabel = 'Ouvert';
                                }
                                ?>
                                <span class="status-badge <?php echo htmlspecialchars($statusClass); ?>"><?php echo $statusLabel; ?></span>
                            </div>
                        </div>

                        <div class="ticket-chat">
                            <div class="message-row user">
                                <div class="message-author">Vous</div>
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
                                        En attente de réponse de l'administration...
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.tickets-chat-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.ticket-card {
    border-radius: var(--radius);
    border: 1px solid var(--border);
    background: var(--card);
    box-shadow: var(--shadow);
    padding: 16px 18px;
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
    font-size: 0.85rem;
    color: var(--muted);
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

@media (max-width: 640px) {
    .ticket-card-header {
        flex-direction: column;
        gap: 6px;
    }
}
</style>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>

