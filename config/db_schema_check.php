<?php
/**
 * Database schema verification and update script
 * This script compares the current database structure with the expected schema
 * and makes the necessary changes to synchronize them.
 */

// Include configuration file only if constants are not already defined
if (!defined('DB_HOST')) {
    require_once 'config.php';
}

/**
 * Class for checking and updating the database schema
 */
class DatabaseSchemaChecker
{
    private $pdo;
    private $expectedSchema;

    /**
     * Constructor
     * @param PDO $pdo PDO instance for database connection
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->initExpectedSchema();
    }

    /**
     * Initialize the expected database schema
     */
    private function initExpectedSchema()
    {
        // Definition of the expected schema for each table
        $this->expectedSchema = [
            'article' => [
                'id' => ['type' => 'int(11)', 'null' => false, 'default' => null, 'extra' => 'auto_increment'],
                'id_user' => ['type' => 'int(11)', 'null' => false, 'default' => null],
                'title' => ['type' => 'varchar(255)', 'null' => false, 'default' => null],
                'content' => ['type' => 'text', 'null' => false, 'default' => null],
                'date_creation' => ['type' => 'datetime', 'null' => false, 'default' => null],
                'date_update' => ['type' => 'datetime', 'null' => true, 'default' => null],
                'views_count' => ['type' => 'int(11)', 'null' => false, 'default' => '0']
            ],
            'comment' => [
                'id' => ['type' => 'int(11)', 'null' => false, 'default' => null, 'extra' => 'auto_increment'],
                'id_article' => ['type' => 'int(11)', 'null' => false, 'default' => null],
                'pseudo' => ['type' => 'varchar(255)', 'null' => false, 'default' => null],
                'content' => ['type' => 'text', 'null' => false, 'default' => null],
                'date_creation' => ['type' => 'datetime', 'null' => false, 'default' => null]
            ],
            'user' => [
                'id' => ['type' => 'int(11)', 'null' => false, 'default' => null, 'extra' => 'auto_increment'],
                'login' => ['type' => 'varchar(255)', 'null' => false, 'default' => null],
                'password' => ['type' => 'varchar(255)', 'null' => false, 'default' => null],
                'nickname' => ['type' => 'varchar(255)', 'null' => false, 'default' => null]
            ]
        ];
    }

    /**
     * Check and update the database schema
     * @return array Results of checks and updates
     */
    public function checkAndUpdateSchema()
    {
        $results = [];

        foreach ($this->expectedSchema as $tableName => $columns) {
            // Check if table exists
            if (!$this->tableExists($tableName)) {
                $results[$tableName] = "La table n'existe pas. Création nécessaire.";
                $this->createTable($tableName, $columns);
                continue;
            }

            // Get current table structure
            $currentColumns = $this->getTableColumns($tableName);
            
            // Compare columns
            $missingColumns = [];
            $modifiedColumns = [];

            foreach ($columns as $columnName => $columnDef) {
                if (!isset($currentColumns[$columnName])) {
                    $missingColumns[$columnName] = $columnDef;
                } else {
                    // Check if column definition matches
                    $currentDef = $currentColumns[$columnName];
                    if ($this->columnNeedsUpdate($columnDef, $currentDef)) {
                        $modifiedColumns[$columnName] = $columnDef;
                    }
                }
            }

            // Add missing columns
            if (!empty($missingColumns)) {
                foreach ($missingColumns as $columnName => $columnDef) {
                    $this->addColumn($tableName, $columnName, $columnDef);
                    $results[$tableName][] = "Colonne '$columnName' ajoutée.";
                }
            }

            // Modify columns that need updating
            if (!empty($modifiedColumns)) {
                foreach ($modifiedColumns as $columnName => $columnDef) {
                    $this->modifyColumn($tableName, $columnName, $columnDef);
                    $results[$tableName][] = "Colonne '$columnName' modifiée.";
                }
            }

            if (empty($missingColumns) && empty($modifiedColumns)) {
                $results[$tableName] = "La structure est conforme au schéma attendu.";
            }
        }

        return $results;
    }

