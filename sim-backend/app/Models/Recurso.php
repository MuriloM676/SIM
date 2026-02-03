<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recurso extends Model
{
    use HasFactory;

    protected $table = 'recursos';

    protected $fillable = [
        'numero_protocolo',
        'multa_id',
        'usuario_criador_id',
        'tipo',
        'fundamentacao',
        'status',
        'parecer_tecnico',
        'decisao',
        'justificativa_decisao',
        'usuario_julgador_id',
        'data_julgamento',
        'data_protocolo',
    ];

    protected $casts = [
        'data_julgamento' => 'datetime',
        'data_protocolo' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($recurso) {
            if (empty($recurso->numero_protocolo)) {
                $recurso->numero_protocolo = self::gerarProtocolo();
            }
        });
    }

    public function multa(): BelongsTo
    {
        return $this->belongsTo(Multa::class);
    }

    public function usuarioCriador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_criador_id');
    }

    public function usuarioJulgador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_julgador_id');
    }

    private static function gerarProtocolo(): string
    {
        $ano = date('Y');
        $sequencial = str_pad(self::whereYear('created_at', $ano)->count() + 1, 8, '0', STR_PAD_LEFT);
        return "REC{$ano}{$sequencial}";
    }
}
