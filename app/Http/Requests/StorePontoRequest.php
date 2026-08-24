<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\MetodoValidacao;
use App\Enums\TipoPonto;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePontoRequest extends FormRequest
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
            'colaborador_id' => ['required', 'exists:colaboradores,id'],
            'tipo' => ['required', new Enum(TipoPonto::class)],
            'registrado_em' => ['required', 'date', 'before_or_equal:now'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'metodo_validacao' => ['required', new Enum(MetodoValidacao::class)],
            'observacao' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'colaborador_id.required' => 'O colaborador é obrigatório.',
            'colaborador_id.exists' => 'O colaborador informado não existe.',
            'tipo.required' => 'O tipo de ponto é obrigatório.',
            'registrado_em.required' => 'A data e hora do registro são obrigatórias.',
            'registrado_em.before_or_equal' => 'A data e hora do registro não podem estar no futuro.',
            'latitude.between' => 'A latitude deve estar entre -90 e 90 graus.',
            'longitude.between' => 'A longitude deve estar entre -180 e 180 graus.',
            'metodo_validacao.required' => 'O método de validação é obrigatório.',
            'observacao.max' => 'A observação não pode ter mais que 1000 caracteres.',
        ];
    }
}
