<?php 
/**
 * This file is the main template that "contains" what has been generated by other views.
 * 
 * The variables that must be defined are:
 *      $title string: the page title.
 *      $content string: the page content.
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emilie Forteroche</title>
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <header>
        <nav>
            <a href="index.php">Articles</a>
            <a href="index.php?action=apropos">À propos</a>
            <?php 
                // If user is connected, display logout button, otherwise display login button
                if (isset($_SESSION['userData'])) {
                    echo '<a href="index.php?action=disconnectUser">Déconnexion</a>';
                }
                ?>
        </nav>
        <h1>Emilie Forteroche</h1>
    </header>

    <main>    
        <?= $content /* Here is displayed the actual content of the page. */ ?>
    </main>
    
    <footer>
        <p>Copyright © Emilie Forteroche <?php echo date('Y'); ?> - Openclassrooms - <a href="index.php?action=admin">Administration</a>
    </footer>

</body>
</html>