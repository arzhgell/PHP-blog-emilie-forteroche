<div class="container mt-5">
    <h1 class="mb-4">Vérification de la structure de la base de données</h1>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h2 class="h5 mb-0">Résultats de la vérification</h2>
        </div>
        <div class="card-body">
            <?php if (empty($results)): ?>
                <div class="alert alert-info">
                    Aucun résultat à afficher.
                </div>
            <?php else: ?>
                <?php foreach ($results as $tableName => $tableResults): ?>
                    <div class="mb-4">
                        <h3 class="h5 mb-3">Table: <?= htmlspecialchars($tableName) ?></h3>
                        
                        <?php if (is_array($tableResults)): ?>
                            <ul class="list-group">
                                <?php foreach ($tableResults as $result): ?>
                                    <li class="list-group-item">
                                        <?= htmlspecialchars($result) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="alert <?= strpos($tableResults, 'conforme') !== false ? 'alert-success' : 'alert-warning' ?>">
                                <?= htmlspecialchars($tableResults) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-info text-white">
            <h2 class="h5 mb-0">Informations sur la vérification automatique</h2>
        </div>
        <div class="card-body">
            <p>
                La vérification automatique de la structure de la base de données est effectuée à chaque démarrage de l'application.
                Les résultats de cette vérification sont enregistrés dans le fichier de log <code>logs/db_updates.log</code>.
            </p>
            <p>
                Si des modifications sont nécessaires, elles sont appliquées automatiquement pour assurer la cohérence entre le code et la base de données.
            </p>
            <p>
                Cette fonctionnalité permet d'éviter les erreurs liées à des colonnes manquantes ou mal définies dans la base de données.
            </p>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="index.php?action=admin" class="btn btn-secondary">Retour à l'administration</a>
    </div>
</div> 