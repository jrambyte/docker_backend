📝 Comandi Equivalenti (Senza Make)

make up: docker-compose up -d
make down: docker-compose down
make logs: docker-compose logs -f app
make bash: docker-compose exec app bash
make db: docker-compose exec db psql -U appuser -d appdb
make ps: docker-compose ps
make restart: docker-compose restart
make clean: docker-compose down -v

# 1. Vai nella cartella del progetto
cd docker_tuo_progetto

# 2. Avvia i container
docker-compose up -d

# 3. Aspetta 5 secondi per l'inizializzazione

# 4. Testa
curl http://localhost:8080
curl http://localhost:8080/api/health
curl http://localhost:8080/api/users

# 5. Ferma
docker-compose down