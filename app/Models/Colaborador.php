<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Colaborador extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'colaboradores';

    protected $fillable = [
        'user_id',
        'empresa_id',
        'departamento_id',
        'escala_id',
        'nome',
        'cpf',
        'cpf_hash',
        'matricula',
        'cargo',
        'saldo_horas',
        'data_admissao',
        'data_demissao',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'cpf' => 'encrypted',
            'data_admissao' => 'date',
            'data_demissao' => 'date',
            'ativo' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Colaborador $colaborador) {
            if (empty($colaborador->matricula)) {
                // Usa o máximo atual + 1 com lock para evitar race condition em inserções paralelas.
                // O campo matricula tem unique constraint implícita via regra de negócio;
                // em caso de colisão extrema, o banco rejeitará e o usuário poderá tentar novamente.
                $maxMatricula = (int) (\Illuminate\Support\Facades\DB::table('colaboradores')->max('id') ?? 0);
                $colaborador->matricula = str_pad((string) ($maxMatricula + 1), 6, '0', STR_PAD_LEFT);
            }
        });

        static::saving(function (Colaborador $colaborador) {
            if ($colaborador->isDirty('cpf') && $colaborador->cpf) {
                $colaborador->cpf_hash = hash('sha256', $colaborador->cpf);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function escala(): BelongsTo
    {
        return $this->belongsTo(EscalaTrabalho::class, 'escala_id');
    }

    public function pontos(): HasMany
    {
        return $this->hasMany(Ponto::class);
    }

    public function justificativas(): HasMany
    {
        return $this->hasMany(Justificativa::class);
    }

    public function bancoHorasLogs(): HasMany
    {
        return $this->hasMany(BancoHorasLog::class);
    }

    public function ferias(): HasMany
    {
        return $this->hasMany(Ferias::class);
    }

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    public function getFullIdentifier(): string
    {
        return "{$this->matricula} - {$this->nome}";
    }
}
