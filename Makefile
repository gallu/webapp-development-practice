.PHONY: up down permissions clean exec-php exec-mysql ps logs disintegrate

# Docker環境を起動
up:
	docker compose up -d --build

# Docker環境を停止・削除
# DBなどのvolumeは残す
down:
	docker compose down

# Laravel用パーミッションの調整
# storage と bootstrap/cache を php-fpm(www-data) から書き込み可能にする
permissions:
	docker compose exec -u root php sh -c '\
		chgrp -R www-data storage bootstrap/cache && \
		chmod -R g+rwX storage bootstrap/cache && \
		find storage bootstrap/cache -type d -exec chmod g+s {} \;'

# プロジェクト単位のクリーンアップ
# コンテナ、ネットワーク、ローカルimage、不要なorphansを削除する
# DBなどのvolumeは残す
clean:
	docker compose down --rmi local --remove-orphans

# PHPコンテナへ入る
exec-php:
	docker compose exec php bash

# MySQLコンテナへ入る
exec-mysql:
	docker compose exec mysql bash

# このComposeプロジェクトのコンテナ状態を表示
ps:
	docker compose ps

# 全サービスのログを追跡表示
logs:
	docker compose logs -f

# このComposeプロジェクトをDBデータ込みで完全に削除
# named volumeも削除されるため、MySQLのデータも消える
# 初期状態へ戻したい場合のみ使用する
disintegrate:
	docker compose down --rmi local --volumes --remove-orphans
