<?php 
class AdminController {
    public function showAdmin() : void
    {
        $this->checkIfUserIsConnected();

        $articleManager = new ArticleManager();
        $articles = $articleManager->getAllArticles();

        $view = new View("Administration");
        $view->render("admin", [
            'articles' => $articles
        ]);
    }

    private function checkIfUserIsConnected() : void
    {
        if (!isset($_SESSION['userData'])) {
            Utils::redirect("connectionForm");
        }
    }

    public function displayConnectionForm() : void 
    {
        $view = new View("Connexion");
        $view->render("connectionForm");
    }

    public function connectUser() : void 
    {            
        $login = Utils::requestString("login");
        $password = Utils::requestString("password");

        if (empty($login) || empty($password)) {
            throw new Exception("Tous les champs sont obligatoires.");
        }

        $userManager = new UserManager();
        $user = $userManager->getUserByLogin($login);
        if (!$user) {
            throw new Exception("Identifiant ou mot de passe incorrect.");
        }

        if (!password_verify($password, $user->getPassword())) {
            throw new Exception("Identifiant ou mot de passe incorrect.");
        }

        $_SESSION['userData'] = [
            'id' => $user->getId(),
            'login' => $user->getLogin()
        ];
        $_SESSION['idUser'] = $user->getId();
        
        session_regenerate_id(true);

        Utils::redirect("admin");
    }

    public function disconnectUser() : void 
    {
        $sessionData = [];
        
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        
        session_start();
        
        session_regenerate_id(true);

        $_SESSION = $sessionData;

        Utils::redirect("home");
    }

    public function showUpdateArticleForm() : void 
    {
        $this->checkIfUserIsConnected();

        $id = Utils::requestInt("id", -1);

        $articleManager = new ArticleManager();
        $article = $articleManager->getArticleById($id);

        if (!$article) {
            $article = new Article();
        }

        $view = new View("Edition d'un article");
        $view->render("updateArticleForm", [
            'article' => $article
        ]);
    }

    public function updateArticle() : void 
    {
        $this->checkIfUserIsConnected();

        $id = Utils::requestInt("id", -1);
        $title = Utils::requestString("title");
        $content = Utils::requestString("content");

        if (empty($title) || empty($content)) {
            throw new Exception("Tous les champs sont obligatoires.");
        }

        $article = new Article([
            'id' => $id,
            'title' => $title,
            'content' => $content,
            'id_user' => $_SESSION['idUser']
        ]);

        $articleManager = new ArticleManager();
        $articleManager->addOrUpdateArticle($article);

        Utils::redirect("admin");
    }

    public function deleteArticle() : void 
    {
        $this->checkIfUserIsConnected();

        $id = Utils::requestInt("id", -1);

        if ($id <= 0) {
            throw new Exception("L'article demandé n'existe pas.");
        }

        $articleManager = new ArticleManager();
        $article = $articleManager->getArticleById($id);
        
        if ($article) {
            $articleManager->deleteArticle($id);
        } else {
            $articleManager->deleteArticle($id);
        }

        Utils::redirect("admin");
    }

    public function showMonitoring() : void
    {
        $this->checkIfUserIsConnected();

        $sortBy = Utils::requestString('sort', 'date_creation');
        $sortOrder = Utils::requestString('order', 'desc');

        $allowedSortFields = ['title', 'date_creation', 'date_update', 'views_count', 'comments'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'date_creation';
        }

        $allowedSortOrders = ['asc', 'desc'];
        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'desc';
        }

        $articleManager = new ArticleManager();
        
        $sqlSortBy = $sortBy;
        $needsManualSort = false;
        
        if ($sortBy === 'comments') {
            $sqlSortBy = 'date_creation'; 
            $needsManualSort = true;
        }
        
        $articles = $articleManager->getAllArticles($sqlSortBy, $sortOrder);
        
        $totalArticles = count($articles);
        
        $mostViewedArticle = null;
        $maxViews = -1;
        
