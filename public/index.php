<?php
// public/index.php
// Router minimale con 3 endpoint per imparare Docker

// Variabili ambiente
$db_driver = getenv('DB_DRIVER') ?: 'pgsql';
$db_host = getenv('DB_HOST') ?: 'db';
$db_port = getenv('DB_PORT') ?: 5432;
$db_name = getenv('DB_NAME') ?: 'appdb';
$db_user = getenv('DB_USER') ?: 'appuser';
$db_password = getenv('DB_PASSWORD') ?: 'appuser';

// Connessione PDO
try {
    if ($db_driver === 'pgsql') {
        $dsn = "pgsql:host={$db_host};port={$db_port};dbname={$db_name}";
        $pdo = new PDO($dsn, $db_user, $db_password);
    } else {
        $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name}";
        $pdo = new PDO($dsn, $db_user, $db_password);
    }
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db_status = "✔ Database connesso ({$db_driver})";
} catch (PDOException $e) {
    $db_status = "✘ Errore: " . $e->getMessage();
    $pdo = null;
}

// Router minimale
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Endpoint 1: GET / → Homepage
if ($path === '/') {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'ok',
        'message' => 'API REST Backend',
        'version' => '1.0',
        'endpoints' => [
            'GET /api/health' => 'Health check',
            'GET /api/users' => 'List users',
            'POST /api/users' => 'Create user',
            'GET /api/posts' => 'List posts'
        ]
    ]);
}

// Endpoint 2: GET /api/health → Status sistema
else if ($path === '/api/health') {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'ok',
        'database' => $pdo ? 'connected' : 'failed',
        'driver' => $db_driver
    ]);
}

// Endpoint 3: GET /api/users → Lista utenti
else if ($path === '/api/users' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    
    if (!$pdo) {
        http_response_code(503);
        echo json_encode(['error' => 'Database not available']);
        exit;
    }
    
    try {
        $stmt = $pdo->query("SELECT id, username, email FROM users LIMIT 10");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Endpoint 4: POST /api/users → Crea utente
else if ($path === '/api/users' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (!$pdo) {
        http_response_code(503);
        echo json_encode(['error' => 'Database not available']);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['username'], $data['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Username e email richiesti']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$data['username'], $data['email']]);
        
        http_response_code(201);
        echo json_encode([
            'id' => $pdo->lastInsertId(),
            'username' => $data['username'],
            'email' => $data['email']
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// 404
else {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not found']);
}
