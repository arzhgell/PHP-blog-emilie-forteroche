<?php 
/**
 * Admin controller
 */
 
class AdminController {

    /**
     * Displays admin page
     */
    public function showAdmin() : void
    {
        // Check if user is connected
        $this->checkIfUserIsConnected();

        // Nettoyer les jetons CSRF expirés
        Utils::cleanExpiredCsrfTokens();

        // Get all articles
        $articleManager = new ArticleManager();
        $articles = $articleManager->getAllArticles();

        // Display admin page
        $view = new View("Administration");
        $view->render("admin", [
            'articles' => $articles
        ]);
    }

    /**
     * Checks if user is connected
     */
    private function checkIfUserIsConnected() : void
    {
        // Check if user is connected
        if (!isset($_SESSION['userData'])) {
            Utils::redirect("connectionForm");
        }
    }

    /**
     * Displays login form
     */
    public function displayConnectionForm() : void 
    {
        $view = new View("Connexion");
        $view->render("connectionForm");
    }

    /**
     * Handles user login
     */
    public function connectUser() : void 
    {
        // Récupération de l'adresse IP du client
        $ip = Utils::getClientIp();
        
        // Vérification des tentatives de connexion
        if (!Utils::checkLoginAttempts($ip)) {
            throw new Exception("Trop de tentatives de connexion. Veuillez réessayer plus tard.");
        }
        
        // Vérification du jeton CSRF
        $csrfToken = Utils::request("csrf_token");
        if (!$csrfToken || !Utils::validateCsrfToken($csrfToken, 'login_form')) {
            // Enregistrement de la tentative échouée
            Utils::recordFailedLoginAttempt($ip);
            throw new Exception("Session expirée ou requête invalide. Veuillez réessayer.");
        }
        
        // Get form data
        $login = Utils::requestString("login");
        $password = Utils::requestString("password");

        // Check if data is valid
        if (empty($login) || empty($password)) {
            // Enregistrement de la tentative échouée
            Utils::recordFailedLoginAttempt($ip);
            throw new Exception("Tous les champs sont obligatoires.");
        }

        // Check if user exists
        $userManager = new UserManager();
        $user = $userManager->getUserByLogin($login);
        if (!$user) {
            // Enregistrement de la tentative échouée
            Utils::recordFailedLoginAttempt($ip);
            // Utilisation d'un message générique pour éviter de révéler l'existence ou non d'un utilisateur
            throw new Exception("Identifiant ou mot de passe incorrect.");
        }

        // Check if password is correct
        if (!password_verify($password, $user->getPassword())) {
            // Enregistrement de la tentative échouée
            Utils::recordFailedLoginAttempt($ip);
            // Utilisation d'un message générique pour éviter de révéler l'existence ou non d'un utilisateur
            throw new Exception("Identifiant ou mot de passe incorrect.");
        }

        // Réinitialisation des tentatives de connexion en cas de succès
        Utils::resetLoginAttempts($ip);
        
        // Connect user - store only necessary data instead of the whole object
        $_SESSION['userData'] = [
            'id' => $user->getId(),
            'login' => $user->getLogin()
        ];
        $_SESSION['idUser'] = $user->getId();
        
        // Régénérer l'ID de session pour éviter les attaques de fixation de session
        session_regenerate_id(true);

        // Redirect to admin page
        Utils::redirect("admin");
    }

    /**
     * Handles user logout
     */
    public function disconnectUser() : void 
    {
        // Sauvegarde des données de session que nous voulons conserver
        $sessionData = [];
        
        // Destruction complète de la session
        $_SESSION = [];
        
        // Suppression du cookie de session si présent
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruction de la session
        session_destroy();
        
        // Démarrage d'une nouvelle session
        session_start();
        
        // Régénération de l'ID de session
        session_regenerate_id(true);
        
        // Restauration des données de session à conserver
        $_SESSION = $sessionData;

        // Redirect to home page
        Utils::redirect("home");
    }

