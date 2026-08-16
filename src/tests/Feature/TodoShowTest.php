<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_in_user_can_view_own_todo(): void
    {
        $user = User::factory()->create();
        $todo = $this->createTodo($user, [
            'title' => '詳細を確認するToDo',
            'body' => "1行目\n2行目",
            'due_date' => '2026-08-17',
        ]);

        $response = $this->actingAs($user)->get(route('todos.show', [
            'todoId' => $todo->id,
        ]));

        $response->assertOk();
        $response->assertViewIs('todos.show');
        $response->assertSee('詳細を確認するToDo');
        $response->assertSee("1行目\n2行目");
        $response->assertSee('white-space: pre-wrap', false);
        $response->assertSee('2026-08-17');
        $response->assertSee(route('todos.edit', ['todoId' => $todo->id]), false);
        $response->assertSee(route('todos.complete', ['todoId' => $todo->id]), false);
    }

    public function test_user_cannot_view_other_users_todo(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $todo = $this->createTodo($otherUser, [
            'title' => '他のユーザーのToDo',
        ]);

        $response = $this->actingAs($user)->get(route('todos.show', [
            'todoId' => $todo->id,
        ]));

        $response->assertNotFound();
    }

    public function test_todo_title_on_top_page_links_to_detail_page(): void
    {
        $user = User::factory()->create();
        $todo = $this->createTodo($user, [
            'title' => 'リンクを確認するToDo',
        ]);

        $response = $this->actingAs($user)->get('/top');

        $response->assertOk();
        $response->assertSee(route('todos.show', [
            'todoId' => $todo->id,
        ]), false);
    }

    public function test_guest_is_redirected_to_login_page(): void
    {
        $user = User::factory()->create();
        $todo = $this->createTodo($user, [
            'title' => 'ログインが必要なToDo',
        ]);

        $response = $this->get(route('todos.show', [
            'todoId' => $todo->id,
        ]));

        $response->assertRedirect(route('login'));
    }

    /**
     * 詳細表示のテストに必要なToDoを作成します。
     *
     * @param  array<string, mixed>  $attributes
     */
    private function createTodo(User $user, array $attributes): Todo
    {
        $todo = new Todo;
        $todo->user_id = $user->id;
        $todo->fill($attributes);
        $todo->save();

        return $todo;
    }
}
