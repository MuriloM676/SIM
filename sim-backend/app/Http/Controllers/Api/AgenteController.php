<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AgenteController extends Controller
{
    public function index(Request $request)
    {
        $agentes = DB::table('agentes')
            ->where('municipio_id', $request->user_municipio_id ?? 1)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $agentes,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'matricula' => 'required|string|max:20|unique:agentes,matricula',
            'nome' => 'required|string|max:100',
            'cpf' => 'required|string|size:11|unique:agentes,cpf',
            'cargo' => 'required|string|max:50',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $agenteId = DB::table('agentes')->insertGetId([
            'municipio_id' => $request->user_municipio_id ?? 1,
            'matricula' => $request->matricula,
            'nome' => $request->nome,
            'cpf' => $request->cpf,
            'cargo' => $request->cargo,
            'telefone' => $request->telefone,
            'email' => $request->email,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Agente cadastrado com sucesso',
            'data' => ['id' => $agenteId],
        ], 201);
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'ativo' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::table('agentes')
            ->where('id', $id)
            ->where('municipio_id', $request->user_municipio_id ?? 1)
            ->update([
                'telefone' => $request->telefone,
                'email' => $request->email,
                'ativo' => $request->ativo ?? true,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Agente atualizado com sucesso',
        ]);
    }
}
