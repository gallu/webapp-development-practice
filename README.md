# Web Application Development Practice

開発用のシンプルな Docker ベース環境です。  
PHP（php-fpm）・nginx・MySQL・Redis を含む基本構成を提供し、  
`src/` に配置した Laravel 製のToDoアプリケーションを実行できます。

ToDoアプリケーションには、ログイン、ToDoの登録・一覧・詳細・編集・完了・削除機能があります。

---

## セットアップ

インストールは以下のようにします。

```bash
composer create-project gallu/docker-app-skeleton [my-app-name]
```

---

## 構成

```
/
├─ docker-compose.yml
├─ docker/
│   ├─ nginx/
│   │   ├─ Dockerfile
│   │   └─ default.conf
│   ├─ php/
│   │   └─ Dockerfile
│   └─ mysql/
│       └─ init/（初回起動時に app_testing を作成）
├─ storage/
│   ├─ db/（bind mount に切り替えたときに使用）
│   ├─ logs/
│   └─ cache/
├─ src/
│   ├─ app/
│   ├─ database/
│   ├─ resources/
│   ├─ routes/
│   └─ tests/
└─ scripts/
    └─ setup.sh
```

---

## セットアップ手順

### 1. 初期ディレクトリ作成

```
sh ./scripts/setup.sh
```

### 2. Laravelアプリケーションの準備

`src/` にはLaravelアプリケーションが配置済みです。`src/` で新たに
`composer create-project` を実行する必要はありません。

`src/public/` がWeb rootとしてnginxから参照されます。

---

## 起動

先にリポジトリ直下で `.env.sample` を `.env` にコピーし、
`COMPOSE_PROJECT_NAME` と `WEB_PORT` を環境に合わせて変更してください。

```
cp .env.sample .env
docker compose up --build -d
```

`.env.sample` の初期値では `WEB_PORT=8081` です。

## 停止

```
docker compose down
```

## PHP へのアクセス

`.env` の `WEB_PORT` で公開したポートにアクセスします。

```
http://localhost:<WEB_PORT>/
```

初期値のままなら `http://localhost:8081/` です。

---

## MySQL

```
docker compose exec mysql bash
mysql -u root -p
```

---

## PHP → MySQL 接続例

src/public/test_mysql.php:

```php
<?php

try {
    $pdo = new PDO(
        'mysql:host=mysql;dbname=app;charset=utf8mb4',
        'app',
        'app',
        [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ]
    );

    echo "OK: Connected to MySQL\n";
} catch (PDOException $e) {
    echo "NG: " . $e->getMessage();
}
```

---

## Redis

RedisコンテナとPHPのRedis拡張は利用できますが、現在のToDoアプリケーションでは
セッション、キャッシュ、キューにRedisを使用していません。これらには`database`
ドライバーを使用し、`DB_CONNECTION`で指定したデータベースへ保存します。

LaravelからRedisを利用する場合、PHPコンテナ内から接続するホスト名は`redis`です。
`src/.env`の`REDIS_HOST`を次のように設定し、用途に応じて`CACHE_STORE`、
`SESSION_DRIVER`、`QUEUE_CONNECTION`を`redis`へ変更してください。

```dotenv
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379
```

---

## Makefile Commands

開発環境の操作を簡略化するため、いくつかのコマンドを Makefile として提供しています。
以下は各コマンドの動作内容と注意点です。

### up
コンテナ群をバックグラウンドで起動します。必要に応じてビルドも実行します。

    make up

### down
現在の docker-compose プロジェクトで起動中のコンテナを停止し、ネットワークを削除します。
MySQLのnamed volume（`mysql_data`）は削除しません。

    make down

### permissions
Laravelが書き込む`src/storage/`と`src/bootstrap/cache/`について、PHP-FPMの実行ユーザーが
書き込めるようにグループとパーミッションを調整します。コンテナの起動後に実行してください。

    make permissions

### clean
この docker-compose プロジェクトで生成されたリソースのみを削除します。
以下が削除対象です：
- コンテナ
- ネットワーク
- このプロジェクト内でビルドされたイメージ
- このプロジェクト内で作成されたボリューム（MySQLの`mysql_data`を含む）

他プロジェクトには影響しません。

    make clean

### all-clean
Docker 全体に対して `docker system prune -f` を実行します。
以下が削除されます：
- 停止中のすべてのコンテナ
- 未使用のネットワーク
- 参照されていないイメージ
- Build キャッシュ

複数の Docker プロジェクトを扱っている場合は注意してください。

    make all-clean

### disintegrate
Docker 全体に対して最も強力なクリーンアップを実行します。
以下が削除対象です：
- 停止中のすべてのコンテナ
- 未使用のネットワーク
- 未使用のイメージ（すべて）
- 未使用のボリューム（すべて）

Docker のあらゆる不要データを削除しますが、他プロジェクトのデータも含めて完全に消去されます。
慎重に利用してください。

    make disintegrate

---
## 注意事項

- `src/` のLaravelアプリケーションはGit管理対象です。
- MySQLのデータはnamed volume（`mysql_data`）に保存されます。
  `make down`では残り、`make clean`や`make disintegrate`では削除されます。
- `storage/` はログなどの作業領域です。既定ではデータベースファイルは置きません。
- ホストの`storage/db`に永続化（bind mount）する場合は、次を変更します。
  - `docker-compose.yml`: `./storage/db:/var/lib/mysql` のコメントを外し、`mysql_data:/var/lib/mysql` をコメントアウトする。末尾の `volumes: mysql_data` も使わない。
  - `scripts/setup.sh`: `mkdir -p storage/db` のコメントを外す。
  - `.gitignore` の `/storage/db/` 向けルールはそのままでよい（中身はコミットしない）。
