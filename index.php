<?php

require_once 'config/config.php';
require_once 'config/autoload.php';

require_once 'config/db_init.php';
$action = Utils::request('action', 'home');

$routes = [
    'home' => ['ArticleController', 'showHome'],
    'apropos' => ['ArticleController', 'showApropos'],
    'showArticle' => ['ArticleController', 'showArticle'],
    'addArticle' => ['ArticleController', 'addArticle'],
    'addComment' => ['CommentController', 'addComment'],
    
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

try {
    if (isset($routes[$action])) {
        list($controllerName, $methodName) = $routes[$action];
        
        $controller = new $controllerName();
        $controller->$methodName();
    } else {
        throw new Exception("La page demandée n'existe pas.");
    }
} catch (Exception $e) {
    $errorView = new View('Erreur');
    $errorView->render('errorPage', ['errorMessage' => $e->getMessage()]);
}
