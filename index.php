<?php

// Inclusion of a single configuration file
require_once 'config/config.php';
require_once 'config/autoload.php';

// Automatic verification and update of the database schema
require_once 'config/db_init.php';

// Get the action requested by the user.
// If no action is requested, display the home page.
$action = Utils::request('action', 'home');

// Define routes as an associative array [action => [controller, method]]
$routes = [
    // Pages accessibles à tous
    'home' => ['ArticleController', 'showHome'],
    'apropos' => ['ArticleController', 'showApropos'],
    'showArticle' => ['ArticleController', 'showArticle'],
    'addArticle' => ['ArticleController', 'addArticle'],
    'addComment' => ['CommentController', 'addComment'],
    
    // Section admin et connexion
    'admin' => ['AdminController', 'showAdmin'],
    'connectionForm' => ['AdminController', 'displayConnectionForm'],
    'connectUser' => ['AdminController', 'connectUser'],
    'disconnectUser' => ['AdminController', 'disconnectUser'],
    'showUpdateArticleForm' => ['AdminController', 'showUpdateArticleForm'],
    'updateArticle' => ['AdminController', 'updateArticle'],
    'deleteArticle' => ['AdminController', 'deleteArticle'],
    'showMonitoring' => ['AdminController', 'showMonitoring'],
    'showComments' => ['AdminController', 'showComments'],
    'deleteComment' => ['AdminController', 'deleteComment'],
];

// Global try catch to handle errors
try {
    // Check if the requested action exists in our routes
    if (isset($routes[$action])) {
        // Get controller and method from routes
        list($controllerName, $methodName) = $routes[$action];
        
        // Instantiate controller and call method
        $controller = new $controllerName();
        $controller->$methodName();
    } else {
        throw new Exception("La page demandée n'existe pas.");
    }
} catch (Exception $e) {
    // In case of error, display the error page.
    $errorView = new View('Erreur');
    $errorView->render('errorPage', ['errorMessage' => $e->getMessage()]);
}
