<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_in_user_can_delete_incomplete_todo(): void
    {
        $user = User::factory()->create();
        $todo = $this->createTodo($user);

        $response = $this->actingAs($user)->delete(route('todos.destroy', [
            'todoId' => $todo->id,
        ]));

        $response->assertRedirect(route('top'));
        $response->assertSessionHas('success', 'ToDoを削除しました。');
        $this->assertDatabaseMissing('todos', ['id' => $todo->id]);
    }

    public function test_logged_in_user_can_delete_completed_todo(): void
    {
        $user = User::factory()->create();
        $todo = $this->createTodo($user);
        $todo->completed_at = now();
        $todo->save();

        $response = $this->actingAs($user)->delete(route('todos.destroy', [
            'todoId' => $todo->id,
        ]));

        $response->assertRedirect(route('top'));
        $this->assertDatabaseMissing('todos', ['id' => $todo->id]);
    }

    public function test_user_cannot_delete_other_users_todo(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $todo = $this->createTodo($otherUser);

        $response = $this->actingAs($user)->delete(route('todos.destroy', [
            'todoId' => $todo->id,
        ]));

        $response->assertNotFound();
        $this->assertDatabaseHas('todos', ['id' => $todo->id]);
    }

    public function test_detail_page_has_delete_confirmation(): void
    {
        $user = User::factory()->create();
        $todo = $this->createTodo($user);

        $response = $this->actingAs($user)->get(route('todos.show', [
            'todoId' => $todo->id,
        ]));

        $response->assertOk();
        $response->assertSee("confirm('このToDoを削除しますか？')", false);
        $response->assertSee('name="_method" value="DELETE"', false);
    }

    public function test_guest_cannot_delete_todo(): void
    {
        $user = User::factory()->create();
        $todo = $this->createTodo($user);

        $response = $this->delete(route('todos.destroy', [
            'todoId' => $todo->id,
        ]));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('todos', ['id' => $todo->id]);
    }

    private function createTodo(User $user): Todo
    {
        $todo = new Todo;
        $todo->user_id = $user->id;
        $todo->title = '削除するToDo';
        $todo->save();

        return $todo;
    }
}
