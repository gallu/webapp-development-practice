<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Models\Todo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TodoController extends Controller
{
    /**
     * ログインユーザーのToDo一覧を表示します。
     */
    public function index(Request $request): View
    {
        $todos = $this->getTodos($request->user()->id);

        return view('top', compact('todos'));
    }

    /**
     * ログインユーザーの完了済みToDo一覧を表示します。
     */
    public function completed(Request $request): View
    {
        $todos = $this->getCompletedTodos($request->user()->id);

        return view('todos.completed', compact('todos'));
    }

    /**
     * 入力された内容でToDoを追加します。
     */
    public function store(StoreTodoRequest $request): RedirectResponse
    {
        $todo = new Todo;

        // validated()を使うと、Form Requestで確認済みの値だけを取得できます。
        $todo->fill($request->validated());

        // user_idはフォームから受け取らず、ログインユーザーのIDを設定します。
        // これにより、別のユーザーのToDoとして登録されることを防ぎます。
        $todo->user_id = $request->user()->id;
        $todo->save();

        return redirect()
            ->route('top')
            ->with('success', 'ToDoを追加しました。');
    }

    /**
     * 指定したToDoの詳細を表示します。
     */
    public function show(Request $request, int $todoId): View
    {
        // URLのIDだけで検索すると、他のユーザーのToDoも取得できてしまいます。
        // ログインユーザーのIDでも絞り込み、自分のToDoだけを表示できるようにします。
        $todo = Todo::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($todoId);

        return view('todos.show', compact('todo'));
    }

    /**
     * ToDoの編集画面を表示します。
     */
    public function edit(Request $request, int $todoId): View|RedirectResponse
    {
        $todo = Todo::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($todoId);

        // 完了済みのToDoは編集画面を表示せず、詳細画面へ戻します。
        if ($todo->completed_at !== null) {
            return redirect()
                ->route('todos.show', ['todoId' => $todo->id])
                ->with('error', '完了済みのToDoは編集できません。');
        }

        return view('todos.edit', compact('todo'));
    }

    /**
     * 編集された内容でToDoを更新します。
     */
    public function update(UpdateTodoRequest $request, int $todoId): RedirectResponse
    {
        $todo = Todo::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($todoId);

        // URLを直接操作された場合も、完了済みのToDoは更新しません。
        if ($todo->completed_at !== null) {
            return redirect()
                ->route('todos.show', ['todoId' => $todo->id])
                ->with('error', '完了済みのToDoは編集できません。');
        }

        $todo->fill($request->validated());
        $todo->save();

        return redirect()
            ->route('todos.show', ['todoId' => $todo->id])
            ->with('success', 'ToDoを更新しました。');
    }

    /**
     * ToDoを完了状態にします。
     */
    public function complete(Request $request, int $todoId): RedirectResponse
    {
        $todo = Todo::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($todoId);

        // 完了日時を上書きしないため、完了済みの場合はsave()を呼びません。
        if ($todo->completed_at !== null) {
            return redirect()
                ->route('todos.show', ['todoId' => $todo->id])
                ->with('error', 'このToDoはすでに完了しています。');
        }

        $todo->completed_at = now();
        $todo->save();

        return redirect()
            ->route('todos.show', ['todoId' => $todo->id])
            ->with('success', 'ToDoを完了しました。');
    }

    /**
     * 指定したToDoを削除します。
     */
    public function destroy(Request $request, int $todoId): RedirectResponse
    {
        // ログインユーザーのToDoだけを削除対象として取得します。
        // 他のユーザーのToDoを指定した場合は404を返します。
        $todo = Todo::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($todoId);

        $todo->delete();

        return redirect()
            ->route('top')
            ->with('success', 'ToDoを削除しました。');
    }

    /**
     * 指定したユーザーのToDo一覧を取得します。
     *
     * 後の授業で、このデータ取得処理をRepositoryへ移動する予定です。
     * そのため、現時点ではController内のprivateメソッドに分けています。
     *
     * @return Collection<int, Todo>
     */
    private function getTodos(int $userId): Collection
    {
        // 他のユーザーのToDoを取得しないよう、ユーザーIDで絞り込みます。
        // TopPageには未完了のToDoだけを表示します。
        // MySQLでは日付の昇順にすると、期限がNULLのToDoが先頭に並びます。
        // 期限が同じ場合は、並び順が一定になるようIDの昇順で取得します。
        return Todo::query()
            ->where('user_id', $userId)
            ->whereNull('completed_at')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();
    }

    /**
     * 指定したユーザーの完了済みToDo一覧を取得します。
     *
     * @return Collection<int, Todo>
     */
    private function getCompletedTodos(int $userId): Collection
    {
        // 完了済みのToDoだけを、最近完了した順に取得します。
        // 完了日時が同じ場合は、並び順が一定になるようIDの昇順にします。
        return Todo::query()
            ->where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->orderBy('id')
            ->get();
    }
}
