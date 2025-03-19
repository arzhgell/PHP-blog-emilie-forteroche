<?php

class CommentManager extends AbstractEntityManager
{
    public function getAllCommentsByArticleId(int $idArticle) : array
    {
        $sql = "SELECT * FROM comment WHERE id_article = :idArticle";
        $result = $this->db->query($sql, ['idArticle' => $idArticle]);
        $comments = [];

        while ($comment = $result->fetch()) {
            $comments[] = new Comment($comment);
        }
        return $comments;
    }

    public function getAllCommentsWithArticleInfo(int $page = 1, int $commentsPerPage = 10, string $sortBy = 'date_creation', string $sortOrder = 'desc') : array
    {
        $allowedSortFields = ['pseudo', 'date_creation', 'article_title'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'date_creation';
        }

        $allowedSortOrders = ['asc', 'desc'];
        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'desc';
        }
        
        $sortColumn = $sortBy;
        if ($sortBy === 'article_title') {
            $sortColumn = 'a.title';
        } elseif ($sortBy === 'pseudo') {
            $sortColumn = 'c.pseudo';
        } elseif ($sortBy === 'date_creation') {
            $sortColumn = 'c.date_creation';
        }
        
        $offset = ($page - 1) * $commentsPerPage;
        
        $countSql = "SELECT COUNT(*) as total FROM comment";
        $countResult = $this->db->query($countSql);
        $totalComments = $countResult->fetch()['total'];
        
        $totalPages = ceil($totalComments / $commentsPerPage);
        
        $sql = "SELECT c.*, a.title as article_title 
                FROM comment c 
                JOIN article a ON c.id_article = a.id 
                ORDER BY {$sortColumn} {$sortOrder}
                LIMIT " . (int)$commentsPerPage . " OFFSET " . (int)$offset;
                
        $result = $this->db->query($sql);
        
        $commentsData = [];
        
        while ($row = $result->fetch()) {
            $comment = new Comment($row);
            $commentsData[] = [
                'comment' => $comment,
                'article_title' => $row['article_title']
            ];
        }
        
        return [
            'comments' => $commentsData,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'commentsPerPage' => $commentsPerPage,
                'totalComments' => $totalComments
            ]
        ];
    }

    public function getCommentById(int $id) : ?Comment
    {
        $sql = "SELECT * FROM comment WHERE id = :id";
        $result = $this->db->query($sql, ['id' => $id]);
        $comment = $result->fetch();
        if ($comment) {
            return new Comment($comment);
        }
        return null;
    }

    public function addComment(Comment $comment) : bool
    {
        $sql = "INSERT INTO comment (pseudo, content, id_article, date_creation) VALUES (:pseudo, :content, :idArticle, NOW())";
        $result = $this->db->query($sql, [
            'pseudo' => $comment->getPseudo(),
            'content' => $comment->getContent(),
            'idArticle' => $comment->getIdArticle()
        ]);
        return $result->rowCount() > 0;
    }
    
    public function deleteComment(int $id) : bool
    {
        $sql = "DELETE FROM comment WHERE id = :id";
        $result = $this->db->query($sql, ['id' => $id]);
        return $result->rowCount() > 0;
    }
}
