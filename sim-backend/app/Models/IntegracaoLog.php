<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegracaoLog extends Model
{
    protected $table = 'integracoes_log';

    const UPDATED_AT = null;
    const CREATED_AT = 'data_hora';

    protected $fillable = [
        'sistema_externo',
        'operacao',
        'tipo',
        'multa_id',
        'usuario_id',
        'metodo_http',
        'endpoint',
        'status_http',
        'request_headers',
        'request_body',
        'response_headers',
        'response_body',
        'tempo_resposta_ms',
        'sucesso',
        'erro',
        'data_hora',
    ];

    protected $casts = [
        'request_headers' => 'array',
        'request_body' => 'array',
        'response_headers' => 'array',
        'response_body' => 'array',
        'sucesso' => 'boolean',
        'data_hora' => 'datetime',
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
