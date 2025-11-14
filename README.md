# 🐳 Docker PHP Minimale

Progetto didattico per imparare Docker, PHP e database (PostgreSQL/MySQL).

## Struttura

```
progetto/
├── docker/
│   ├── Dockerfile           # Ricetta PHP + Apache
│   └── php.ini              # Configurazione PHP
├── migrations/
│   └── init.sql             # Schema database
├── public/
│   └── index.php            # Router principale
├── docker-compose.yml       # Orchestrazione container
├── .env                     # Variabili ambiente
├── .gitignore
├── Makefile                 # Comandi rapidi
└── README.md
```

## Quick Start (60 secondi)

```bash
# 1. Avvia container
make up

# 2. Accedi applicazione
curl http://localhost:8080

# 3. Testa API
curl http://localhost:8080/api/health
curl http://localhost:8080/api/users

# 4. Ferma
make down
```

## Scegliere Database

### PostgreSQL (default)
```bash
# .env
DB_DRIVER=pgsql
DB_PORT=5432
```

### MySQL
1. Modifica `docker-compose.yml`:
```yaml
db:
  image: mysql:8.0-alpine  # Cambia questa riga
  ports:
    - "3306:3306"          # Attiva porta MySQL
    # - "5432:5432"        # Disattiva porta PostgreSQL
  healthcheck:
    test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]  # Cambia questa riga
```

2. Modifica `.env`:
```bash
DB_DRIVER=mysql
DB_PORT=3306
```

3. Ricrea container:
```bash
make clean
make up
```

## API Endpoints

### Status
```bash
curl http://localhost:8080/api/health
# {"status":"ok","database":"connected","driver":"pgsql"}
```

### Leggi utenti
```bash
curl http://localhost:8080/api/users
# [{"id":"1","username":"alice","email":"alice@example.com"}, ...]
```

### Crea utente
```bash
curl -X POST http://localhost:8080/api/users \
  -H "Content-Type: application/json" \
  -d '{"username":"newuser","email":"new@example.com"}'
# {"id":"4","username":"newuser","email":"new@example.com"}
```

## Comandi Utili

```bash
make up              # Avvia container
make down            # Ferma container
make bash            # Terminal PHP container
make db              # Terminal database (auto-rileva PostgreSQL/MySQL)
make logs            # Visualizza log live
make health          # Health check API
make ps              # Stato container
make restart         # Riavvia container
make clean           # Resetta tutto (elimina volume)
```

## Acceso Database Direttamente

### PostgreSQL
```bash
# Via Makefile (rileva automaticamente)
make db

# Oppure manuale
docker-compose exec db psql -U appuser -d appdb

# Query
SELECT * FROM users;
SELECT * FROM posts;
```

### MySQL
```bash
# Via Makefile (rileva automaticamente)
make db

# Oppure manuale
docker-compose exec db mysql -u appuser -pappuser appdb

# Query
SELECT * FROM users;
SELECT * FROM posts;
```

## Modifica database

Edita `migrations/init.sql` e ricrea:

```bash
make clean
make up
```

## Troubleshooting

| Problema | Soluzione |
|----------|-----------|
| "Errore connessione DB" | Aspetta 5 sec, esegui `make restart` |
| "Port 8080 già in uso" | Modifica docker-compose.yml: `"8081:80"` |
| "Permission denied" | Usa `sudo make up` |
| "Database vuoto" | Controlla `migrations/init.sql` esiste |

## Concetti Imparati

- Orchestrazione container con docker-compose
- Passaggio variabili ambiente tra container
- Montaggio volumi per hot-reload
- Inizializzazione database automatica
- Connessione PHP via PDO (PostgreSQL/MySQL)
- Router minimale in PHP
- API REST base (GET/POST)

## Prossimi Step

1. Aggiungi nuovi endpoint in `index.php`
2. Modifica schema in `migrations/init.sql`
3. Installa estensioni PHP nel `Dockerfile`
4. Aggiungi nuovi servizi nel `docker-compose.yml`
5. Implementa logica database più complessa

---

**Mantieni questa versione semplice. Aggiungi complessità solo quando serve!**
