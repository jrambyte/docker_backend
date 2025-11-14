# 📚 GUIDA MINIMALE - Impara Docker Step by Step

Questa è la versione **più leggera e semplice possibile**. Ogni file ha **un solo scopo**.

## Principi Fondamentali

| Concetto | Che cos'è |
|----------|-----------|
| **Container** | Scatola isolata che contiene applicazione + dipendenze |
| **Image** | Ricetta per creare container (come un template) |
| **docker-compose.yml** | Orchestrazione: definisce quali servizi avviare e come comunicano |
| **Volume** | Cartella condivisa tra host e container (hot-reload) |
| **Rete** | Bridge che permette ai container di comunicarsi |

## Struttura Minimale

```
progetto/
├── docker/
│   └── Dockerfile              # Ricetta PHP
│   └── php.ini                 # Config PHP
├── migrations/
│   └── init.sql                # Crea tabelle + dati di test
├── public/
│   └── index.php               # Tutta l'app in un file
├── docker-compose.yml          # Definisce container
├── .env                        # Variabili ambiente
└── Makefile                    # Comandi rapidi
```

## Passo 1: Crea la Struttura

```bash
mkdir -p progetto/{docker,migrations,public}
cd progetto

# Copia i file da questa guida:
# docker-compose.yml → progetto/
# docker/Dockerfile → progetto/docker/
# docker/php.ini → progetto/docker/
# .env → progetto/
# Makefile → progetto/
# public/index.php → progetto/public/
# migrations/init.sql → progetto/migrations/
```

## Passo 2: Avvia

```bash
make up
```

### Cosa succede internamente:

```
1. Docker legge docker-compose.yml
2. Legge .env per variabili
3. Crea immagine PHP da Dockerfile
4. Avvia 2 container:
   - app (PHP + Apache)
   - db (PostgreSQL o MySQL)
5. Esegue migrations/init.sql per creare tabelle
6. Monta progetto locale inside container
   → Modifiche a index.php ricaricano automaticamente
```

## 🔄 Passo 3: Testa

```bash
# Homepage
curl http://localhost:8080

# Health check API
curl http://localhost:8080/api/health

# Leggi utenti
curl http://localhost:8080/api/users

# Crea utente
curl -X POST http://localhost:8080/api/users \
  -H "Content-Type: application/json" \
  -d '{"username":"test","email":"test@example.com"}'
```

## 📝 Passo 4: Accedi al Database

### PostgreSQL (default)
```bash
make db

# Inside psql prompt:
SELECT * FROM users;
SELECT * FROM posts;
\q
```

### MySQL
Stessa procedura, make db rileva il driver automaticamente.

## 🔧 Modifica Struttura

### Vuoi aggiungere un endpoint?

Modifica `public/index.php`:

```php
// Aggiungi prima dell'ultimo else
else if ($path === '/api/posts' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    try {
        $stmt = $pdo->query("SELECT * FROM posts");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
```

Reload automatico ✔

### Vuoi aggiungere una tabella?

Modifica `migrations/init.sql`:

```sql
CREATE TABLE IF NOT EXISTS comments (
    id SERIAL PRIMARY KEY,
    post_id INTEGER NOT NULL REFERENCES posts(id),
    text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Ricrea container:

```bash
make clean
make up
```

### Vuoi installare estensione PHP?

Modifica `docker/Dockerfile`:

```dockerfile
# Aggiungi dopo docker-php-ext-install:
RUN docker-php-ext-install pdo pdo_pgsql pdo_mysql json curl

# Poi ricrea:
make down
docker-compose up -d --build
```

## 🔌 Variabili Ambiente Spiegate

Nel `.env`:

```bash
# Quale database usi
DB_DRIVER=pgsql

# Indirizzo container database
# Nome servizio in docker-compose = hostname
DB_HOST=db

# Porta (5432 PostgreSQL, 3306 MySQL)
DB_PORT=5432

# Nome database
DB_NAME=appdb

