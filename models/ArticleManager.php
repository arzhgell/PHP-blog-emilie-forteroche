<?php

/**
 * Manages article operations
 */
class ArticleManager extends AbstractEntityManager 
{
    /**
     * Gets all articles
     * @param string $sortBy Field to sort by (title, date_creation, date_update, views_count)
     * @param string $sortOrder Sort order (asc, desc)
     * @return array Array of Article objects
     */
    public function getAllArticles(string $sortBy = 'date_creation', string $sortOrder = 'desc') : array
    {
        // Validate sort parameters
        $allowedSortFields = ['title', 'date_creation', 'date_update', 'views_count'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'date_creation'; // Default value if sort field is not valid
        }

        $allowedSortOrders = ['asc', 'desc'];
        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'desc'; // Default value if sort order is not valid
        }

        // Build SQL query with ORDER BY clause
        $sql = "SELECT * FROM article ORDER BY {$sortBy} {$sortOrder}";
        $result = $this->db->query($sql);
        $articles = [];

        while ($article = $result->fetch()) {
            $articles[] = new Article($article);
        }
        return $articles;
    }
    
    /**
     * Gets article by ID
     */
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

    /**
     * Adds or updates article based on ID
     */
    public function addOrUpdateArticle(Article $article) : void 
    {
        if ($article->getId() == -1) {
            $this->addArticle($article);
        } else {
            $this->updateArticle($article);
        }
    }

    /**
     * Adds a new article
     */
    public function addArticle(Article $article) : void
    {
        $sql = "INSERT INTO article (id_user, title, content, date_creation) VALUES (:id_user, :title, :content, NOW())";
        $this->db->query($sql, [
            'id_user' => $article->getIdUser(),
            'title' => $article->getTitle(),
            'content' => $article->getContent()
        ]);
    }

    /**
     * Updates existing article
     */
    public function updateArticle(Article $article) : void
    {
        $sql = "UPDATE article SET title = :title, content = :content, date_update = NOW() WHERE id = :id";
        $this->db->query($sql, [
            'title' => $article->getTitle(),
            'content' => $article->getContent(),
            'id' => $article->getId()
        ]);
    }

    /**
     * Deletes article by ID
     */
    public function deleteArticle(int $id) : void
    {
        $sql = "DELETE FROM article WHERE id = :id";
        $this->db->query($sql, ['id' => $id]);
    }

    /**
     * Increments article view count
     */
    public function incrementViewsCount(int $id) : void
    {
        $sql = "UPDATE article SET views_count = views_count + 1 WHERE id = :id";
        $this->db->query($sql, ['id' => $id]);
    }

    /**
     * Gets article view count
     */
    public function getViewsCount(int $id) : int
    {
        $sql = "SELECT views_count FROM article WHERE id = :id";
        $result = $this->db->query($sql, ['id' => $id]);
        $data = $result->fetch();
        return $data ? (int)$data['views_count'] : 0;
    }
}