    /**
     * Displays article edit form
     */
    public function showUpdateArticleForm() : void 
    {
        $this->checkIfUserIsConnected();

        // Get article ID if it exists
        $id = Utils::requestInt("id", -1);

        // Get associated article
        $articleManager = new ArticleManager();
        $article = $articleManager->getArticleById($id);

        // If article doesn't exist, create an empty one
        if (!$article) {
            $article = new Article();
        }

        // Display article edit page
        $view = new View("Edition d'un article");
        $view->render("updateArticleForm", [
            'article' => $article
        ]);
    }

    /**
     * Handles article creation/update
     */
    public function updateArticle() : void 
    {
        $this->checkIfUserIsConnected();

        // Vérification du jeton CSRF
        $csrfToken = Utils::request("csrf_token");
        if (!$csrfToken || !Utils::validateCsrfToken($csrfToken, 'article_form')) {
            throw new Exception("Session expirée ou requête invalide. Veuillez réessayer.");
        }

        // Get form data
        $id = Utils::requestInt("id", -1);
        $title = Utils::requestString("title");
        $content = Utils::requestString("content");

        // Check if data is valid
        if (empty($title) || empty($content)) {
            throw new Exception("Tous les champs sont obligatoires.");
        }

        // Create Article object
        $article = new Article([
            'id' => $id, // If id is -1, article will be added. Otherwise, it will be updated.
            'title' => $title,
            'content' => $content,
            'id_user' => $_SESSION['idUser']
        ]);

        // Add or update article
        $articleManager = new ArticleManager();
        $articleManager->addOrUpdateArticle($article);

        // Redirect to admin page
        Utils::redirect("admin");
    }

    /**
     * Handles article deletion
     */
    public function deleteArticle() : void 
    {
        $this->checkIfUserIsConnected();

        // Vérification du jeton CSRF
        $csrfToken = Utils::request("csrf_token");
        if (!$csrfToken || !Utils::validateCsrfToken($csrfToken, 'delete_article')) {
            throw new Exception("Session expirée ou requête invalide. Veuillez réessayer.");
        }

        // Get article ID
        $id = Utils::requestInt("id", -1);

        // Check if ID is valid
        if ($id <= 0) {
            throw new Exception("L'article demandé n'existe pas.");
        }

        // Delete article
        $articleManager = new ArticleManager();
        $article = $articleManager->getArticleById($id);
        
        if ($article) {
            $articleManager->deleteArticle($id);
        } else {
            $articleManager->deleteArticle($id);
        }

        // Redirect to admin page
        Utils::redirect("admin");
    }

    /**
     * Displays monitoring page with statistics
     */
    public function showMonitoring() : void
    {
        // Check if user is connected
        $this->checkIfUserIsConnected();

        // Get sorting parameters
        $sortBy = Utils::requestString('sort', 'date_creation'); // Default sort by creation date
        $sortOrder = Utils::requestString('order', 'desc'); // Default order is descending

        // Validate sorting parameters
        $allowedSortFields = ['title', 'date_creation', 'date_update', 'views_count', 'comments'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'date_creation'; // Default value if sort field is not valid
        }

        $allowedSortOrders = ['asc', 'desc'];
        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'desc'; // Default value if sort order is not valid
        }

        // Get article statistics
        $articleManager = new ArticleManager();
        
        // Si le tri est par commentaires, nous devons le faire manuellement
        // Sinon, nous utilisons le tri SQL
        $sqlSortBy = $sortBy;
        $needsManualSort = false;
        
        if ($sortBy === 'comments') {
            $sqlSortBy = 'date_creation'; // Tri par défaut, sera remplacé par un tri manuel
            $needsManualSort = true;
        }
        
        $articles = $articleManager->getAllArticles($sqlSortBy, $sortOrder);
        
        // Total number of articles
        $totalArticles = count($articles);
        
        // Most viewed article
        $mostViewedArticle = null;
        $maxViews = -1;
        
        // Newest article
        $newestArticle = null;
        $newestDate = null;
        
        // Total views
        $totalViews = 0;
        
        foreach ($articles as $article) {
            // Update total views
            $totalViews += $article->getViewsCount();
            
            // Check for most viewed article
            if ($article->getViewsCount() > $maxViews) {
                $maxViews = $article->getViewsCount();
                $mostViewedArticle = $article;
            }
            
            // Check for newest article
            $articleDate = $article->getDateCreation();
            if ($newestDate === null || $articleDate > $newestDate) {
                $newestDate = $articleDate;
                $newestArticle = $article;
            }
        }
        
