<?php
    
// En fonction des routes utilisées, il est possible d'avoir besoin de la session ; on la démarre dans tous les cas. 
session_start();

// Ici on met les constantes utiles, 
// les données de connexions à la bdd
// et tout ce qui sert à configurer. 

define('TEMPLATE_VIEW_PATH', './views/templates/'); // Le chemin vers les templates de vues.
define('MAIN_VIEW_PATH', TEMPLATE_VIEW_PATH . 'main.php'); // Le chemin vers le template principal.

// Utilisation des variables d'environnement Docker
define('DB_HOST', getenv('DB_HOST') ?: 'mysql');
define('DB_NAME', getenv('DB_NAME') ?: 'blog_forteroche');
define('DB_USER', getenv('DB_USER') ?: 'forteroche');
define('DB_PASS', getenv('DB_PASS') ?: 'forteroche_password'); 