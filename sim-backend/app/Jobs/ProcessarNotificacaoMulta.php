<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessarNotificacaoMulta implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;

    public function __construct(
        public int $multaId
    ) {}

    public function handle(): void
    {
        $multa = DB::table('multas')
            ->join('veiculos', 'multas.veiculo_id', '=', 'veiculos.id')
            ->select(
                'multas.*',
                'veiculos.proprietario_nome',
                'veiculos.proprietario_email',
                'veiculos.proprietario_endereco'
            )
            ->where('multas.id', $this->multaId)
            ->first();

        if (!$multa) {
            Log::error("Multa {$this->multaId} não encontrada para notificação");
            return;
        }

        // Gerar PDF da notificação (simulado)
        $pdfPath = "notificacoes/multa_{$multa->auto_infracao}.pdf";
        
        // Em produção: gerar PDF real com biblioteca como DomPDF ou mPDF
        // Exemplo: $pdf = PDF::loadView('pdf.notificacao_multa', compact('multa'));
        // Storage::put($pdfPath, $pdf->output());

        // Enviar email (simulado)
        if ($multa->proprietario_email) {
            // Mail::to($multa->proprietario_email)->send(new NotificacaoMultaMail($multa));
            Log::info("Email de notificação enviado para {$multa->proprietario_email}");
        }

        // Registrar envio postal (simulado)
        DB::table('notificacoes')->insert([
            'multa_id' => $this->multaId,
            'tipo' => 'notificacao_autuacao',
            'destinatario_nome' => $multa->proprietario_nome,
            'destinatario_endereco' => $multa->proprietario_endereco,
            'destinatario_email' => $multa->proprietario_email,
            'arquivo_pdf' => $pdfPath,
            'data_envio' => now(),
            'prazo_defesa' => now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Atualizar status
        DB::table('multas')->where('id', $this->multaId)->update([
            'status' => 'notificada',
            'data_notificacao' => now(),
            'updated_at' => now(),
        ]);

        Log::info("Notificação da multa {$this->multaId} processada com sucesso");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Job ProcessarNotificacaoMulta falhou para multa {$this->multaId}: {$exception->getMessage()}");
    }
}
