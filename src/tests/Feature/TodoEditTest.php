<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_in_user_can_view_edit_page_for_incomplete_todo(): void
    {
        $user = User::factory()->create();
        $todo = $this->createTodo($user);

        $response = $this->actingAs($user)->get(route('todos.edit', [
            'todoId' => $todo->id,
        ]));

        $response->assertOk();
        $response->assertViewIs('todos.edit');
        $response->assertSee($todo->title);
    }

    public function test_logged_in_user_can_update_incomplete_todo(): void
    {
        $user = User::factory()->create();
        $todo = $this->createTodo($user);

        $response = $this->actingAs($user)->patch(route('todos.update', [
            'todoId' => $todo->id,
        ]), [
            'title' => '更新後のタイトル',
            'body' => '更新後の本文',
            'due_date' => today()->addDay()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('todos.show', ['todoId' => $todo->id]));
        $response->assertSessionHas('success', 'ToDoを更新しました。');
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'title' => '更新後のタイトル',
            'body' => '更新後の本文',
            'due_date' => today()->addDay()->format('Y-m-d'),
        ]);
    }

    public function test_todo_is_not_updated_when_due_date_is_in_the_past(): void
    {
        $user = User::factory()->create();
        $todo = $this->createTodo($user);

        $response = $this->actingAs($user)->from(route('todos.edit', [
            'todoId' => $todo->id,
        ]))->patch(route('todos.update', [
            'todoId' => $todo->id,
        ]), [
            'title' => '更新しないタイトル',
            'due_date' => today()->subDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('due_date');
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'title' => $todo->title,
        ]);
    }

    public function test_completed_todo_cannot_be_edited_or_updated(): void
    {
        $user = User::factory()->create();
        $todo = $this->createTodo($user);
        $todo->completed_at = now();
        $todo->save();

        $editResponse = $this->actingAs($user)->get(route('todos.edit', [
            'todoId' => $todo->id,
        ]));

        $editResponse->assertRedirect(route('todos.show', ['todoId' => $todo->id]));
        $editResponse->assertSessionHas('error', '完了済みのToDoは編集できません。');

        $updateResponse = $this->actingAs($user)->patch(route('todos.update', [
            'todoId' => $todo->id,
        ]), [
            'title' => '更新できないタイトル',
            'body' => null,
            'due_date' => null,
        ]);

        $updateResponse->assertRedirect(route('todos.show', ['todoId' => $todo->id]));
        $updateResponse->assertSessionHas('error', '完了済みのToDoは編集できません。');
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'title' => $todo->title,
        ]);
    }

    public function test_user_cannot_edit_other_users_todo(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $todo = $this->createTodo($otherUser);

        $response = $this->actingAs($user)->get(route('todos.edit', [
            'todoId' => $todo->id,
        ]));

        $response->assertNotFound();
    }

    private function createTodo(User $user): Todo
    {
        $todo = new Todo;
        $todo->user_id = $user->id;
        $todo->title = '編集前のタイトル';
        $todo->body = '編集前の本文';
        $todo->due_date = today()->addWeek();
        $todo->save();

        return $todo;
    }
}
