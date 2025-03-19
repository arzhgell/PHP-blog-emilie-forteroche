<?php

class CommentController 
{
    public function addComment() : void
    {
        $pseudo = Utils::requestString("pseudo");
        $content = Utils::requestString("content");
        $idArticle = Utils::requestInt("idArticle", 0);

        if (empty($pseudo) || empty($content) || $idArticle <= 0) {
            throw new Exception("Tous les champs sont obligatoires.");
        }
        
        if (strlen($content) < 5) {
            throw new Exception("Le commentaire est trop court. Veuillez écrire au moins 5 caractères.");
        }

        $articleManager = new ArticleManager();
        $article = $articleManager->getArticleById($idArticle);
        if (!$article) {
            throw new Exception("L'article demandé n'existe pas.");
        }

        $comment = new Comment([
            'pseudo' => $pseudo,
            'content' => $content,
            'idArticle' => $idArticle
        ]);

        $commentManager = new CommentManager();
        $result = $commentManager->addComment($comment);

        if (!$result) {
            throw new Exception("Une erreur est survenue lors de l'ajout du commentaire.");
        }

        Utils::redirect("showArticle", ['id' => $idArticle]);
    }
}