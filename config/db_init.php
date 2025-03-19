<?php
if (!defined('DB_HOST')) {
    require_once 'config.php';
}

class DatabaseInitializer
{
    private $pdo;
    private $expectedSchema;
    private $logFile;
    private $updatePerformed = false;

    public function __construct(PDO $pdo, $logFile = null)
    {
        $this->pdo = $pdo;
        $this->logFile = $logFile;
        $this->initExpectedSchema();
    }

    private function initExpectedSchema()
    {
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

    public function checkAndUpdateSchema()
    {
        $results = [];

        foreach ($this->expectedSchema as $tableName => $columns) {
            if (!$this->tableExists($tableName)) {
                $message = "La table '$tableName' n'existe pas. Création nécessaire.";
                $this->log($message);
                $results[$tableName] = $message;
                $this->createTable($tableName, $columns);
                $this->updatePerformed = true;
                continue;
            }

            $currentColumns = $this->getTableColumns($tableName);
            
            $missingColumns = [];
            $modifiedColumns = [];

            foreach ($columns as $columnName => $columnDef) {
                if (!isset($currentColumns[$columnName])) {
                    $missingColumns[$columnName] = $columnDef;
                } else {
                    $currentDef = $currentColumns[$columnName];
                    if ($this->columnNeedsUpdate($columnDef, $currentDef)) {
                        $modifiedColumns[$columnName] = $columnDef;
                    }
                }
            }

            if (!empty($missingColumns)) {
                foreach ($missingColumns as $columnName => $columnDef) {
                    $message = "Colonne '$columnName' manquante dans la table '$tableName'. Ajout en cours...";
                    $this->log($message);
                    $this->addColumn($tableName, $columnName, $columnDef);
                    $results[$tableName][] = "Colonne '$columnName' ajoutée.";
                    $this->updatePerformed = true;
                }
            }

            if (!empty($modifiedColumns)) {
                foreach ($modifiedColumns as $columnName => $columnDef) {
                    $message = "Colonne '$columnName' dans la table '$tableName' nécessite une mise à jour. Modification en cours...";
                    $this->log($message);
                    $this->modifyColumn($tableName, $columnName, $columnDef);
                    $results[$tableName][] = "Colonne '$columnName' modifiée.";
                    $this->updatePerformed = true;
                }
            }

            if (empty($missingColumns) && empty($modifiedColumns)) {
                $results[$tableName] = "La structure est conforme au schéma attendu.";
            }
        }

        return $results;
    }

    public function wasUpdatePerformed()
    {
        return $this->updatePerformed;
    }

    private function tableExists($tableName)
    {
        $stmt = $this->pdo->prepare("SHOW TABLES LIKE :tableName");
        $stmt->execute(['tableName' => $tableName]);
        return $stmt->rowCount() > 0;
    }

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

    private function columnNeedsUpdate($expected, $current)
    {
        if (strtolower(trim($expected['type'])) !== strtolower(trim($current['type']))) {
            return true;
        }
        
        if ($expected['null'] !== $current['null']) {
            return true;
        }
        
        if ($expected['default'] !== $current['default']) {
            return true;
        }

        if (isset($expected['extra']) && $expected['extra'] !== $current['extra']) {
            return true;
        }
        
        return false;
    }


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
            $message = "Table '$tableName' créée avec succès.";
            $this->log($message);
        } catch (PDOException $e) {
            $message = "Erreur lors de la création de la table '$tableName': " . $e->getMessage();
            $this->log($message, true);
        }
    }

    private function addColumn($tableName, $columnName, $columnDef)
    {
        $columnDefinition = $this->buildColumnDefinition($columnName, $columnDef);
        $sql = "ALTER TABLE `$tableName` ADD $columnDefinition";
        
        try {
            $this->pdo->exec($sql);
            $message = "Colonne '$columnName' ajoutée à la table '$tableName'.";
            $this->log($message);
        } catch (PDOException $e) {
            $message = "Erreur lors de l'ajout de la colonne '$columnName': " . $e->getMessage();
            $this->log($message, true);
        }
    }

    private function modifyColumn($tableName, $columnName, $columnDef)
    {
        $columnDefinition = $this->buildColumnDefinition($columnName, $columnDef);
        $sql = "ALTER TABLE `$tableName` MODIFY $columnDefinition";
        
        try {
            $this->pdo->exec($sql);
            $message = "Colonne '$columnName' modifiée dans la table '$tableName'.";
            $this->log($message);
        } catch (PDOException $e) {
            $message = "Erreur lors de la modification de la colonne '$columnName': " . $e->getMessage();
            $this->log($message, true);
        }
    }

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

    private function log($message, $isError = false)
    {
        $logPrefix = $isError ? '[ERROR] ' : '[INFO] ';
        $logMessage = date('Y-m-d H:i:s') . ' ' . $logPrefix . $message . PHP_EOL;
        
        if ($this->logFile) {
            file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        }
    }
}

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
    
    $logFile = __DIR__ . '/../logs/db_updates.log';
    
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }
    
    $dbInitializer = new DatabaseInitializer($pdo, $logFile);
    $dbInitializer->checkAndUpdateSchema();
    
} catch (PDOException $e) {
    $logFile = __DIR__ . '/../logs/db_errors.log';
    
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }
    
    $errorMessage = date('Y-m-d H:i:s') . ' [FATAL] Erreur de connexion à la base de données: ' . $e->getMessage() . PHP_EOL;
    file_put_contents($logFile, $errorMessage, FILE_APPEND);
} 