        // Get comment statistics
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
        
        // Article with most comments
        $mostCommentedArticleId = 0;
        $maxComments = -1;
        
        foreach ($commentsPerArticle as $articleId => $commentCount) {
            if ($commentCount > $maxComments) {
                $maxComments = $commentCount;
                $mostCommentedArticleId = $articleId;
            }
        }
        
        $mostCommentedArticle = $articleManager->getArticleById($mostCommentedArticleId);
        
        // Si nous avons besoin d'un tri manuel (par nombre de commentaires)
        if ($needsManualSort) {
            usort($articles, function($a, $b) use ($commentsPerArticle, $sortOrder) {
                $commentsA = $commentsPerArticle[$a->getId()] ?? 0;
                $commentsB = $commentsPerArticle[$b->getId()] ?? 0;
                $result = $commentsA <=> $commentsB;
                
                // Reverse result if order is descending
                return $sortOrder === 'desc' ? -$result : $result;
            });
        }
        
        // Display monitoring page
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

    /**
     * Checks and updates database schema
     */
    public function checkDatabaseSchema() : void
    {
        // Check if user is connected and is administrator
        $this->checkIfUserIsConnected();
        
        if (!isset($_SESSION['userData']) || $_SESSION['userData']['id'] != 1) {
            throw new Exception("Vous n'avez pas les droits pour accéder à cette page.");
        }
        
        try {
            // Create PDO connection
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            
            // Check and update schema
            require_once 'config/db_schema_check.php';
            $schemaChecker = new DatabaseSchemaChecker($pdo);
            $results = $schemaChecker->checkAndUpdateSchema();
            
            // Display results
            $view = new View("Vérification de la base de données");
            $view->render("databaseCheck", [
                'results' => $results
            ]);
            
        } catch (PDOException $e) {
            throw new Exception("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }

    /**
     * Displays comments management page
     */
    public function showComments() : void
    {
        // Check if user is connected
        $this->checkIfUserIsConnected();

        // Get pagination parameters
        $page = Utils::requestInt('page', 1);
        $commentsPerPage = Utils::requestInt('per_page', 10);
        
        // Validate pagination parameters
        if ($page < 1) {
            $page = 1;
        }
        
        if ($commentsPerPage < 5 || $commentsPerPage > 50) {
            $commentsPerPage = 10; // Default value if comments per page is not valid
        }

        // Get sorting parameters
        $sortBy = Utils::requestString('sort', 'date_creation'); // Default sort by creation date
        $sortOrder = Utils::requestString('order', 'desc'); // Default order is descending

        // Validate sorting parameters
        $allowedSortFields = ['pseudo', 'date_creation', 'article_title'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'date_creation'; // Default value if sort field is not valid
        }

        $allowedSortOrders = ['asc', 'desc'];
        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'desc'; // Default value if sort order is not valid
        }

        // Get all comments with article information
        $commentManager = new CommentManager();
        $result = $commentManager->getAllCommentsWithArticleInfo($page, $commentsPerPage, $sortBy, $sortOrder);
        $commentsData = $result['comments'];
        $pagination = $result['pagination'];

        // Display comments management page
        $view = new View("Gestion des commentaires");
        $view->render("adminComments", [
            'commentsData' => $commentsData,
            'pagination' => $pagination,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ]);
    }

    /**
     * Handles comment deletion
     */
    public function deleteComment() : void
    {
        $this->checkIfUserIsConnected();

        // Vérification du jeton CSRF
        $csrfToken = Utils::request("csrf_token");
        if (!$csrfToken || !Utils::validateCsrfToken($csrfToken, 'delete_comment')) {
            throw new Exception("Session expirée ou requête invalide. Veuillez réessayer.");
        }

        $id = Utils::requestInt("id", -1);

        if ($id <= 0) {
            throw new Exception("ID de commentaire invalide.");
        }

        // Delete comment
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

        // Redirect to comments management page
        Utils::redirect("showComments");
    }
}