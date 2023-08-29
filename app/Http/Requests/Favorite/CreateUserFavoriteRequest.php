<?php

namespace App\Http\Requests\Favorite;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserFavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');
        return $this->user()->id !== $user->id;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user' => $this->route('user'),
        ]);
    }

    public function rules(): array
    {
        return  [];
    }
}
