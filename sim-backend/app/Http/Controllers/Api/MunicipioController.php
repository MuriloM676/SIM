<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MunicipioController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('municipios')->orderBy('nome');

        // Apenas admin vê todos
        if ($request->user_perfil !== 'administrador') {
            $query->where('id', $request->user_municipio_id ?? 1);
        }

        $municipios = $query->get();

        return response()->json([
            'success' => true,
            'data' => $municipios,
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user_perfil !== 'administrador') {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem criar municípios',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:100|unique:municipios,nome',
            'uf' => 'required|string|size:2',
            'codigo_ibge' => 'required|string|size:7|unique:municipios,codigo_ibge',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $municipioId = DB::table('municipios')->insertGetId([
            'nome' => $request->nome,
            'uf' => strtoupper($request->uf),
            'codigo_ibge' => $request->codigo_ibge,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Município cadastrado com sucesso',
            'data' => ['id' => $municipioId],
        ], 201);
    }

    public function update($id, Request $request)
    {
        if ($request->user_perfil !== 'administrador') {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem atualizar municípios',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'ativo' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::table('municipios')->where('id', $id)->update([
            'ativo' => $request->ativo ?? true,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Município atualizado com sucesso',
        ]);
    }
}
