<?php

/**
 * Class for managing comments
 */
class CommentManager extends AbstractEntityManager
{
    /**
     * Get all comments for an article
     * @param int $idArticle : article ID
     * @return array : array of Comment objects
     */
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

    /**
     * Get all comments with associated article information
     * @param int $page : current page number (starts at 1)
     * @param int $commentsPerPage : number of comments per page
     * @param string $sortBy : field to sort by (pseudo, date_creation, article_title)
     * @param string $sortOrder : sort order (asc, desc)
     * @return array : associative array containing comments, article titles and pagination info
     */
    public function getAllCommentsWithArticleInfo(int $page = 1, int $commentsPerPage = 10, string $sortBy = 'date_creation', string $sortOrder = 'desc') : array
    {
        // Validate sort parameters
        $allowedSortFields = ['pseudo', 'date_creation', 'article_title'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'date_creation'; // Default value if sort field is not valid
        }

        $allowedSortOrders = ['asc', 'desc'];
        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'desc'; // Default value if sort order is not valid
        }
        
        // Map sort field to actual column in query
        $sortColumn = $sortBy;
        if ($sortBy === 'article_title') {
            $sortColumn = 'a.title';
        } elseif ($sortBy === 'pseudo') {
            $sortColumn = 'c.pseudo';
        } elseif ($sortBy === 'date_creation') {
            $sortColumn = 'c.date_creation';
        }
        
        // Calculate offset for SQL query
        $offset = ($page - 1) * $commentsPerPage;
        
        // Query to count total comments
        $countSql = "SELECT COUNT(*) as total FROM comment";
        $countResult = $this->db->query($countSql);
        $totalComments = $countResult->fetch()['total'];
        
        // Calculate total pages
        $totalPages = ceil($totalComments / $commentsPerPage);
        
        // Query to get comments with pagination and sorting
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
        
        // Return comments and pagination info
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

    /**
     * Get a comment by its ID
     * @param int $id : comment ID
     * @return Comment|null : Comment object or null if comment doesn't exist
     */
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

    /**
     * Add a comment
     * @param Comment $comment : Comment object to add
     * @return bool : true if successful, false otherwise
     */
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

    /**
     * Delete a comment
     * @param int $id : ID of the comment to delete
     * @return bool : true if successful, false otherwise
     */
    public function deleteComment(int $id) : bool
    {
        $sql = "DELETE FROM comment WHERE id = :id";
        $result = $this->db->query($sql, ['id' => $id]);
        return $result->rowCount() > 0;
    }
}
