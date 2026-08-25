<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJustificativaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * A autorização real é verificada pelas Policies no controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * As rotas de aprovar/rejeitar apenas aceitam observação opcional.
     * O status é determinado pela rota chamada, não pelo payload.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'observacao_aprovador' => ['nullable', 'string', 'max:500'],
        ];
    }
}