# Credenziali
DB_USER=appuser
DB_PASSWORD=appuser
```

In PHP, leggi con:

```php
$driver = getenv('DB_DRIVER');
$host = getenv('DB_HOST');
```

## 🔀 Switch Database: PostgreSQL → MySQL

### 1. Modifica docker-compose.yml

Cambia:
```yaml
db:
  image: postgres:16-alpine
```

In:
```yaml
db:
  image: mysql:8.0-alpine
```

Cambia porta:
```yaml
ports:
  - "5432:5432"
```

In:
```yaml
ports:
  - "3306:3306"
```

Cambia healthcheck:
```yaml
healthcheck:
  test: ["CMD-SHELL", "pg_isready -U appuser"]
```

In:
```yaml
healthcheck:
  test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
```

### 2. Modifica .env

```bash
DB_DRIVER=mysql
DB_PORT=3306
```

### 3. Ricrea

```bash
make clean
make up
```

## 📊 File Spiegati

### docker-compose.yml

Orestra 2 servizi:

```yaml
services:
  app:      # Container PHP + Apache
  db:       # Container PostgreSQL o MySQL
```

Permettono di comunicarsi via nome servizio (hostname).

### Dockerfile

Ricetta per immagine PHP:

```dockerfile
FROM php:8.3-apache
# Parte da immagine ufficiale PHP 8.3 con Apache

RUN docker-php-ext-install pdo pdo_pgsql
# Installa estensioni PHP per database

COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
# Copia configurazione PHP
```

### index.php

Router minimale **in un solo file**:

```php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/') {
    // Homepage
}
else if ($path === '/api/health') {
    // Health check
}
else if ($path === '/api/users') {
    // Lista utenti
}
```

Aggiungere endpoint = aggiungere `else if`.

### init.sql

Crea tabelle al **primo avvio**:

```sql
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Eseguito automaticamente da container database.

## 🛠 Comandi di Sviluppo

```bash
make up              # ▶️ Avvia
make down            # ⏹ Ferma
make bash            # 💻 Terminal PHP container
make db              # 🗄️ Terminal database
make logs            # 📋 Log live
make health          # ✅ Health check
make clean           # 🗑️ Resetta tutto
```

## 🐛 Problemi Comuni

### "Errore di connessione database"

**Causa**: Container database sta ancora inizializzando

**Soluzione**:
```bash
make restart
# Aspetta 10 secondi
curl http://localhost:8080/api/health
```

### "Permission denied"

**Soluzione**:
```bash
sudo make up
```

### "Port 8080 già in uso"

Modifica docker-compose.yml:
```yaml
ports:
  - "8081:80"  # Cambia prima porta
```

Accedi: `http://localhost:8081`

### "Non vedo la mia modifica a index.php"

Controlla che ci sia il volume nel docker-compose.yml:
```yaml
volumes:
  - .:/var/www/html  # ← Deve esserci
```

## 💡 Cosa Hai Imparato

✔ Creare immagine Docker con Dockerfile
✔ Orchestrare container con docker-compose
✔ Passare variabili ambiente tra container
✔ Montare volumi per hot-reload
✔ Inizializzare database automaticamente
✔ Connettere PHP a PostgreSQL e MySQL via PDO
✔ Creare router minimale in PHP
✔ Implementare API REST base

## 📈 Prossimi Livelli

### Livello 2: Aggiungi complessità
- Aggiungi service redis per cache
- Aggiungi phpmyadmin o pgadmin
- Middleware di autenticazione
- Gestione errori più robusta

### Livello 3: Produzione
- Dockerfile multi-stage
- Environment variables da secrets
- Reverse proxy nginx
- Load balancing
- Monitoring e logging

### Livello 4: Framework
- Migra a Laravel/Symfony
- Database migrations
- ORM (Eloquent/Doctrine)
- API versioning

---

**Principio**: Mantieni la versione minimale finché non capisci tutto. Aggiungi complessità gradualmente.
