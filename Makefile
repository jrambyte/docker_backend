.PHONY: up down bash db logs health

up:
	docker-compose up -d
	@echo "✔ Container avviati"
	@echo "App: http://localhost:8080"
	@echo "Aspetta 5 secondi per l'inizializzazione database..."
	@sleep 5
	@echo "Verifica: curl http://localhost:8080/api/health"

down:
	docker-compose down
	@echo "✔ Container fermati"

bash:
	docker-compose exec app bash

db:
	@driver=$$(grep ^DB_DRIVER .env | cut -d= -f2); \
	if [ "$$driver" = "mysql" ]; then \
		docker-compose exec db mysql -u appuser -p appuser appdb; \
	else \
		docker-compose exec db psql -U appuser -d appdb; \
	fi

logs:
	docker-compose logs -f app

health:
	@echo "Health check:"
	@curl -s http://localhost:8080/api/health | json_pp || curl -s http://localhost:8080/api/health

ps:
	docker-compose ps

restart:
	docker-compose restart

clean:
	docker-compose down -v
	@echo "✔ Volume rimossi"
