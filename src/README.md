# 授業用ToDoリスト

Laravelの基本的なWebアプリケーション開発を学ぶためのToDoリストです。
ユーザー認証、データベース操作、入力値の検証、ユーザーごとのデータ管理を扱います。

Docker環境の構成、起動方法、停止方法については、[リポジトリ直下のREADME](../README.md)を参照してください。

## インストール

コマンドはリポジトリ直下で実行します。

1. Docker Compose用の環境設定ファイルを作成します。

    ```bash
    cp .env.sample .env
    ```

2. `.env`の`COMPOSE_PROJECT_NAME`と`WEB_PORT`を環境に合わせて変更し、コンテナを起動します。

    ```bash
    make up
    ```

3. PHPの依存パッケージとLaravelの環境設定ファイルを準備します。

    ```bash
    docker compose exec php composer install
    cp src/.env.example src/.env
    ```

4. `src/.env`のデータベース設定をDocker Compose環境に合わせます。

    ```dotenv
    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=app
    DB_USERNAME=app
    DB_PASSWORD=app
    ```

5. アプリケーションキーを生成し、Laravelの書き込み用ディレクトリのパーミッションを調整します。

    ```bash
    docker compose exec php php artisan key:generate
    make permissions
    ```

6. テーブルと授業用ユーザーを作成します。

    ```bash
    docker compose exec php php artisan migrate --seed
    ```

## 機能

- ログイン・ログアウト
- 未完了ToDoの一覧表示
- 完了済みToDoの一覧表示
- ToDoの詳細表示
- ToDoの登録
- 未完了ToDoの編集
- ToDoの完了
- ToDoの削除

ToDoはログインユーザーごとに管理されます。他のユーザーのToDoは表示、編集、完了、削除できません。
また、完了済みのToDoは編集できません。

## ToDoの入力項目

| 項目 | 必須 | 入力条件 |
| --- | --- | --- |
| タイトル | 必須 | 1文字以上255文字以内 |
| 本文 | 任意 | 10,000文字以内 |
| 期限 | 任意 | 今日以降の日付 |

## ログイン情報

データベースの初期データを登録すると、次の授業用ユーザーでログインできます。

```text
メールアドレス: todo@example.com
パスワード: password
```

この認証情報は授業用の開発環境だけで使用してください。本番環境では使用しないでください。

初期データの登録方法は「インストール」を参照してください。

## アクセス方法

Docker Composeの起動後、ブラウザで次のURLを開きます。`WEB_PORT`には、
リポジトリ直下の`.env`で設定したポート番号を指定してください。

```text
http://localhost:<WEB_PORT>/
```

## テスト

テストはリポジトリ直下で実行します。

```bash
docker compose exec php composer test
```

テストでは`app_testing`データベースを使用します。未作成の場合は、テスト実行前に作成してください。

```bash
docker compose exec mysql mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS app_testing;"
```

## 主な技術構成

- PHP 8.5
- Laravel 13
- MySQL 8.0
- nginx
- Docker Compose
- PHPUnit 12

RedisコンテナとPHPのRedis拡張も開発環境に含まれていますが、現在のアプリケーションでは
セッション、キャッシュ、キューにRedisを使用していません。
