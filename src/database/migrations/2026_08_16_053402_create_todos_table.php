<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('todos', function (Blueprint $table) {
            $table->id();

            // ToDoを作成したユーザーと関連付けます。
            $table->foreignId('user_id')->constrained();

            $table->string('title');

            // 本文と期限は入力しなくても登録できるようにNULLを許可します。
            $table->text('body')->nullable();
            $table->date('due_date')->nullable();

            // 未完了の場合はNULL、完了した場合は完了日時を保存します。
            $table->dateTime('completed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};
