<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TopPage</title>
</head>
<body>
    <main>
        <h1>TopPage</h1>

        @if (session('success'))
            <p>{{ session('success') }}</p>
        @endif

        <section>
            <h2>ToDoの追加</h2>

            <form method="POST" action="{{ route('todos.store') }}">
                @csrf

                <div>
                    <label for="title">タイトル</label>
                    <input id="title" name="title" type="text" value="{{ old('title') }}" required>
                    @error('title')
                        <p>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="body">本文</label>
                    <textarea id="body" name="body">{{ old('body') }}</textarea>
                    @error('body')
                        <p>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="due_date">期限</label>
                    <input id="due_date" name="due_date" type="date" value="{{ old('due_date') }}">
                    @error('due_date')
                        <p>{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit">追加</button>
            </form>
        </section>

        <section>
            <h2>未完了ToDoの一覧</h2>

            <p><a href="{{ route('todos.completed') }}">完了済み一覧</a></p>

            <table>
                <thead>
                    <tr>
                        <th>タイトル</th>
                        <th>期限</th>
                        <th>状態</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($todos as $todo)
                        <tr>
                            <td>
                                <a href="{{ route('todos.show', ['todoId' => $todo->id]) }}">
                                    {{ $todo->title }}
                                </a>
                            </td>
                            <td>{{ $todo->due_date?->format('Y-m-d') ?? '未設定' }}</td>
                            <td>{{ $todo->completed_at === null ? '未完了' : '完了' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">ToDoはありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">ログアウト</button>
        </form>
    </main>
</body>
</html>
