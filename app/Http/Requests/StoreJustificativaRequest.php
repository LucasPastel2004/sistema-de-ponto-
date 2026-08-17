<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJustificativaRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'colaborador_id' => ['required', 'exists:colaboradores,id'],
            'ponto_id' => ['nullable', 'exists:pontos,id'],
            'data_referencia' => ['required', 'date', 'before_or_equal:today'],
            'tipo' => ['required', 'string', 'max:100'],
            'descricao' => ['required', 'string', 'max:500'],
            'comprovante' => ['nullable', 'file', 'mimes:pdf,jpg,png', 'max:5120'],
        ];
    }
}