        $newestArticle = null;
        $newestDate = null;
        
        $totalViews = 0;
        
        foreach ($articles as $article) {
            $totalViews += $article->getViewsCount();
            
            if ($article->getViewsCount() > $maxViews) {
                $maxViews = $article->getViewsCount();
                $mostViewedArticle = $article;
            }
            
            $articleDate = $article->getDateCreation();
            if ($newestDate === null || $articleDate > $newestDate) {
                $newestDate = $articleDate;
                $newestArticle = $article;
            }
        }
        
        $commentManager = new CommentManager();
        $totalComments = 0;
        $commentsPerArticle = [];
        
        foreach ($articles as $article) {
            $articleId = $article->getId();
            $comments = $commentManager->getAllCommentsByArticleId($articleId);
            $commentsCount = count($comments);
            $totalComments += $commentsCount;
            $commentsPerArticle[$articleId] = $commentsCount;
        }
        
        $mostCommentedArticleId = 0;
        $maxComments = -1;
        
        foreach ($commentsPerArticle as $articleId => $commentCount) {
            if ($commentCount > $maxComments) {
                $maxComments = $commentCount;
                $mostCommentedArticleId = $articleId;
            }
        }
        
        $mostCommentedArticle = $articleManager->getArticleById($mostCommentedArticleId);
        
        if ($needsManualSort) {
            usort($articles, function($a, $b) use ($commentsPerArticle, $sortOrder) {
                $commentsA = $commentsPerArticle[$a->getId()] ?? 0;
                $commentsB = $commentsPerArticle[$b->getId()] ?? 0;
                $result = $commentsA <=> $commentsB;
                
                return $sortOrder === 'desc' ? -$result : $result;
            });
        }
        
        $view = new View("Monitoring");
        $view->render("monitoring", [
            'totalArticles' => $totalArticles,
            'totalViews' => $totalViews,
            'totalComments' => $totalComments,
            'mostViewedArticle' => $mostViewedArticle,
            'mostCommentedArticle' => $mostCommentedArticle,
            'newestArticle' => $newestArticle,
            'articles' => $articles,
            'commentsPerArticle' => $commentsPerArticle,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ]);
    }

    public function showComments() : void
    {
        $this->checkIfUserIsConnected();

        $page = Utils::requestInt('page', 1);
        $commentsPerPage = Utils::requestInt('per_page', 10);
        
        if ($page < 1) {
            $page = 1;
        }
        
        if ($commentsPerPage < 5 || $commentsPerPage > 50) {
            $commentsPerPage = 10; 
        }

        $sortBy = Utils::requestString('sort', 'date_creation');
        $sortOrder = Utils::requestString('order', 'desc');

        $allowedSortFields = ['pseudo', 'date_creation', 'article_title'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'date_creation';
        }

        $allowedSortOrders = ['asc', 'desc'];
        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'desc';
        }

        $commentManager = new CommentManager();
        $result = $commentManager->getAllCommentsWithArticleInfo($page, $commentsPerPage, $sortBy, $sortOrder);
        $commentsData = $result['comments'];
        $pagination = $result['pagination'];

        $view = new View("Gestion des commentaires");
        $view->render("adminComments", [
            'commentsData' => $commentsData,
            'pagination' => $pagination,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ]);
    }

    public function deleteComment() : void
    {
        $this->checkIfUserIsConnected();

        $id = Utils::requestInt("id", -1);

        if ($id <= 0) {
            throw new Exception("ID de commentaire invalide.");
        }

        $commentManager = new CommentManager();
        $comment = $commentManager->getCommentById($id);
        
        if ($comment) {
            $success = $commentManager->deleteComment($id);
            
            if (!$success) {
                throw new Exception("Erreur lors de la suppression du commentaire.");
            }
        } else {
            throw new Exception("Le commentaire demandé n'existe pas.");
        }

        Utils::redirect("showComments");
    }
}