# SWITCH RAPIDO DATABASE

Per passare rapidamente tra PostgreSQL e MySQL, segui queste istruzioni.

## Da PostgreSQL a MySQL (5 step)

### 1. Modifica docker-compose.yml

Sezione `db` → Cambia image e porta:

```yaml
db:
  image: mysql:8.0-alpine  # ← Cambia questa riga
  
  ports:
    - "3306:3306"  # ← PostgreSQL 5432 → MySQL 3306
```

### 2. Modifica Healthcheck

Stesso servizio `db`, sezione `healthcheck`:

```yaml
healthcheck:
  test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]  # ← Cambia
  interval: 10s
  timeout: 5s
  retries: 5
```

### 3. Modifica .env

```bash
DB_DRIVER=mysql     # ← Cambia da pgsql
DB_PORT=3306        # ← Cambia da 5432
```

### 4. Elimina volume precedente

```bash
make clean
```

### 5. Riavvia

```bash
make up
```

---

## Da MySQL a PostgreSQL (5 step)

### 1. Modifica docker-compose.yml

Sezione `db` → Cambia image e porta:

```yaml
db:
  image: postgres:16-alpine  # ← Cambia questa riga
  
  ports:
    - "5432:5432"  # ← MySQL 3306 → PostgreSQL 5432
```

### 2. Modifica Healthcheck

Stesso servizio `db`, sezione `healthcheck`:

```yaml
healthcheck:
  test: ["CMD-SHELL", "pg_isready -U appuser"]  # ← Cambia
  interval: 10s
  timeout: 5s
  retries: 5
```

### 3. Modifica .env

```bash
DB_DRIVER=pgsql    # ← Cambia da mysql
DB_PORT=5432       # ← Cambia da 3306
```

### 4. Elimina volume precedente

```bash
make clean
```

### 5. Riavvia

```bash
make up
```

---

## Verifica Switch Avvenuto

```bash
# Health check
curl http://localhost:8080/api/health

# Accedi database
make db

# Query di test
SELECT * FROM users;
```

---

## Schema SQL Compatibile

Il file `migrations/init.sql` è compatibile con entrambi i database:

```sql
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,  # ← Funziona sia su PostgreSQL che MySQL
    username VARCHAR(100),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## Differenze da Conoscere

| Aspetto | PostgreSQL | MySQL |
|---------|-----------|-------|
| **Image** | postgres:16-alpine | mysql:8.0-alpine |
| **Porta default** | 5432 | 3306 |
| **Utente** | postgres / appuser | root / appuser |
| **CLI** | psql | mysql |
| **Serial ID** | SERIAL | AUTO_INCREMENT |
| **Timestamp** | DEFAULT CURRENT_TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| **ON CONFLICT** | ON CONFLICT DO NOTHING | ON DUPLICATE KEY UPDATE |

---

## Comando One-Liner

Per switch completo in una riga (PostgreSQL → MySQL):

```bash
# 1. Modifica docker-compose.yml manualmente (vedi sopra)
# 2. Poi:
make clean && \
sed -i 's/DB_DRIVER=pgsql/DB_DRIVER=mysql/g' .env && \
sed -i 's/DB_PORT=5432/DB_PORT=3306/g' .env && \
make up
```

---

## Backup Database Prima di Switch

```bash
# PostgreSQL backup
make db
\dt  # Lista tabelle
SELECT * FROM users;  # Esporta mentalmente i dati

# MySQL backup
make db
SHOW TABLES;
SELECT * FROM users;
```

---

## Reset Totale (nuclearmente sicuro)

Se qualcosa va storto:

```bash
# Ferma tutto
docker-compose down -v

# Rimuovi volumi
docker volume prune

# Ricrea da zero
docker-compose up -d --build

# Verifica
make health
```
