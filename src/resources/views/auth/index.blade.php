<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
</head>
<body>
    <main>
        <h1>ログイン</h1>

        <form method="POST" action="{{ route('login.authenticate') }}">
            @csrf

            @error('email')
                <p>{{ $message }}</p>
            @enderror

            <div>
                <label for="email">メールアドレス</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                >
            </div>

            <div>
                <label for="password">パスワード</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                >
                @error('password')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <button type="submit">ログイン</button>
        </form>
    </main>
</body>
</html>
