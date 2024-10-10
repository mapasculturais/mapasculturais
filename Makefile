# Makefile for managing a project with multiple Docker Compose services

# Variables
DOCKER_COMPOSE_FILE := docker-compose.yml
COMPOSE := docker compose -f $(DOCKER_COMPOSE_FILE)
COMPOSE_DEV := docker compose -f $(DOCKER_COMPOSE_FILE) -f docker-compose.dev.yml
BACKEND_SERVICE := backend
FRONTEND_SERVICE := frontend

# Default target: show help
.PHONY: help
help:
	@echo "Available targets:"
	@echo "  up            - Start all services in the background (detached mode)"
	@echo "  down          - Stop services and remove containers"
	@echo "  ps            - List running containers"
	@echo "  dev           - Start backend and frontend in the background (dev mode)"
	@echo "  build-backend - Build the backend image"
	@echo "  build-frontend- Build the frontend image"
	@echo "  build         - Build both backend and frontend services"
	@echo "  logs          - Tail logs for all services"
	@echo "  logs-backend  - Tail logs for backend"
	@echo "  restart       - Restart services"
	@echo "  test-backend  - Run backend test suite"
	@echo "  test-frontend - Run frontend test suite"
	@echo "  lint-backend  - Lint the backend codebase"
	@echo "  lint-frontend - Lint the frontend codebase"
	@echo "  clean         - Clean dangling Docker images and volumes"

# Dev setup
.PHONY: dev 
dev:
	$(COMPOSE_DEV) up -d
	@echo "Services running in dev mode"

# List running containers
.PHONY: ps
ps:
	$(COMPOSE) ps
	@echo "Services running"

# Start all services in background (detached mode)
.PHONY: up
up:
	$(COMPOSE) up -d
	@echo "Services have been started in detached mode."

# Stop and remove all services and containers
.PHONY: down
down:
	$(COMPOSE) down
	@echo "Services have been stopped and removed."

# Create folders and fix permissions: doctrine, assets, private-files, 
.PHONY: init
init:
	cp .env.sample .env
	# $(COMPOSE) exec backend mkdir var/logs/
	# $(COMPOSE) exec backend mkdir var/private-files/
	@echo "Folders created."
	$(COMPOSE) exec backend chmod 777 var/logs/
	$(COMPOSE) exec backend chmod 777 var/private-files/
	$(COMPOSE) exec backend chmod 777 var/sessions/
	$(COMPOSE) exec backend chmod 777 var/DoctrineProxies/
	$(COMPOSE) exec backend chmod 777 public/assets/
	@echo "Permissions granted."


.PHONY: init_dev
init_dev:
	$(COMPOSE) exec backend composer install
	@echo "Permissions granted."

# Run Migrations
.PHONY: db-migrations
db-migrations:
	$(COMPOSE) exec backend php src/tools/apply-updates.php
	@echo "db updates applied."

# Restore dump database
.PHONY: db-restore
db-restore:
	$(COMPOSE) exec database bash -c "psql -h localhost -U mapas -d mapas < /data/dump.sql"
	@echo "dump.sql default database dump was restored."

# Build the backend service
.PHONY: build-backend
build-backend:
	$(COMPOSE) build $(BACKEND_SERVICE)
	@echo "Backend service has been built."

# # Build the frontend service
.PHONY: build-frontend
build-frontend:
# 	$(COMPOSE_DEV) run --rm frontend "/bin/sh -c 'npm install -g pnpm && pnpm install -s && pnpm run watch'"
	@echo "Frontend service has been built."

# Build all images (both backend and frontend)
.PHONY: build
build: build-backend build-frontend
	@echo "All services have been built."

# Tail logs for all services
.PHONY: logs
logs:
	$(COMPOSE) logs
	@echo "Displaying logs for all services."

# tail logs for backend
.phony: logs-backend
logs-backend:
	$(COMPOSE_DEV) exec $(BACKEND_SERVICE) tail -f var/logs/app.log
	@echo "displaying logs for backend."

# tail logs for frontend
.phony: logs-frontend
logs-frontend:
	$(COMPOSE_DEV) logs -f $(FRONTEND_SERVICE) 
	@echo "displaying logs for frontend."

# Restart all services (useful after code changes)
.PHONY: restart
restart:
	$(COMPOSE) restart
	@echo "All services have been restarted."

# Run the backend tests
.PHONY: test-backend
test-backend:
	$(COMPOSE) exec $(BACKEND_SERVICE) make test  # assuming you have a Makefile for testing inside backend
	@echo "Backend tests have been run."

# Run the frontend tests
.PHONY: test-frontend
test-frontend:
	$(COMPOSE) exec $(FRONTEND_SERVICE) npm test  # or whatever command you use
	@echo "Frontend tests have been run."

# Lint the backend code
.PHONY: lint-backend
lint-backend:
	$(COMPOSE) exec $(BACKEND_SERVICE) make lint  # assuming you have a Makefile target for this
	@echo "Backend code has been linted."

# Lint the frontend code
.PHONY: lint-frontend
lint-frontend:
	$(COMPOSE) exec $(FRONTEND_SERVICE) npm run lint  # or command appropriate for your frontend framework
	@echo "Frontend code has been linted."

# Clean Docker: remove any stopped container, dangling images, volumes, etc.
.PHONY: clean
clean:
	$(COMPOSE) down -v --remove-orphans
	@sudo find . -name 'node_modules' -type d -prune -exec rm -rf '{}' +
	@sudo rm -rf vendor
	@echo "Cleaned up Docker containers, images, volumes."
