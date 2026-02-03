<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Veiculo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'veiculos';

    protected $fillable = [
        'municipio_id',
        'placa',
        'renavam',
        'chassi',
        'marca',
        'modelo',
        'cor',
        'ano_fabricacao',
        'ano_modelo',
        'categoria',
        'proprietario_nome',
        'proprietario_cpf_cnpj',
    ];

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function multas(): HasMany
    {
        return $this->hasMany(Multa::class);
    }

    /**
     * Normaliza placa antes de salvar
     */
    public function setPlacaAttribute($value)
    {
        $this->attributes['placa'] = strtoupper($value);
    }
}
