<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>完了済みToDo一覧</title>
</head>
<body>
    <main>
        <h1>完了済みToDo一覧</h1>

        <table>
            <thead>
                <tr>
                    <th>タイトル</th>
                    <th>期限</th>
                    <th>完了日時</th>
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
                        <td>{{ $todo->completed_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">完了済みのToDoはありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <p><a href="{{ route('top') }}">未完了一覧へ戻る</a></p>
    </main>
</body>
</html>
