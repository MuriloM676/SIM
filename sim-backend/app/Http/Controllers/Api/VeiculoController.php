<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VeiculoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        
        $query = DB::table('veiculos')
            ->where('municipio_id', $request->user_municipio_id ?? 1)
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('placa', 'ILIKE', "%{$search}%")
                  ->orWhere('renavam', 'ILIKE', "%{$search}%")
                  ->orWhere('proprietario_nome', 'ILIKE', "%{$search}%");
            });
        }

        $total = $query->count();
        $veiculos = $query->skip(($request->input('page', 1) - 1) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $veiculos,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => (int)$request->input('page', 1),
                'last_page' => ceil($total / $perPage),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'placa' => 'required|string|size:7|unique:veiculos,placa',
            'renavam' => 'required|string|size:11|unique:veiculos,renavam',
            'chassi' => 'required|string|size:17',
            'marca' => 'required|string|max:50',
            'modelo' => 'required|string|max:50',
            'cor' => 'required|string|max:30',
            'ano_fabricacao' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'ano_modelo' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'categoria' => 'required|in:particular,aluguel,oficial,aprendizagem',
            'proprietario_nome' => 'required|string|max:100',
            'proprietario_cpf_cnpj' => 'required|string|max:18',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $veiculoId = DB::table('veiculos')->insertGetId([
            'municipio_id' => $request->user_municipio_id ?? 1,
            'placa' => strtoupper($request->placa),
            'renavam' => $request->renavam,
            'chassi' => strtoupper($request->chassi),
            'marca' => $request->marca,
            'modelo' => $request->modelo,
            'cor' => $request->cor,
            'ano_fabricacao' => $request->ano_fabricacao,
            'ano_modelo' => $request->ano_modelo,
            'categoria' => $request->categoria,
            'proprietario_nome' => $request->proprietario_nome,
            'proprietario_cpf_cnpj' => $request->proprietario_cpf_cnpj,
            'proprietario_endereco' => $request->proprietario_endereco,
            'proprietario_telefone' => $request->proprietario_telefone,
            'proprietario_email' => $request->proprietario_email,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('auditorias')->insert([
            'usuario_id' => $request->user_id ?? 1,
            'municipio_id' => $request->user_municipio_id ?? 1,
            'tipo' => 'criacao',
            'entidade' => 'Veiculo',
            'entidade_id' => $veiculoId,
            'dados_depois' => json_encode(['placa' => $request->placa]),
            'descricao' => 'Veículo cadastrado',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'base_legal_lgpd' => 'Art. 7º, II - Cumprimento de obrigação legal',
            'data_hora' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Veículo cadastrado com sucesso',
            'data' => ['id' => $veiculoId],
        ], 201);
    }

    public function show($id, Request $request)
    {
        $veiculo = DB::table('veiculos')
            ->where('id', $id)
            ->where('municipio_id', $request->user_municipio_id ?? 1)
            ->first();

        if (!$veiculo) {
            return response()->json([
                'success' => false,
                'message' => 'Veículo não encontrado',
            ], 404);
        }

        DB::table('auditorias')->insert([
            'usuario_id' => $request->user_id ?? 1,
            'municipio_id' => $request->user_municipio_id ?? 1,
            'tipo' => 'visualizacao',
            'entidade' => 'Veiculo',
            'entidade_id' => $id,
            'descricao' => 'Visualização de dados pessoais do proprietário',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'base_legal_lgpd' => 'Art. 7º, II - Cumprimento de obrigação legal',
            'data_hora' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $veiculo,
        ]);
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'proprietario_endereco' => 'nullable|string|max:255',
            'proprietario_telefone' => 'nullable|string|max:20',
            'proprietario_email' => 'nullable|email|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $veiculo = DB::table('veiculos')
            ->where('id', $id)
            ->where('municipio_id', $request->user_municipio_id ?? 1)
            ->first();

        if (!$veiculo) {
            return response()->json([
                'success' => false,
                'message' => 'Veículo não encontrado',
            ], 404);
        }

        DB::table('veiculos')->where('id', $id)->update([
            'proprietario_endereco' => $request->proprietario_endereco,
            'proprietario_telefone' => $request->proprietario_telefone,
            'proprietario_email' => $request->proprietario_email,
            'updated_at' => now(),
        ]);

        DB::table('auditorias')->insert([
            'usuario_id' => $request->user_id ?? 1,
            'municipio_id' => $request->user_municipio_id ?? 1,
            'tipo' => 'alteracao',
            'entidade' => 'Veiculo',
            'entidade_id' => $id,
            'dados_antes' => json_encode([
                'endereco' => $veiculo->proprietario_endereco,
                'telefone' => $veiculo->proprietario_telefone,
            ]),
            'dados_depois' => json_encode([
                'endereco' => $request->proprietario_endereco,
                'telefone' => $request->proprietario_telefone,
            ]),
            'descricao' => 'Dados do proprietário atualizados',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'base_legal_lgpd' => 'Art. 7º, II - Cumprimento de obrigação legal',
            'data_hora' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Veículo atualizado com sucesso',
        ]);
    }
}
