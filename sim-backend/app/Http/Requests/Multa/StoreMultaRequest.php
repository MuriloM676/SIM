<?php

namespace App\Http\Requests\Multa;

use App\Enums\MultaStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMultaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->temPermissao('multas.criar');
    }

    public function rules(): array
    {
        return [
            'municipio_id' => ['required', 'exists:municipios,id'],
            'infracao_id' => ['required', 'exists:infracoes,id'],
            'veiculo_id' => ['required', 'exists:veiculos,id'],
            'agente_id' => ['required', 'exists:agentes,id'],
            'placa' => ['required', 'string', 'size:7', 'regex:/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/'],
            'data_infracao' => ['required', 'date', 'before_or_equal:today'],
            'hora_infracao' => ['required', 'date_format:H:i'],
            'local_infracao' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
            'velocidade_medida' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'velocidade_maxima' => ['nullable', 'numeric', 'min:0', 'max:200'],
            'status' => ['sometimes', Rule::enum(MultaStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'placa.regex' => 'Placa deve estar no formato válido (ex: ABC1D23 ou ABC1234)',
            'data_infracao.before_or_equal' => 'Data da infração não pode ser futura',
            'municipio_id.exists' => 'Município inválido',
            'infracao_id.exists' => 'Infração inválida',
            'veiculo_id.exists' => 'Veículo inválido',
            'agente_id.exists' => 'Agente inválido',
        ];
    }
}
