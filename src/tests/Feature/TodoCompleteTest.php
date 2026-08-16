<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoCompleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_in_user_can_complete_own_todo(): void
    {
        $user = User::factory()->create();
        $todo = $this->createTodo($user);

        $this->freezeTime(function () use ($user, $todo): void {
            $response = $this->actingAs($user)->patch(route('todos.complete', [
                'todoId' => $todo->id,
            ]));

            $response->assertRedirect(route('todos.show', ['todoId' => $todo->id]));
            $response->assertSessionHas('success', 'ToDoを完了しました。');
            $this->assertDatabaseHas('todos', [
                'id' => $todo->id,
                'completed_at' => now(),
            ]);
        });
    }

    public function test_completed_todo_is_not_saved_again(): void
    {
        $user = User::factory()->create();
        $todo = $this->createTodo($user);
        // MySQLのDATETIMEに合わせ、マイクロ秒を含まない時刻を保存します。
        $completedAt = now()->subHour()->startOfSecond();
        $todo->completed_at = $completedAt;
        $todo->save();
        $updatedAt = $todo->updated_at;

        $this->travel(1)->minutes();

        $response = $this->actingAs($user)->patch(route('todos.complete', [
            'todoId' => $todo->id,
        ]));

        $response->assertRedirect(route('todos.show', ['todoId' => $todo->id]));
        $response->assertSessionHas('error', 'このToDoはすでに完了しています。');

        // save()が呼ばれていなければ、完了日時と更新日時は変わりません。
        $todo->refresh();
        $this->assertTrue($todo->completed_at->equalTo($completedAt));
        $this->assertTrue($todo->updated_at->equalTo($updatedAt));
    }

    public function test_user_cannot_complete_other_users_todo(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $todo = $this->createTodo($otherUser);

        $response = $this->actingAs($user)->patch(route('todos.complete', [
            'todoId' => $todo->id,
        ]));

        $response->assertNotFound();
        $this->assertNull($todo->fresh()->completed_at);
    }

    private function createTodo(User $user): Todo
    {
        $todo = new Todo;
        $todo->user_id = $user->id;
        $todo->title = '完了するToDo';
        $todo->save();

        return $todo;
    }
}
