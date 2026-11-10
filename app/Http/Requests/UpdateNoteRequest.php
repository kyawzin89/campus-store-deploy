<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Delegates to NotePolicy::update(). Returning false gives a 403.
        return $this->user()->can('update', $this->route('note'));
    }

    public function rules(): array
    {
        // "sometimes" lets the client send only the fields it wants to change.
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'body' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'is_pinned' => ['sometimes', 'boolean'],
        ];
    }
}
