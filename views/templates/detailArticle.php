<?php
    /**
     * This template displays an article and its comments.
     * It also displays a form to add a comment.
     */
?>

<article class="mainArticle">
    <h2><?= htmlspecialchars($article->getTitle()) ?></h2>
    <div class="quotation">"</div>
    <p><?= nl2br(htmlspecialchars($article->getContent())) ?></p>

    <div class="footer">
        <span class="info"><?= Utils::convertDateToFrenchFormat($article->getDateCreation()) ?></span>
        <?php if ($article->getDateUpdate() != null) { ?>
            <span class="info"> Modifié le <?= Utils::convertDateToFrenchFormat($article->getDateUpdate()) ?></span>
        <?php } ?>
        <span class="info"><?= $article->getViewsCount() ?> vues</span>
    </div>
</article>

<div class="comments">
    <h2 class="commentsTitle">Commentaires</h2>
    <?php 
        if (empty($comments)) {
            echo '<p class="info">Aucun commentaire pour cet article.</p>';
        } else {
            echo '<ul>';
            foreach ($comments as $comment) {
                echo '<li>';
                echo '  <div class="smiley">:)</div>';
                echo '  <div class="detailComment">';
                echo '      <div class="info">' . htmlspecialchars($comment->getPseudo()) . '</div>';
                echo '      <div class="info">' . Utils::convertDateToFrenchFormat($comment->getDateCreation()) . '</div>';
                echo '      <div class="content">' . htmlspecialchars($comment->getContent()) . '</div>';
                echo '  </div>';
                echo '</li>';
            }               
            echo '</ul>';
        } 
    ?>

    <form action="index.php?action=addComment" method="post" class="foldedCorner">
        <h2>Ajouter un commentaire</h2>

        <div class="formComment formGrid">
            <input type="hidden" name="csrf_token" value="<?= Utils::generateCsrfToken('comment_form') ?>">
            
            <label for="pseudo">Nom</label>
            <input type="text" name="pseudo" id="pseudo" required maxlength="50">

            <label for="content">Commentaire</label>
            <textarea name="content" id="content" required minlength="5" maxlength="1000"></textarea>

            <input type="hidden" name="idArticle" value="<?= $article->getId() ?>">

            <button type="submit" class="submit">Ajouter</button>
        </div>
    </form>
</div>