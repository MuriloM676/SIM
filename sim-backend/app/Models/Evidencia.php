<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evidencia extends Model
{
    use HasFactory;

    protected $table = 'evidencias';

    const UPDATED_AT = null;
    const CREATED_AT = 'data_upload';

    protected $fillable = [
        'multa_id',
        'usuario_upload_id',
        'tipo',
        'nome_original',
        'nome_armazenado',
        'mime_type',
        'tamanho_bytes',
        'hash_sha256',
        'descricao',
        'caminho_storage',
        'data_upload',
    ];

    protected $casts = [
        'data_upload' => 'datetime',
    ];

    public function multa(): BelongsTo
    {
        return $this->belongsTo(Multa::class);
    }

    public function usuarioUpload(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_upload_id');
    }

    /**
     * Retorna tamanho formatado
     */
    public function tamanhoFormatado(): string
    {
        $bytes = $this->tamanho_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
