.PHONY: up down logs clean restart status shell db-shell

# Start all services
up:
	@echo "Starting WordPress booking application..."
	docker-compose up -d
	@echo "\n✓ Services started successfully!"
	@echo "→ WordPress: http://localhost:8080"
	@echo "→ phpMyAdmin: http://localhost:8081"
	@echo "\nDatabase credentials:"
	@echo "  User: wordpress"
	@echo "  Password: wordpress"
	@echo "  Database: wordpress"

# Stop all services
down:
	@echo "Stopping all services..."
	docker-compose down
	@echo "✓ Services stopped"

# View logs
logs:
	docker-compose logs -f

# View logs for specific service
logs-wp:
	docker-compose logs -f wordpress

logs-db:
	docker-compose logs -f db

# Clean up everything (containers, volumes, networks)
clean:
	@echo "⚠️  WARNING: This will remove all containers, volumes, and data!"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		docker-compose down -v; \
		echo "✓ All containers, volumes, and data removed"; \
	else \
		echo "Cancelled"; \
	fi

# Restart all services
restart:
	@echo "Restarting all services..."
	docker-compose restart
	@echo "✓ Services restarted"

# Show status of all services
status:
	docker-compose ps

# Open bash shell in WordPress container
shell:
	docker exec -it wp_booking_app bash

# Open MySQL shell
db-shell:
	docker exec -it wp_booking_db mysql -u wordpress -pwordpress wordpress

# View WordPress logs to check if ready for installation
check:
	@echo "Checking WordPress status..."
	@echo "Visit http://localhost:8080 to complete installation"
	@echo "The booking plugin is mounted at: ./wp-booking-plugin/"

# Help
help:
	@echo "WordPress Booking Plugin Development Commands:"
	@echo ""
	@echo "  make up              - Start all services"
	@echo "  make down            - Stop all services"
	@echo "  make logs            - View all logs (follow mode)"
	@echo "  make logs-wp         - View WordPress logs only"
	@echo "  make logs-db         - View database logs only"
	@echo "  make clean           - Remove all containers and volumes (with confirmation)"
	@echo "  make restart         - Restart all services"
	@echo "  make status          - Show status of all services"
	@echo "  make shell           - Open bash in WordPress container"
	@echo "  make db-shell        - Open MySQL shell"
	@echo "  make check           - Check WordPress status and get installation URL"
	@echo "  make help            - Show this help message"
