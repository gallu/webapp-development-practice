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
│   ├─ mysql/
│   │   └─ Dockerfile（必要に応じて配置）
│   └─ redis/
│       └─ Dockerfile（必要に応じて配置）
├─ storage/
│   ├─ db/
│   └─ logs/
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

```
docker compose up --build -d
```

## 停止

```
docker compose down
```

## PHP へのアクセス

```
http://localhost:8080/
```

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
        'root',
        'rootpassword',
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
永続化ボリュームは削除しません。

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
- このプロジェクト内で作成されたボリューム

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
- `storage/` は永続化領域です（DB・ログなど）。
