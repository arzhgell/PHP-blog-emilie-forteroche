<?php
    /* 
     * Template to display the login form.
     */
?>

<div class="connection-form">
    <h2>Connexion</h2>
    <form action="index.php?action=connectUser" method="post" class="foldedCorner">
        <div class="formGrid">
            <!-- Ajout d'un jeton CSRF pour la protection contre les attaques CSRF -->
            <input type="hidden" name="csrf_token" value="<?= Utils::generateCsrfToken('login_form') ?>">
            
            <label for="login">Identifiant</label>
            <input type="text" name="login" id="login" required autocomplete="username" autofocus>
            
            <label for="password">Mot de passe</label>
            <input type="password" name="password" id="password" required autocomplete="current-password">
            
            <button type="submit" class="submit">Se connecter</button>
        </div>
    </form>
</div>