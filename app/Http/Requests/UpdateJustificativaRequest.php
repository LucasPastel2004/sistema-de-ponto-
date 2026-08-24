<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\StatusJustificativa;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateJustificativaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(StatusJustificativa::class)],
            'observacao_aprovador' => ['nullable', 'string', 'max:500'],
        ];
    }
}
