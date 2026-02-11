# =============================================================================
# Makefile for Laravel SaaS Attendance Management System
# Common commands for development and production
# =============================================================================

.PHONY: help build up down restart logs shell migrate seed fresh queue schedule test lint

# Default target
help:
	@echo "Laravel Attendance Management System - Available Commands:"
	@echo ""
	@echo "  Docker Commands:"
	@echo "    make build      - Build Docker containers"
	@echo "    make up         - Start all containers"
	@echo "    make up-dev     - Start all containers with dev profile (includes phpMyAdmin)"
	@echo "    make down       - Stop all containers"
	@echo "    make restart    - Restart all containers"
	@echo "    make logs       - View container logs"
	@echo "    make shell      - Open shell in app container"
	@echo ""
	@echo "  Laravel Commands:"
	@echo "    make install    - Install Composer dependencies"
	@echo "    make key        - Generate application key"
	@echo "    make migrate    - Run database migrations"
	@echo "    make seed       - Seed the database"
	@echo "    make fresh      - Fresh migration with seeding"
	@echo "    make cache      - Clear and rebuild all caches"
	@echo "    make queue      - Start queue worker"
	@echo "    make schedule   - Run scheduler"
	@echo "    make tinker     - Open Laravel Tinker"
	@echo ""
	@echo "  Development Commands:"
	@echo "    make test       - Run PHPUnit tests"
	@echo "    make lint       - Run PHP CS Fixer"
	@echo "    make npm-dev    - Run npm development build"
	@echo "    make npm-watch  - Run npm watch"
	@echo "    make npm-prod   - Run npm production build"

# =============================================================================
# Docker Commands
# =============================================================================

build:
	docker compose build

up:
	docker compose up -d

up-dev:
	docker compose --profile dev up -d

down:
	docker compose down

restart:
	docker compose restart

logs:
	docker compose logs -f

logs-app:
	docker compose logs -f app

logs-queue:
	docker compose logs -f queue

shell:
	docker compose exec app bash

shell-root:
	docker compose exec -u root app bash

# =============================================================================
# Laravel Commands
# =============================================================================

install:
	docker compose exec app composer install

key:
	docker compose exec app php artisan key:generate

migrate:
	docker compose exec app php artisan migrate

migrate-central:
	docker compose exec app php artisan migrate --path=database/migrations/central

seed:
	docker compose exec app php artisan db:seed

fresh:
	docker compose exec app php artisan migrate:fresh --seed

rollback:
	docker compose exec app php artisan migrate:rollback

cache:
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan route:clear
	docker compose exec app php artisan view:clear
	docker compose exec app php artisan cache:clear

cache-prod:
	docker compose exec app php artisan config:cache
	docker compose exec app php artisan route:cache
	docker compose exec app php artisan view:cache

queue:
	docker compose exec app php artisan queue:work redis --sleep=3 --tries=3

queue-restart:
	docker compose exec app php artisan queue:restart

schedule:
	docker compose exec app php artisan schedule:run

horizon:
	docker compose exec app php artisan horizon

tinker:
	docker compose exec app php artisan tinker

# =============================================================================
# Artisan Wrapper (pass commands directly)
# Usage: make artisan cmd="route:list"
# =============================================================================

artisan:
	docker compose exec app php artisan $(cmd)

# =============================================================================
# Device Commands
# =============================================================================

poll-devices:
	docker compose exec app php artisan attendance:poll-devices

import-device:
	docker compose exec app php artisan attendance:import-from-device $(device_id)

process-attendance:
	docker compose exec app php artisan attendance:process

# =============================================================================
# Testing & Quality
# =============================================================================

test:
	docker compose exec app php artisan test

test-coverage:
	docker compose exec app php artisan test --coverage

lint:
	docker compose exec app ./vendor/bin/pint

lint-fix:
	docker compose exec app ./vendor/bin/pint --repair

# =============================================================================
# Frontend (NPM)
# =============================================================================

npm-install:
	docker compose exec app npm install

npm-dev:
	docker compose exec app npm run dev

npm-watch:
	docker compose exec app npm run dev -- --watch

npm-prod:
	docker compose exec app npm run build

# =============================================================================
# Tenancy Commands
# =============================================================================

tenant-create:
	docker compose exec app php artisan tenant:create $(domain)

tenant-migrate:
	docker compose exec app php artisan tenants:migrate

tenant-seed:
	docker compose exec app php artisan tenants:seed

# =============================================================================
# Production Setup
# =============================================================================

setup:
	@echo "Setting up Laravel Attendance Management System..."
	docker compose build
	docker compose up -d
	@echo "Waiting for MySQL to be ready..."
	sleep 10
	docker compose exec app composer install
	docker compose exec app cp .env.example .env
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate --seed
	docker compose exec app npm install
	docker compose exec app npm run build
	@echo ""
	@echo "Setup complete! Application available at http://localhost:8080"
