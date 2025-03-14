<?php 
    /* 
     * Admin section display: list of articles with "edit" button for each.
     * And a form to add an article.
     */
?>

<h2>Gestion des Articles</h2>

<div class="adminArticle">
    <?php foreach ($articles as $article) { ?>
        <div class="articleLine">
            <div class="title"><?= htmlspecialchars($article->getTitle()) ?></div>
            <div class="content"><?= htmlspecialchars($article->getContent(200)) ?></div>
            <div class="info"><?= $article->getViewsCount() ?> vues</div>
            <div><a class="submit" href="index.php?action=showUpdateArticleForm&id=<?= $article->getId() ?>">Modifier</a></div>
            <div>
                <a class="submit" 
                   href="index.php?action=deleteArticle&id=<?= $article->getId() ?>&csrf_token=<?= Utils::generateCsrfToken('delete_article') ?>" 
                   <?= Utils::askConfirmation("Êtes-vous sûr de vouloir supprimer cet article ?") ?>>
                    Supprimer
                </a>
            </div>
        </div>
    <?php } ?>
</div>

<div class="admin-actions">
    <a href="index.php?action=showUpdateArticleForm" class="submit">Créer un nouvel article</a>
    <a href="index.php?action=showMonitoring" class="submit">Statistiques du blog</a>
    <a href="index.php?action=showComments" class="submit">Gérer les commentaires</a>
    <a href="index.php?action=disconnectUser" class="submit">Se déconnecter</a>
</div>