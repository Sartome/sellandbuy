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
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Sujet</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Réponse admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td>#<?php echo (int)$ticket['id']; ?></td>
                                <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo htmlspecialchars($ticket['status']); ?>">
                                        <?php
                                        if ($ticket['status'] === 'answered') {
                                            echo 'Répondu';
                                        } elseif ($ticket['status'] === 'closed') {
                                            echo 'Fermé';
                                        } else {
                                            echo 'Ouvert';
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($ticket['created_at']); ?></td>
                                <td>
                                    <?php if (!empty($ticket['admin_response'])): ?>
                                        <div style="max-width: 320px; white-space: pre-wrap; word-break: break-word;">
                                            <?php echo nl2br(htmlspecialchars($ticket['admin_response'])); ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: var(--muted);">En attente de réponse</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
