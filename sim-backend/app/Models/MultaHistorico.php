<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MultaHistorico extends Model
{
    use HasFactory;

    protected $table = 'multas_historico';

    public $timestamps = false;

    protected $fillable = [
        'multa_id',
        'usuario_id',
        'status_anterior',
        'status_novo',
        'justificativa',
        'ip',
        'data_transicao',
    ];

    protected $casts = [
        'data_transicao' => 'datetime',
    ];

    public function multa(): BelongsTo
    {
        return $this->belongsTo(Multa::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }
}
