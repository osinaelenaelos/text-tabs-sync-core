
<?php
require_once 'config/config.php';

header('Content-Type: application/json');

// Получаем данные запроса
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'test_db_connection':
        testDatabaseConnection($input['config']);
        break;
        
    case 'save_db_config':
        saveDatabaseConfig($input['config']);
        break;
        
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action'
        ]);
}

/**
 * Тестирование подключения к базе данных
 */
function testDatabaseConnection($config) {
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        
        // Проверяем соединение простым запросом
        $stmt = $pdo->query('SELECT 1');
        
        echo json_encode([
            'success' => true,
            'message' => 'Подключение к базе данных успешно установлено!'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка подключения: ' . $e->getMessage()
        ]);
    }
}

/**
 * Сохранение конфигурации базы данных
 */
function saveDatabaseConfig($config) {
    try {
        // Сначала тестируем подключение
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Если подключение успешно, сохраняем конфигурацию
        $configTemplate = "<?php
/**
 * Конфигурация базы данных
 * 
 * Этот файл содержит настройки подключения к MySQL базе данных.
 * Адаптируйте параметры под ваше окружение.
 */

// Определяем окружение (можно изменить через переменную окружения или настройки хостинга)
\$environment = \$_ENV['APP_ENV'] ?? 'production';

// Настройки для разных окружений
\$database_config = [
    'development' => [
        'host' => '{$config['host']}',
        'database' => '{$config['database']}',
        'username' => '{$config['username']}',
        'password' => '{$config['password']}',
        'charset' => 'utf8mb4',
        'port' => {$config['port']}
    ],
    'production' => [
        'host' => '{$config['host']}',
        'database' => '{$config['database']}',
        'username' => '{$config['username']}',
        'password' => '{$config['password']}',
        'charset' => 'utf8mb4',
        'port' => {$config['port']}
    ]
];

// Выбираем конфигурацию для текущего окружения
\$db_config = \$database_config[\$environment];

// Настройки подключения PDO
\$dsn = \"mysql:host={\$db_config['host']};port={\$db_config['port']};dbname={\$db_config['database']};charset={\$db_config['charset']}\";

\$pdo_options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => \"SET NAMES {\$db_config['charset']}\"
];

/**
 * Функция для получения подключения к базе данных
 * @return PDO
 */
function getDBConnection() {
    global \$dsn, \$db_config, \$pdo_options;
    
    try {
        \$pdo = new PDO(\$dsn, \$db_config['username'], \$db_config['password'], \$pdo_options);
        return \$pdo;
    } catch (PDOException \$e) {
        // В production логируем ошибку, но не показываем детали
        error_log(\"Database connection failed: \" . \$e->getMessage());
        
        // Возвращаем общую ошибку
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database connection failed'
        ]);
        exit;
    }
}

/**
 * Функция для безопасного закрытия подключения
 * @param PDO \$pdo
 */
function closeDBConnection(&\$pdo) {
    \$pdo = null;
}
?>";

        // Записываем файл конфигурации
        $configPath = __DIR__ . '/config/database.php';
        file_put_contents($configPath, $configTemplate);
        
        echo json_encode([
            'success' => true,
            'message' => 'Настройки базы данных успешно сохранены!'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка при сохранении: ' . $e->getMessage()
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка записи файла: ' . $e->getMessage()
        ]);
    }
}
?>
