<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InfracaoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $query = DB::table('infracoes')
            ->where('ativo', true)
            ->orderBy('codigo_ctb');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('codigo_ctb', 'ILIKE', "%{$search}%")
                  ->orWhere('descricao', 'ILIKE', "%{$search}%");
            });
        }

        $infracoes = $query->get();

        return response()->json([
            'success' => true,
            'data' => $infracoes,
        ]);
    }

    public function show($id)
    {
        $infracao = DB::table('infracoes')->where('id', $id)->first();

        if (!$infracao) {
            return response()->json([
                'success' => false,
                'message' => 'Infração não encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $infracao,
        ]);
    }
}
