<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'agentes';

    protected $fillable = [
        'municipio_id',
        'matricula',
        'nome',
        'cpf',
        'cargo',
        'lotacao',
        'ativo',
        'data_admissao',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'data_admissao' => 'date',
    ];

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}
