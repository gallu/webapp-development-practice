<?php

namespace Tests\Unit;

use App\Http\Controllers\TodoController;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class TodoControllerTest extends TestCase
{
    // テストごとにDBを作り直し、他のテストデータの影響を受けないようにします。
    use RefreshDatabase;

    public function test_get_todos_returns_only_specified_users_todos(): void
    {
        // ToDoの所有者を区別するため、2人のユーザーを用意します。
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $now = now();

        // insert()に配列を複数渡すと、1回のSQLで複数のToDoを登録できます。
        // insert()ではcreated_atとupdated_atが自動入力されないため、ここで指定します。
        Todo::query()->insert([
            [
                'user_id' => $user->id,
                'title' => '期限未設定のToDo',
                'due_date' => null,
                'completed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $user->id,
                'title' => '同一期限でIDが小さいToDo',
                'due_date' => '2026-08-18',
                'completed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $user->id,
                'title' => '同一期限でIDが大きいToDo',
                'due_date' => '2026-08-18',
                'completed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $user->id,
                'title' => '期限が早いToDo',
                'due_date' => '2026-08-17',
                'completed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $otherUser->id,
                'title' => '他のユーザーのToDo',
                'due_date' => null,
                'completed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $user->id,
                'title' => '完了済みのToDo',
                'due_date' => null,
                'completed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $controller = new TodoController;

        // getTodos()はprivateなので、通常のメソッド呼び出しはできません。
        // 今回はprivateメソッドのテストを学ぶため、Reflectionを使って呼び出します。
        $method = new ReflectionMethod(TodoController::class, 'getTodos');

        // PHP 8.1以降はprivateメソッドもinvoke()で呼べるため、
        // PHP 8.5で非推奨となったsetAccessible()は使用しません。
        /** @var Collection<int, Todo> $todos */
        $todos = $method->invoke($controller, $user->id);

        // 指定したユーザーのToDoだけが取得されることを確認します。
        $this->assertCount(4, $todos);

        // 期限未設定、期限の昇順、同一期限ではIDの昇順に並ぶことを確認します。
        $this->assertSame(
            [
                '期限未設定のToDo',
                '期限が早いToDo',
                '同一期限でIDが小さいToDo',
                '同一期限でIDが大きいToDo',
            ],
            $todos->pluck('title')->all(),
        );
    }

    public function test_get_completed_todos_returns_completed_todos_in_completed_at_order(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $now = now()->startOfSecond();

        // insert()で複数のテストデータを1回のSQLで登録します。
        Todo::query()->insert([
            [
                'user_id' => $user->id,
                'title' => '先に完了したToDo',
                'completed_at' => $now->copy()->subHour(),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $user->id,
                'title' => '後に完了したToDo',
                'completed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $user->id,
                'title' => '未完了のToDo',
                'completed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $otherUser->id,
                'title' => '他のユーザーの完了済みToDo',
                'completed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $controller = new TodoController;

        // privateな完了済み一覧取得メソッドをReflectionで呼び出します。
        $method = new ReflectionMethod(TodoController::class, 'getCompletedTodos');

        /** @var Collection<int, Todo> $todos */
        $todos = $method->invoke($controller, $user->id);

        $this->assertSame(
            ['後に完了したToDo', '先に完了したToDo'],
            $todos->pluck('title')->all(),
        );
    }
}
