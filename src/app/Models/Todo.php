<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

// フォームから一括で代入してよいカラムだけを指定します。
#[Fillable(['title', 'body', 'due_date'])]
class Todo extends Model
{
    /**
     * 日付のカラムをLaravelの日付オブジェクトとして扱います。
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }
}
