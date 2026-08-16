<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoListTest extends TestCase
{
    use RefreshDatabase;

    public function test_top_page_displays_todo_form_and_empty_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/top');

        $response->assertOk();
        $response->assertSee('ToDoの追加');
        $response->assertSee('ToDoの一覧');
        $response->assertSee('ToDoはありません。');
        $response->assertSee('name="title"', false);
        $response->assertSee('name="body"', false);
        $response->assertSee('name="due_date"', false);
    }

    public function test_top_page_displays_only_logged_in_users_todos(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->createTodo($user, '自分のToDo');
        $this->createTodo($otherUser, '他のユーザーのToDo');

        $response = $this->actingAs($user)->get('/top');

        $response->assertOk();
        $response->assertSee('自分のToDo');
        $response->assertDontSee('他のユーザーのToDo');
    }

    /**
     * 一覧表示のテストに必要なToDoを作成します。
     */
    private function createTodo(User $user, string $title): void
    {
        $todo = new Todo;
        $todo->user_id = $user->id;
        $todo->title = $title;
        $todo->save();
    }
}
