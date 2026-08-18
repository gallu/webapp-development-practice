# Web Application Development Practice

開発用のシンプルな Docker ベース環境です。  
PHP（php-fpm）・nginx・MySQL を含む基本構成を提供し、  
`src/` に配置した Laravel 製のToDoアプリケーションを実行できます。

ToDoアプリケーションのインストールとブラウザからのアクセスは、[`src/README.md`](src/README.md) を参照してください。

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

## 起動

先にリポジトリ直下で `.env.sample` を `.env` にコピーし、
`COMPOSE_PROJECT_NAME` と `WEB_PORT` を環境に合わせて変更してください。
`.env.sample` の `studentXX` と `80XX` はプレースホルダなので、指定された値に置き換えます。
同じVPSを利用する他の学生とリソースが混在しないように、`COMPOSE_PROJECT_NAME` と
`WEB_PORT` は学生ごとに異なる値を指定してください。

```
cp .env.sample .env
# .env を修正
make up
```

## 停止

```
make down
```

---

## MySQL

```
docker compose exec mysql bash
mysql -u root -p
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
- 不要になったorphanコンテナ

MySQLのnamed volume（`mysql_data`）は削除しません。
他の学生と異なる`COMPOSE_PROJECT_NAME`を設定している限り、他プロジェクトには影響しません。

    make clean

### disintegrate
この docker-compose プロジェクトを、MySQLのデータを含めて初期状態に戻します。
以下が削除対象です：
- コンテナ
- ネットワーク
- このプロジェクト内でビルドされたイメージ
- 不要になったorphanコンテナ
- このプロジェクトのnamed volume（MySQLの`mysql_data`を含む）

MySQLのデータは復元できないため、初期状態へ戻したい場合のみ使用してください。
他の学生と異なる`COMPOSE_PROJECT_NAME`を設定している限り、他プロジェクトには影響しません。

    make disintegrate

---
## 注意事項

- `src/` のLaravelアプリケーションはGit管理対象です。
- MySQLのデータはnamed volume（`mysql_data`）に保存されます。
  `make down`と`make clean`では残り、`make disintegrate`では削除されます。
- `storage/` はログなどの作業領域です。既定ではデータベースファイルは置きません。
- ホストの`storage/db`に永続化（bind mount）する場合は、次を変更します。
  - `docker-compose.yml`: `./storage/db:/var/lib/mysql` のコメントを外し、`mysql_data:/var/lib/mysql` をコメントアウトする。末尾の `volumes: mysql_data` も使わない。
  - `scripts/setup.sh`: `mkdir -p storage/db` のコメントを外す。
  - `.gitignore` の `/storage/db/` 向けルールはそのままでよい（中身はコミットしない）。
