<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class EvidenciaController extends Controller
{
    public function store(Request $request, $multaId)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:jpg,jpeg,png|max:10240',
            'tipo' => 'required|in:foto_veiculo,foto_local,foto_infracao,documento,outro',
            'descricao' => 'nullable|string|max:500',
        ]);

        $multa = DB::table('multas')->where('id', $multaId)->first();
        if (!$multa) {
            return response()->json(['message' => 'Multa não encontrada'], 404);
        }

        // Verificar permissão
        $usuario = $request->get('usuario');
        if ($usuario->perfil !== 'administrador' && $multa->municipio_id != $usuario->municipio_id) {
            return response()->json(['message' => 'Sem permissão'], 403);
        }

        $file = $request->file('arquivo');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        // Criar diretório se não existir
        $path = storage_path('app/public/evidencias/' . $multaId);
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        // Salvar original
        $file->move($path, $filename);
        $filepath = 'evidencias/' . $multaId . '/' . $filename;

        // Criar thumbnail
        $thumbnailPath = $path . '/thumb_' . $filename;
        $img = imagecreatefromstring(file_get_contents($path . '/' . $filename));
        $width = imagesx($img);
        $height = imagesy($img);
        $newWidth = 200;
        $newHeight = ($height / $width) * $newWidth;
        $thumb = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagejpeg($thumb, $thumbnailPath, 85);
        imagedestroy($img);
        imagedestroy($thumb);

        $evidenciaId = DB::table('evidencias')->insertGetId([
            'multa_id' => $multaId,
            'tipo' => $request->tipo,
            'arquivo' => $filepath,
            'descricao' => $request->descricao,
            'tamanho' => filesize($path . '/' . $filename),
            'mime_type' => $file->getClientMimeType(),
            'usuario_upload_id' => $usuario->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Auditar
        DB::table('auditorias')->insert([
            'usuario_id' => $usuario->id,
            'municipio_id' => $multa->municipio_id,
            'tipo' => 'upload',
            'entidade' => 'Evidencia',
            'entidade_id' => $evidenciaId,
            'descricao' => 'Upload de evidência: ' . $request->tipo,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'data_hora' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $evidenciaId,
                'arquivo' => $filepath,
                'tipo' => $request->tipo,
                'url' => asset('storage/' . $filepath),
                'thumbnail' => asset('storage/evidencias/' . $multaId . '/thumb_' . $filename),
            ]
        ], 201);
    }

    public function index($multaId)
    {
        $evidencias = DB::table('evidencias')
            ->where('multa_id', $multaId)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($ev) use ($multaId) {
                return [
                    'id' => $ev->id,
                    'tipo' => $ev->tipo,
                    'descricao' => $ev->descricao,
                    'tamanho' => $ev->tamanho,
                    'mime_type' => $ev->mime_type,
                    'url' => asset('storage/' . $ev->arquivo),
                    'thumbnail' => asset('storage/evidencias/' . $multaId . '/thumb_' . basename($ev->arquivo)),
                    'created_at' => $ev->created_at,
                ];
            });

        return response()->json(['success' => true, 'data' => $evidencias]);
    }

    public function destroy(Request $request, $multaId, $id)
    {
        $evidencia = DB::table('evidencias')
            ->where('id', $id)
            ->where('multa_id', $multaId)
            ->first();

        if (!$evidencia) {
            return response()->json(['message' => 'Evidência não encontrada'], 404);
        }

        $multa = DB::table('multas')->where('id', $multaId)->first();
        $usuario = $request->get('usuario');

        if ($usuario->perfil !== 'administrador' && $multa->municipio_id != $usuario->municipio_id) {
            return response()->json(['message' => 'Sem permissão'], 403);
        }

        // Soft delete
        DB::table('evidencias')->where('id', $id)->update([
            'deleted_at' => now(),
            'updated_at' => now(),
        ]);

        // Auditar
        DB::table('auditorias')->insert([
            'usuario_id' => $usuario->id,
            'municipio_id' => $multa->municipio_id,
            'tipo' => 'exclusao',
            'entidade' => 'Evidencia',
            'entidade_id' => $id,
            'descricao' => 'Exclusão de evidência',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'data_hora' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Evidência excluída']);
    }
}
