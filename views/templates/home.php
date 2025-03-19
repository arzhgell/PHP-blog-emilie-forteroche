<div class="articleList">
    <?php foreach ($articles as $article) { ?>
        <article>
            <h2><?= $article->getTitle() ?></h2>
            <div class="quotation">"</div>
            <p><?= $article->getContent(200) ?></p>
            <div class="footer">
                <span class="info"><?= Utils::convertDateToFrenchFormat($article->getDateCreation()) ?></span>
                <a href="index.php?action=showArticle&id=<?= $article->getId() ?>" class="info">Lire la suite</a>
            </div>
        </article>
    <?php } ?>
</div>