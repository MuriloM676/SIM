<?php

namespace App\Http\Requests\Multa;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMultaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->temPermissao('multas.editar');
    }

    public function rules(): array
    {
        return [
            'veiculo_id' => ['sometimes', 'exists:veiculos,id'],
            'placa' => ['sometimes', 'string', 'size:7', 'regex:/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/'],
            'data_infracao' => ['sometimes', 'date', 'before_or_equal:today'],
            'hora_infracao' => ['sometimes', 'date_format:H:i'],
            'local_infracao' => ['sometimes', 'string', 'max:255'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
            'velocidade_medida' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'velocidade_maxima' => ['nullable', 'numeric', 'min:0', 'max:200'],
        ];
    }
}
