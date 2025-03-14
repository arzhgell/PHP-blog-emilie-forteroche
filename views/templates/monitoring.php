<?php 
    /* 
     * Display of the monitoring page: blog statistics.
     */

    // Function to generate sort links
    function getSortLink($field, $currentSortBy, $currentSortOrder) {
        $newOrder = ($currentSortBy === $field && $currentSortOrder === 'asc') ? 'desc' : 'asc';
        $icon = '';
        
        if ($currentSortBy === $field) {
            $icon = $currentSortOrder === 'asc' ? ' ▲' : ' ▼';
        }
        
        $csrfToken = Utils::generateCsrfToken('monitoring_sort');
        return "index.php?action=showMonitoring&sort={$field}&order={$newOrder}&csrf_token={$csrfToken}" . $icon;
    }
?>

<h2>Statistiques du Blog</h2>

<div class="monitoring-container">
    <div class="monitoring-section">
        <h3>Aperçu général</h3>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">Articles</div>
                <div class="stat-value"><?= $totalArticles ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Vues</div>
                <div class="stat-value"><?= $totalViews ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Commentaires</div>
                <div class="stat-value"><?= $totalComments ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Moyenne de vues par article</div>
                <div class="stat-value"><?= $totalArticles > 0 ? round($totalViews / $totalArticles, 1) : 0 ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Moyenne de commentaires par article</div>
                <div class="stat-value"><?= $totalArticles > 0 ? round($totalComments / $totalArticles, 1) : 0 ?></div>
            </div>
        </div>
    </div>
    
    <div class="monitoring-section">
        <h3>Articles Populaires</h3>
        
        <?php if ($mostViewedArticle): ?>
            <div class="popular-article">
                <h4>Article le plus vu</h4>
                <div class="article-card">
                    <div class="article-title"><?= htmlspecialchars($mostViewedArticle->getTitle()) ?></div>
                    <div class="article-stats">
                        <span><?= $mostViewedArticle->getViewsCount() ?> vues</span>
                        <span><?= $commentsPerArticle[$mostViewedArticle->getId()] ?? 0 ?> commentaires</span>
                    </div>
                    <div class="article-date">Publié le <?= Utils::convertDateToFrenchFormat($mostViewedArticle->getDateCreation()) ?></div>
                    <a href="index.php?action=showArticle&id=<?= $mostViewedArticle->getId() ?>" class="view-link">Voir l'article</a>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($mostCommentedArticle): ?>
            <div class="popular-article">
                <h4>Article le plus commenté</h4>
                <div class="article-card">
                    <div class="article-title"><?= htmlspecialchars($mostCommentedArticle->getTitle()) ?></div>
                    <div class="article-stats">
                        <span><?= $mostCommentedArticle->getViewsCount() ?> vues</span>
                        <span><?= $commentsPerArticle[$mostCommentedArticle->getId()] ?? 0 ?> commentaires</span>
                    </div>
                    <div class="article-date">Publié le <?= Utils::convertDateToFrenchFormat($mostCommentedArticle->getDateCreation()) ?></div>
                    <a href="index.php?action=showArticle&id=<?= $mostCommentedArticle->getId() ?>" class="view-link">Voir l'article</a>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($newestArticle): ?>
            <div class="popular-article">
                <h4>Article le plus récent</h4>
                <div class="article-card">
                    <div class="article-title"><?= htmlspecialchars($newestArticle->getTitle()) ?></div>
                    <div class="article-stats">
                        <span><?= $newestArticle->getViewsCount() ?> vues</span>
                        <span><?= $commentsPerArticle[$newestArticle->getId()] ?? 0 ?> commentaires</span>
                    </div>
                    <div class="article-date">Publié le <?= Utils::convertDateToFrenchFormat($newestArticle->getDateCreation()) ?></div>
                    <a href="index.php?action=showArticle&id=<?= $newestArticle->getId() ?>" class="view-link">Voir l'article</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="monitoring-section">
        <h3>Tous les Articles</h3>
        <div class="sort-info">
            <p>Cliquez sur les en-têtes de colonnes pour trier le tableau. Un second clic inverse l'ordre de tri.</p>
        </div>
        
        <table class="articles-table">
            <thead>
                <tr>
                    <th><a href="<?= getSortLink('title', $sortBy, $sortOrder) ?>" class="sort-link">Titre<?= $sortBy === 'title' ? ($sortOrder === 'asc' ? ' ▲' : ' ▼') : '' ?></a></th>
                    <th><a href="<?= getSortLink('date_creation', $sortBy, $sortOrder) ?>" class="sort-link">Date de création<?= $sortBy === 'date_creation' ? ($sortOrder === 'asc' ? ' ▲' : ' ▼') : '' ?></a></th>
                    <th><a href="<?= getSortLink('date_update', $sortBy, $sortOrder) ?>" class="sort-link">Dernière mise à jour<?= $sortBy === 'date_update' ? ($sortOrder === 'asc' ? ' ▲' : ' ▼') : '' ?></a></th>
                    <th><a href="<?= getSortLink('views_count', $sortBy, $sortOrder) ?>" class="sort-link">Vues<?= $sortBy === 'views_count' ? ($sortOrder === 'asc' ? ' ▲' : ' ▼') : '' ?></a></th>
                    <th><a href="<?= getSortLink('comments', $sortBy, $sortOrder) ?>" class="sort-link">Commentaires<?= $sortBy === 'comments' ? ($sortOrder === 'asc' ? ' ▲' : ' ▼') : '' ?></a></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $article): ?>
                    <tr>
                        <td><?= htmlspecialchars($article->getTitle()) ?></td>
                        <td><?= Utils::convertDateToFrenchFormat($article->getDateCreation()) ?></td>
                        <td><?= $article->getDateUpdate() ? Utils::convertDateToFrenchFormat($article->getDateUpdate()) : 'N/A' ?></td>
                        <td><?= $article->getViewsCount() ?></td>
                        <td><?= $commentsPerArticle[$article->getId()] ?? 0 ?></td>
                        <td>
                            <a href="index.php?action=showArticle&id=<?= $article->getId() ?>" class="action-link">Voir</a>
                            <a href="index.php?action=showUpdateArticleForm&id=<?= $article->getId() ?>" class="action-link">Modifier</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="admin-links">
    <a href="index.php?action=admin" class="submit">Retour à l'administration</a> 