<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        
        $query = DB::table('usuarios')
            ->join('municipios', 'usuarios.municipio_id', '=', 'municipios.id')
            ->select('usuarios.*', 'municipios.nome as municipio_nome')
            ->orderBy('usuarios.nome');

        // Admin vê todos, outros veem apenas do seu município
        if ($request->user_perfil !== 'administrador') {
            $query->where('usuarios.municipio_id', $request->user_municipio_id ?? 1);
        }

        $total = $query->count();
        $usuarios = $query->skip(($request->input('page', 1) - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Remover senha do retorno
        foreach ($usuarios as $usuario) {
            unset($usuario->password);
        }

        return response()->json([
            'success' => true,
            'data' => $usuarios,
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
            'nome' => 'required|string|max:100',
            'email' => 'required|email|unique:usuarios,email',
            'cpf' => 'required|string|size:11|unique:usuarios,cpf',
            'password' => 'required|string|min:6',
            'perfil' => 'required|in:administrador,gestor,operador,auditor',
            'municipio_id' => 'required|exists:municipios,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Apenas admin pode criar admin
        if ($request->perfil === 'administrador' && $request->user_perfil !== 'administrador') {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem criar outros administradores',
            ], 403);
        }

        $usuarioId = DB::table('usuarios')->insertGetId([
            'nome' => $request->nome,
            'email' => $request->email,
            'cpf' => $request->cpf,
            'password' => Hash::make($request->password),
            'perfil' => $request->perfil,
            'municipio_id' => $request->municipio_id,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('auditorias')->insert([
            'usuario_id' => $request->user_id ?? 1,
            'municipio_id' => $request->user_municipio_id ?? 1,
            'tipo' => 'criacao',
            'entidade' => 'Usuario',
            'entidade_id' => $usuarioId,
            'dados_depois' => json_encode(['email' => $request->email, 'perfil' => $request->perfil]),
            'descricao' => 'Usuário criado',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'base_legal_lgpd' => 'Art. 7º, II - Cumprimento de obrigação legal',
            'data_hora' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuário criado com sucesso',
            'data' => ['id' => $usuarioId],
        ], 201);
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'string|max:100',
            'telefone' => 'nullable|string|max:20',
            'ativo' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $usuario = DB::table('usuarios')->where('id', $id)->first();

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado',
            ], 404);
        }

        $updates = array_filter([
            'nome' => $request->nome,
            'telefone' => $request->telefone,
            'ativo' => $request->ativo,
            'updated_at' => now(),
        ], fn($value) => !is_null($value));

        DB::table('usuarios')->where('id', $id)->update($updates);

        DB::table('auditorias')->insert([
            'usuario_id' => $request->user_id ?? 1,
            'municipio_id' => $request->user_municipio_id ?? 1,
            'tipo' => 'alteracao',
            'entidade' => 'Usuario',
            'entidade_id' => $id,
            'dados_depois' => json_encode($updates),
            'descricao' => 'Dados do usuário atualizados',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'base_legal_lgpd' => 'Art. 7º, II - Cumprimento de obrigação legal',
            'data_hora' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuário atualizado com sucesso',
        ]);
    }

    public function resetPassword($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::table('usuarios')->where('id', $id)->update([
            'password' => Hash::make($request->password),
            'updated_at' => now(),
        ]);

        DB::table('auditorias')->insert([
            'usuario_id' => $request->user_id ?? 1,
            'municipio_id' => $request->user_municipio_id ?? 1,
            'tipo' => 'alteracao',
            'entidade' => 'Usuario',
            'entidade_id' => $id,
            'descricao' => 'Senha resetada',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'base_legal_lgpd' => 'Art. 7º, II - Cumprimento de obrigação legal',
            'data_hora' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Senha resetada com sucesso',
        ]);
    }
}
