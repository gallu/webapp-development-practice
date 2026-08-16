.PHONY: up down permissions clean exec-php exec-mysql ps logs all-clean disintegrate

up:
	docker compose up -d --build

down:
	docker compose down

# Laravel用 パーミッションの調整
permissions:
	docker compose exec -u root php sh -c '\
		chgrp -R www-data storage bootstrap/cache && \
		chmod -R g+rwX storage bootstrap/cache && \
		find storage bootstrap/cache -type d -exec chmod g+s {} \;'

# プロジェクト専用クリーン（最も安全）
clean:
	docker compose down --rmi local --volumes

# Exec into app container
exec-php:
	docker compose exec php bash

# Exec into mysql container
exec-mysql:
	docker compose exec mysql bash

# Show container process list
ps:
	docker compose ps

# Tail logs for all services
logs:
	docker compose logs -f

# Docker 全域の軽めクリーン
all-clean:
	docker system prune -f

# Docker 全域の完全破壊
disintegrate:
	docker system prune -a --volumes -f
