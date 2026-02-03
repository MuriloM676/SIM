<?php

namespace App\Models;

use App\Enums\GravidadeInfracao;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Infracao extends Model
{
    use HasFactory;

    protected $table = 'infracoes';

    protected $fillable = [
        'codigo_ctb',
        'descricao',
        'gravidade',
        'pontos',
        'valor',
        'detalhamento',
        'medidor_velocidade',
        'ativo',
    ];

    protected $casts = [
        'gravidade' => GravidadeInfracao::class,
        'valor' => 'decimal:2',
        'medidor_velocidade' => 'boolean',
        'ativo' => 'boolean',
    ];

    /**
     * Scope para infrações ativas
     */
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para filtrar por gravidade
     */
    public function scopePorGravidade($query, GravidadeInfracao $gravidade)
    {
        return $query->where('gravidade', $gravidade->value);
    }

    /**
     * Verifica se requer medidor de velocidade
     */
    public function requerMedidorVelocidade(): bool
    {
        return $this->medidor_velocidade;
    }
}
