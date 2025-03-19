<form action="index.php" method="post" class="foldedCorner">
    <h2><?= $article->getId() == -1 ? "Créer un article" : "Modifier l'article "?></h2>
    <div class="formGrid">
        <label for="title">Titre</label>
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($article->getTitle()) ?>" required autofocus>
        
        <label for="content">Contenu</label>
        <textarea name="content" id="content" cols="30" rows="10" required><?= htmlspecialchars($article->getContent()) ?></textarea>
        
        <input type="hidden" name="action" value="updateArticle">
        <input type="hidden" name="id" value="<?= $article->getId() ?>">
        
        <button type="submit" class="submit"><?= $article->getId() == -1 ? "Ajouter" : "Mettre à jour" ?></button>
    </div>
</form>