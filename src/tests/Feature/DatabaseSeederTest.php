<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_todo_user(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::query()->where('email', 'todo@example.com')->firstOrFail();

        $this->assertSame('Todo User', $user->name);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertDatabaseCount('users', 1);
    }
}
