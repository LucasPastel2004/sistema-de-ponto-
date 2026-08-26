<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ferias extends Model
{
    use HasFactory;

    protected $table = 'ferias';

    protected $fillable = [
        'colaborador_id',
        'empresa_id',
        'data_inicio',
        'data_fim',
        'tipo',
        'dias_vendidos',
        'observacao',
        'aprovado_por',
        'aprovado_em',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_fim' => 'date',
            'aprovado_em' => 'datetime',
            'dias_vendidos' => 'integer',
        ];
    }

    // ─── Relações ───

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function aprovadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    // ─── Scopes ───

    /** Férias que estão vigentes no momento */
    public function scopeVigentes(Builder $query): Builder
    {
        $hoje = now()->toDateString();
        return $query->where('data_inicio', '<=', $hoje)->where('data_fim', '>=', $hoje);
    }

    /** Férias de um colaborador específico */
    public function scopeDoColaborador(Builder $query, int $colaboradorId): Builder
    {
        return $query->where('colaborador_id', $colaboradorId);
    }

    /** Férias coletivas de uma empresa */
    public function scopeDaEmpresa(Builder $query, int $empresaId): Builder
    {
        return $query->where('empresa_id', $empresaId)->whereNull('colaborador_id');
    }

    /** Férias do ano */
    public function scopeDoAno(Builder $query, int $ano): Builder
    {
        return $query->whereYear('data_inicio', $ano);
    }

    // ─── Helpers ───

    /** Verifica se uma data específica cai dentro deste período de férias */
    public function abrangeDia(Carbon $data): bool
    {
        return $data->between($this->data_inicio, $this->data_fim);
    }

    /** Retorna a duração total em dias (data_fim - data_inicio + 1) */
    public function getDuracaoDiasAttribute(): int
    {
        return (int) $this->data_inicio->diffInDays($this->data_fim) + 1;
    }

    /** Retorna os dias efetivos (descontando os vendidos) */
    public function getDiasEfetivosAttribute(): int
    {
        return $this->duracao_dias - $this->dias_vendidos;
    }

    /** Verifica se é férias coletiva (sem colaborador, com empresa) */
    public function getIsColetivaAttribute(): bool
    {
        return is_null($this->colaborador_id) && !is_null($this->empresa_id);
    }

    /**
     * Verifica se um colaborador está de férias em uma data específica.
     * Considera tanto férias individuais quanto coletivas da empresa.
     */
    public static function colaboradorEmFerias(int $colaboradorId, Carbon $data, ?int $empresaId = null): bool
    {
        $dataStr = $data->toDateString();

        // Férias individuais
        $individual = static::where('colaborador_id', $colaboradorId)
            ->where('data_inicio', '<=', $dataStr)
            ->where('data_fim', '>=', $dataStr)
            ->exists();

        if ($individual) {
            return true;
        }

        // Férias coletivas da empresa
        if ($empresaId) {
            return static::whereNull('colaborador_id')
                ->where('empresa_id', $empresaId)
                ->where('data_inicio', '<=', $dataStr)
                ->where('data_fim', '>=', $dataStr)
                ->exists();
        }

        return false;
    }
}
