<?php

namespace App\Models;

use App\Enums\MultaStatus;
use App\Observers\MultaObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([MultaObserver::class])]
class Multa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'multas';

    protected $fillable = [
        'auto_infracao',
        'municipio_id',
        'infracao_id',
        'veiculo_id',
        'agente_id',
        'usuario_criador_id',
        'placa',
        'data_infracao',
        'hora_infracao',
        'local_infracao',
        'latitude',
        'longitude',
        'velocidade_medida',
        'velocidade_maxima',
        'observacoes',
        'status',
        'valor_multa',
        'pontos_cnh',
        'numero_detran',
        'data_envio_detran',
        'data_notificacao',
    ];

    protected $casts = [
        'status' => MultaStatus::class,
        'data_infracao' => 'date',
        'hora_infracao' => 'datetime',
        'valor_multa' => 'decimal:2',
        'velocidade_medida' => 'decimal:2',
        'velocidade_maxima' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'data_envio_detran' => 'datetime',
        'data_notificacao' => 'datetime',
    ];

    /**
     * Boot do model
     */
    protected static function boot()
    {
        parent::boot();

        // Gera auto de infração automático
        static::creating(function ($multa) {
            if (empty($multa->auto_infracao)) {
                $multa->auto_infracao = self::gerarAutoInfracao($multa->municipio_id);
            }

            // Copia valores da infração
            if ($multa->infracao) {
                $multa->valor_multa = $multa->infracao->valor;
                $multa->pontos_cnh = $multa->infracao->pontos;
            }
        });
    }

    /**
     * Relacionamentos
     */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function infracao(): BelongsTo
    {
        return $this->belongsTo(Infracao::class);
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function agente(): BelongsTo
    {
        return $this->belongsTo(Agente::class);
    }

    public function usuarioCriador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_criador_id');
    }

    public function historico(): HasMany
    {
        return $this->hasMany(MultaHistorico::class)->orderBy('data_transicao', 'desc');
    }

    public function recursos(): HasMany
    {
        return $this->hasMany(Recurso::class);
    }

    public function evidencias(): HasMany
    {
        return $this->hasMany(Evidencia::class);
    }

    /**
     * Verifica se a multa pode ser editada
     */
    public function podeSerEditada(): bool
    {
        return in_array($this->status, [
            MultaStatus::RASCUNHO,
            MultaStatus::REGISTRADA,
        ]);
    }

    /**
     * Verifica se pode transitar para novo status
     */
    public function podeTransitarPara(MultaStatus $novoStatus): bool
    {
        return $this->status->podeTransitarPara($novoStatus);
    }

    /**
     * Verifica se está em recurso
     */
    public function emRecurso(): bool
    {
        return $this->status === MultaStatus::EM_RECURSO;
    }

    /**
     * Gera número único do auto de infração
     */
    private static function gerarAutoInfracao(int $municipioId): string
    {
        $ano = date('Y');
        $municipio = str_pad($municipioId, 4, '0', STR_PAD_LEFT);
        $sequencial = str_pad(
            self::where('municipio_id', $municipioId)
                ->whereYear('created_at', $ano)
                ->count() + 1,
            6,
            '0',
            STR_PAD_LEFT
        );

        return "AI{$ano}{$municipio}{$sequencial}";
    }

    /**
     * Scopes
     */
    public function scopePorMunicipio($query, int $municipioId)
    {
        return $query->where('municipio_id', $municipioId);
    }

    public function scopePorStatus($query, MultaStatus $status)
    {
        return $query->where('status', $status->value);
    }

    public function scopePorPlaca($query, string $placa)
    {
        return $query->where('placa', strtoupper($placa));
    }

    public function scopePorPeriodo($query, string $dataInicio, string $dataFim)
    {
        return $query->whereBetween('data_infracao', [$dataInicio, $dataFim]);
    }
}
