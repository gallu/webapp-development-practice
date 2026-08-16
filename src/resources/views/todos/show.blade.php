<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToDo詳細</title>
</head>
<body>
    <main>
        <h1>ToDo詳細</h1>

        @if (session('success'))
            <p>{{ session('success') }}</p>
        @endif

        @if (session('error'))
            <p>{{ session('error') }}</p>
        @endif

        <section>
            <h2>タイトル</h2>
            <p>{{ $todo->title }}</p>
        </section>

        <section>
            <h2>本文</h2>

            {{-- pre-wrapを指定し、本文に入力された改行を画面にも反映します。 --}}
            <div style="white-space: pre-wrap;">{{ $todo->body ?? '未入力' }}</div>
        </section>

        <section>
            <h2>期限</h2>
            <p>{{ $todo->due_date?->format('Y-m-d') ?? '未設定' }}</p>
        </section>

        @if ($todo->completed_at === null)
            <form method="GET" action="{{ route('todos.edit', ['todoId' => $todo->id]) }}">
                <button type="submit">編集</button>
            </form>

            <form method="POST" action="{{ route('todos.complete', ['todoId' => $todo->id]) }}">
                @csrf
                @method('PATCH')
                <button type="submit">完了</button>
            </form>
        @else
            <button type="button" disabled>編集</button>
            <button type="button" disabled>完了</button>
        @endif

        {{-- submit前にconfirm()を実行し、キャンセルされた場合は削除リクエストを送りません。 --}}
        <form
            method="POST"
            action="{{ route('todos.destroy', ['todoId' => $todo->id]) }}"
            onsubmit="return confirm('このToDoを削除しますか？');"
        >
            @csrf
            @method('DELETE')
            <button type="submit">削除</button>
        </form>

        <p><a href="{{ route('top') }}">一覧へ戻る</a></p>
    </main>
</body>
</html>
