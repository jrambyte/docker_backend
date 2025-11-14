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
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Docker PHP <?php echo $db_driver; ?></title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #333; margin: 0 0 10px; }
        .subtitle { color: #666; margin-bottom: 30px; }
        .status { padding: 15px; margin: 20px 0; border-radius: 5px; font-weight: bold; }
        .ok { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .section { margin: 30px 0; }
        h3 { color: #333; margin: 20px 0 10px; }
        code { background: #f0f0f0; padding: 2px 8px; border-radius: 3px; font-family: 'Courier New'; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 5px; overflow-x: auto; }
        a { color: #0066cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Docker PHP + <?php echo strtoupper($db_driver); ?></h1>
        <p class="subtitle">Struttura minimale per imparare Docker</p>

        <div class="status ok">
            <?php echo $db_status; ?>
        </div>

        <div class="section">
            <h3>Endpoint API</h3>
            <code>GET /api/health</code> - Status sistema<br><br>
            <code>GET /api/users</code> - Lista utenti<br><br>
            <code>POST /api/users</code> - Crea utente
        </div>

        <div class="section">
            <h3>Comandi Rapidi</h3>
            <pre>
                make up              # Avvia
                make down            # Ferma
                make bash            # Terminal container
                make db              # Terminal database
                make logs            # Visualizza log
            </pre>
        </div>

        <div class="section">
            <h3>Struttura Progetto</h3>
            <pre>mio_progetto/
                            ├── docker/
                            │   ├── Dockerfile
                            │   └── php.ini
                            ├── migrations/
                            │   └── init.sql
                            ├── public/
                            │   └── index.php
                            ├── docker-compose.yml
                            ├── .env
                            ├── .gitignore
                            └── Makefile</pre>
        </div>
    </div>
</body>
</html>
    <?php
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
