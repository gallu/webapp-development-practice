<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToDo編集</title>
</head>
<body>
    <main>
        <h1>ToDo編集</h1>

        <form method="POST" action="{{ route('todos.update', ['todoId' => $todo->id]) }}">
            @csrf
            @method('PATCH')

            <div>
                <label for="title">タイトル</label>
                <input id="title" name="title" type="text" value="{{ old('title', $todo->title) }}" required>
                @error('title')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="body">本文</label>
                <textarea id="body" name="body">{{ old('body', $todo->body) }}</textarea>
                @error('body')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="due_date">期限</label>
                <input
                    id="due_date"
                    name="due_date"
                    type="date"
                    value="{{ old('due_date', $todo->due_date?->format('Y-m-d')) }}"
                >
                @error('due_date')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <button type="submit">更新</button>
        </form>

        <p>
            <a href="{{ route('todos.show', ['todoId' => $todo->id]) }}">詳細へ戻る</a>
        </p>
    </main>
</body>
</html>
