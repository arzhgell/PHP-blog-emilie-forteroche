<?php 
    /* 
     * Comments management page display
     */

    // Function to generate sort links
    function getSortLink($field, $currentSortBy, $currentSortOrder, $currentPage, $commentsPerPage) {
        $newOrder = ($currentSortBy === $field && $currentSortOrder === 'asc') ? 'desc' : 'asc';
        return "index.php?action=showComments&sort={$field}&order={$newOrder}&page={$currentPage}&per_page={$commentsPerPage}";
    }
    
    // Function to generate pagination links
    function getPaginationLink($page, $sortBy, $sortOrder, $commentsPerPage) {
        return "index.php?action=showComments&page={$page}&sort={$sortBy}&order={$sortOrder}&per_page={$commentsPerPage}";
    }
?>

<h2>Gestion des Commentaires</h2>

<div class="comments-management">
    <div class="sort-info">
        <p>Cliquez sur les en-têtes de colonnes pour trier le tableau. Un second clic inverse l'ordre de tri.</p>
    </div>
    
    <div class="pagination-container">
        <div class="pagination-info">
            <p>
                Affichage de <?= count($commentsData) ?> commentaires sur <?= $pagination['totalComments'] ?> 
                (Page <?= $pagination['currentPage'] ?> sur <?= $pagination['totalPages'] ?>)
            </p>
            
            <div class="comments-per-page">
                <form action="index.php" method="get" class="per-page-form">
                    <input type="hidden" name="action" value="showComments">
                    <input type="hidden" name="sort" value="<?= $sortBy ?>">
                    <input type="hidden" name="order" value="<?= $sortOrder ?>">
                    <input type="hidden" name="page" value="1">
                    <label for="per_page">Commentaires par page :</label>
                    <select name="per_page" id="per_page" onchange="this.form.submit()">
                        <?php foreach ([5, 10, 20, 30, 50] as $option): ?>
                            <option value="<?= $option ?>" <?= $pagination['commentsPerPage'] == $option ? 'selected' : '' ?>>
                                <?= $option ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>
    </div>
    
    <?php if (empty($commentsData)): ?>
        <div class="alert alert-info">
            Aucun commentaire à afficher.
        </div>
    <?php else: ?>
        <table class="comments-table">
            <thead>
                <tr>
                    <th><a href="<?= getSortLink('pseudo', $sortBy, $sortOrder, $pagination['currentPage'], $pagination['commentsPerPage']) ?>" class="sort-link">Auteur<?= $sortBy === 'pseudo' ? ($sortOrder === 'asc' ? ' ▲' : ' ▼') : '' ?></a></th>
                    <th>Contenu</th>
                    <th><a href="<?= getSortLink('date_creation', $sortBy, $sortOrder, $pagination['currentPage'], $pagination['commentsPerPage']) ?>" class="sort-link">Date<?= $sortBy === 'date_creation' ? ($sortOrder === 'asc' ? ' ▲' : ' ▼') : '' ?></a></th>
                    <th><a href="<?= getSortLink('article_title', $sortBy, $sortOrder, $pagination['currentPage'], $pagination['commentsPerPage']) ?>" class="sort-link">Article<?= $sortBy === 'article_title' ? ($sortOrder === 'asc' ? ' ▲' : ' ▼') : '' ?></a></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commentsData as $data): ?>
                    <?php $comment = $data['comment']; ?>
                    <tr>
                        <td><?= htmlspecialchars($comment->getPseudo()) ?></td>
                        <td class="comment-content">
                            <?php 
                                $content = $comment->getContent();
                                echo htmlspecialchars(strlen($content) > 100 ? substr($content, 0, 100) . '...' : $content);
                            ?>
                        </td>
                        <td><?= Utils::convertDateToFrenchFormat($comment->getDateCreation()) ?></td>
                        <td>
                            <a href="index.php?action=showArticle&id=<?= $comment->getIdArticle() ?>" class="article-link">
                                <?= htmlspecialchars($data['article_title']) ?>
                            </a>
                        </td>
                        <td class="actions">
                            <a href="index.php?action=deleteComment&id=<?= $comment->getId() ?>&csrf_token=<?= Utils::generateCsrfToken('delete_comment') ?>" 
                               class="delete-link"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?');">
                                Supprimer
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if ($pagination['totalPages'] > 1): ?>
            <div class="pagination-container">
                <div class="pagination">
                    <ul>
                        <?php if ($pagination['currentPage'] > 1): ?>
                            <li>
                                <a href="<?= getPaginationLink(1, $sortBy, $sortOrder, $pagination['commentsPerPage']) ?>" class="pagination-link">
                                    &laquo; Première
                                </a>
                            </li>
                            <li>
                                <a href="<?= getPaginationLink($pagination['currentPage'] - 1, $sortBy, $sortOrder, $pagination['commentsPerPage']) ?>" class="pagination-link">
                                    &lsaquo; Précédente
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                            // Display a limited number of pages around the current page
                            $startPage = max(1, $pagination['currentPage'] - 2);
                            $endPage = min($pagination['totalPages'], $pagination['currentPage'] + 2);
                            
                            // Ensure we display at least 5 pages if possible
                            if ($endPage - $startPage < 4) {
                                $endPage = min($pagination['totalPages'], $startPage + 4);
                                if ($endPage - $startPage < 4) {
                                    $startPage = max(1, $endPage - 4);
                                }
                            }
                        ?>
                        
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li>
                                <a href="<?= getPaginationLink($i, $sortBy, $sortOrder, $pagination['commentsPerPage']) ?>" 
                                   class="pagination-link <?= $i === $pagination['currentPage'] ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['currentPage'] < $pagination['totalPages']): ?>
                            <li>
                                <a href="<?= getPaginationLink($pagination['currentPage'] + 1, $sortBy, $sortOrder, $pagination['commentsPerPage']) ?>" class="pagination-link">
                                    Suivante &rsaquo;
                                </a>
                            </li>
                            <li>
                                <a href="<?= getPaginationLink($pagination['totalPages'], $sortBy, $sortOrder, $pagination['commentsPerPage']) ?>" class="pagination-link">
                                    Dernière &raquo;
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="admin-links">
    <a href="index.php?action=admin" class="submit">Retour à l'administration</a> 
</div> 