    /**
     * Check if a table exists in the database
     * @param string $tableName Table name
     * @return bool True if the table exists, false otherwise
     */
    private function tableExists($tableName)
    {
        $stmt = $this->pdo->prepare("SHOW TABLES LIKE :tableName");
        $stmt->execute(['tableName' => $tableName]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get the column structure of a table
     * @param string $tableName Table name
     * @return array Column structure
     */
    private function getTableColumns($tableName)
    {
        $columns = [];
        $stmt = $this->pdo->prepare("DESCRIBE `$tableName`");
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[$row['Field']] = [
                'type' => $row['Type'],
                'null' => $row['Null'] === 'YES',
                'default' => $row['Default'],
                'extra' => $row['Extra']
            ];
        }
        
        return $columns;
    }

    /**
     * Check if a column needs to be updated
     * @param array $expected Expected column definition
     * @param array $current Current column definition
     * @return bool True if the column needs to be updated, false otherwise
     */
    private function columnNeedsUpdate($expected, $current)
    {
        // Compare types (ignore case and spaces)
        if (strtolower(trim($expected['type'])) !== strtolower(trim($current['type']))) {
            return true;
        }
        
        // Compare nullability
        if ($expected['null'] !== $current['null']) {
            return true;
        }
        
        // Compare default values
        if ($expected['default'] !== $current['default']) {
            return true;
        }
        
        // Compare extras if defined
        if (isset($expected['extra']) && $expected['extra'] !== $current['extra']) {
            return true;
        }
        
        return false;
    }

    /**
     * Create a new table
     * @param string $tableName Table name
     * @param array $columns Column definitions
     */
    private function createTable($tableName, $columns)
    {
        $columnDefs = [];
        
        foreach ($columns as $columnName => $columnDef) {
            $columnDefs[] = $this->buildColumnDefinition($columnName, $columnDef);
        }
        
        $primaryKey = 'id';
        $sql = "CREATE TABLE `$tableName` (" . implode(', ', $columnDefs) . ", PRIMARY KEY (`$primaryKey`))";
        
        try {
            $this->pdo->exec($sql);
            echo "Table '$tableName' créée avec succès.\n";
        } catch (PDOException $e) {
            echo "Erreur lors de la création de la table '$tableName': " . $e->getMessage() . "\n";
        }
    }

    /**
     * Add a column to a table
     * @param string $tableName Table name
     * @param string $columnName Column name
     * @param array $columnDef Column definition
     */
    private function addColumn($tableName, $columnName, $columnDef)
    {
        $columnDefinition = $this->buildColumnDefinition($columnName, $columnDef);
        $sql = "ALTER TABLE `$tableName` ADD $columnDefinition";
        
        try {
            $this->pdo->exec($sql);
            echo "Colonne '$columnName' ajoutée à la table '$tableName'.\n";
        } catch (PDOException $e) {
            echo "Erreur lors de l'ajout de la colonne '$columnName': " . $e->getMessage() . "\n";
        }
    }

    /**
     * Modify an existing column
     * @param string $tableName Table name
     * @param string $columnName Column name
     * @param array $columnDef New column definition
     */
    private function modifyColumn($tableName, $columnName, $columnDef)
    {
        $columnDefinition = $this->buildColumnDefinition($columnName, $columnDef);
        $sql = "ALTER TABLE `$tableName` MODIFY $columnDefinition";
        
        try {
            $this->pdo->exec($sql);
            echo "Colonne '$columnName' modifiée dans la table '$tableName'.\n";
        } catch (PDOException $e) {
            echo "Erreur lors de la modification de la colonne '$columnName': " . $e->getMessage() . "\n";
        }
    }

    /**
     * Build the SQL definition of a column
     * @param string $columnName Column name
     * @param array $columnDef Column definition
     * @return string SQL definition of the column
     */
    private function buildColumnDefinition($columnName, $columnDef)
    {
        $definition = "`$columnName` " . $columnDef['type'];
        
        if (!$columnDef['null']) {
            $definition .= " NOT NULL";
        } else {
            $definition .= " NULL";
        }
        
        if ($columnDef['default'] !== null) {
            $definition .= " DEFAULT '" . $columnDef['default'] . "'";
        }
        
        if (isset($columnDef['extra']) && !empty($columnDef['extra'])) {
            $definition .= " " . $columnDef['extra'];
        }
        
        return $definition;
    }
}

// Création de la connexion PDO
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Vérification et mise à jour du schéma
    $schemaChecker = new DatabaseSchemaChecker($pdo);
    $results = $schemaChecker->checkAndUpdateSchema();
    
    // Affichage des résultats
    echo "<h1>Résultats de la vérification du schéma de la base de données</h1>";
    echo "<pre>";
    print_r($results);
    echo "</pre>";
    
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
} 