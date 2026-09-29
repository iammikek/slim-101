.PHONY: test serve migrate docker-up docker-down

test:
	./vendor/bin/phpunit

serve:
	php -S $${APP_HOST:-127.0.0.1}:$${PORT:-8016} -t public public/index.php

migrate:
	php database/migrate.php database/database.sqlite

docker-up:
	docker compose up --build

docker-down:
	docker compose down
