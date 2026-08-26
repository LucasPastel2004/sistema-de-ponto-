<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BancoHorasLog extends Model
{
    protected $fillable = [
        'colaborador_id',
        'data_referencia',
        'minutos_ajuste',
        'observacao',
    ];

    protected function casts(): array
    {
        return [
            'data_referencia' => 'date',
            'minutos_ajuste' => 'integer',
        ];
    }

    public function colaborador()
    {
        return $this->belongsTo(Colaborador::class);
    }
}
