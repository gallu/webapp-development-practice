<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTodoRequest extends FormRequest
{
    /**
     * ToDoを更新できるかを判定します。
     */
    public function authorize(): bool
    {
        // ログインとToDoの所有者確認はルートとControllerで行います。
        return true;
    }

    /**
     * ToDo更新時の入力ルールを返します。
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'body' => ['nullable', 'string', 'max:10000'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * 画面に表示するバリデーションメッセージを返します。
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください。',
            'title.string' => 'タイトルは文字列で入力してください。',
            'title.min' => 'タイトルは1文字以上で入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'body.string' => '本文は文字列で入力してください。',
            'body.max' => '本文は10000文字以内で入力してください。',
            'due_date.date' => '期限は正しい日付で入力してください。',
            'due_date.after_or_equal' => '期限は今日以降の日付を入力してください。',
        ];
    }
}
