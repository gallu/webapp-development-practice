<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoCompletedListTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_page_displays_only_logged_in_users_completed_todos(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $olderTodo = $this->createTodo($user, '先に完了したToDo', now()->subHour());
        $newerTodo = $this->createTodo($user, '後に完了したToDo', now());
        $this->createTodo($user, '未完了のToDo');
        $this->createTodo($otherUser, '他のユーザーの完了済みToDo', now());

        $response = $this->actingAs($user)->get(route('todos.completed'));

        $response->assertOk();
        $response->assertViewIs('todos.completed');
        $response->assertSeeInOrder([
            $newerTodo->title,
            $olderTodo->title,
        ]);
        $response->assertDontSee('未完了のToDo');
        $response->assertDontSee('他のユーザーの完了済みToDo');
    }

    public function test_top_page_displays_only_incomplete_todos_and_completed_list_link(): void
    {
        $user = User::factory()->create();

        $this->createTodo($user, '未完了のToDo');
        $this->createTodo($user, '完了済みのToDo', now());

        $response = $this->actingAs($user)->get('/top');

        $response->assertOk();
        $response->assertSee('未完了のToDo');
        $response->assertDontSee('完了済みのToDo');
        $response->assertSee(route('todos.completed'), false);
    }

    public function test_guest_is_redirected_to_login_page(): void
    {
        $response = $this->get(route('todos.completed'));

        $response->assertRedirect(route('login'));
    }

    private function createTodo(User $user, string $title, mixed $completedAt = null): Todo
    {
        $todo = new Todo;
        $todo->user_id = $user->id;
        $todo->title = $title;
        $todo->completed_at = $completedAt;
        $todo->save();

        return $todo;
    }
}
