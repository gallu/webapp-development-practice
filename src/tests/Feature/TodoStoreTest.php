<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_in_user_can_store_todo(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)->post('/todos', [
            'title' => 'Laravelの復習',
            'body' => '認証とバリデーションを確認する。',
            'due_date' => today()->addDay()->format('Y-m-d'),

            // フォームが書き換えられても、この値は保存処理で使用されません。
            'user_id' => $otherUser->id,
        ]);

        $response->assertRedirect(route('top'));
        $response->assertSessionHas('success', 'ToDoを追加しました。');

        // ToDoがログインユーザーに紐付き、未完了で登録されることを確認します。
        $this->assertDatabaseHas('todos', [
            'user_id' => $user->id,
            'title' => 'Laravelの復習',
            'body' => '認証とバリデーションを確認する。',
            'due_date' => today()->addDay()->format('Y-m-d'),
            'completed_at' => null,
        ]);
    }

    public function test_todo_is_not_stored_when_input_is_invalid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/top')->post('/todos', [
            'title' => str_repeat('a', 256),
            'body' => str_repeat('a', 10001),
            'due_date' => today()->subDay()->format('Y-m-d'),
        ]);

        $response->assertRedirect('/top');
        $response->assertSessionHasErrors(['title', 'body', 'due_date']);
        $this->assertDatabaseCount('todos', 0);
    }

    public function test_title_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/todos', [
            'title' => '',
            'body' => '',
            'due_date' => '',
        ]);

        $response->assertSessionHasErrors('title');
        $this->assertDatabaseCount('todos', 0);
    }

    public function test_guest_cannot_store_todo(): void
    {
        $response = $this->post('/todos', [
            'title' => '登録できないToDo',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('todos', 0);
    }
}
