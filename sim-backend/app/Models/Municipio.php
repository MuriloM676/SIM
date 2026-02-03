<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Municipio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'municipios';

    protected $fillable = [
        'codigo_ibge',
        'nome',
        'uf',
        'cnpj',
        'ativo',
        'configuracoes',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'configuracoes' => 'array',
    ];

    /**
     * Usuários do município
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class);
    }

    /**
     * Multas do município
     */
    public function multas(): HasMany
    {
        return $this->hasMany(Multa::class);
    }

    /**
     * Veículos cadastrados
     */
    public function veiculos(): HasMany
    {
        return $this->hasMany(Veiculo::class);
    }

    /**
     * Agentes do município
     */
    public function agentes(): HasMany
    {
        return $this->hasMany(Agente::class);
    }

    /**
     * Scope para buscar apenas ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para buscar por UF
     */
    public function scopePorUf($query, string $uf)
    {
        return $query->where('uf', strtoupper($uf));
    }
}
