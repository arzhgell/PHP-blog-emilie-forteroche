<?php

class ArticleManager extends AbstractEntityManager 
{
    public function getAllArticles(string $sortBy = 'date_creation', string $sortOrder = 'desc') : array
    {
        $allowedSortFields = ['title', 'date_creation', 'date_update', 'views_count'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'date_creation';
        }

        $allowedSortOrders = ['asc', 'desc'];
        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'desc';
        }

        $sql = "SELECT * FROM article ORDER BY {$sortBy} {$sortOrder}";
        $result = $this->db->query($sql);
        $articles = [];

        while ($article = $result->fetch()) {
            $articles[] = new Article($article);
        }
        return $articles;
    }
    
    public function getArticleById(int $id) : ?Article
    {
        $sql = "SELECT * FROM article WHERE id = :id";
        $result = $this->db->query($sql, ['id' => $id]);
        $article = $result->fetch();
        if ($article) {
            return new Article($article);
        }
        return null;
    }

    public function addOrUpdateArticle(Article $article) : void 
    {
        if ($article->getId() == -1) {
            $this->addArticle($article);
        } else {
            $this->updateArticle($article);
        }
    }

    public function addArticle(Article $article) : void
    {
        $sql = "INSERT INTO article (id_user, title, content, date_creation) VALUES (:id_user, :title, :content, NOW())";
        $this->db->query($sql, [
            'id_user' => $article->getIdUser(),
            'title' => $article->getTitle(),
            'content' => $article->getContent()
        ]);
    }

    public function updateArticle(Article $article) : void
    {
        $sql = "UPDATE article SET title = :title, content = :content, date_update = NOW() WHERE id = :id";
        $this->db->query($sql, [
            'title' => $article->getTitle(),
            'content' => $article->getContent(),
            'id' => $article->getId()
        ]);
    }

    public function deleteArticle(int $id) : void
    {
        $sql = "DELETE FROM article WHERE id = :id";
        $this->db->query($sql, ['id' => $id]);
    }

    public function incrementViewsCount(int $id) : void
    {
        $sql = "UPDATE article SET views_count = views_count + 1 WHERE id = :id";
        $this->db->query($sql, ['id' => $id]);
    }

    public function getViewsCount(int $id) : int
    {
        $sql = "SELECT views_count FROM article WHERE id = :id";
        $result = $this->db->query($sql, ['id' => $id]);
        $data = $result->fetch();
        return $data ? (int)$data['views_count'] : 0;
    }
}