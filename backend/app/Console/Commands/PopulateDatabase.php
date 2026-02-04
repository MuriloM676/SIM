<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopulateDatabase extends Command
{
    protected $signature = 'db:populate';
    protected $description = 'Popula o banco de dados com dados de teste';

    public function handle()
    {
        $this->info('Limpando tabelas...');
        DB::table('auditorias')->delete();
        DB::table('recursos')->delete();
        DB::table('multas')->delete();
        DB::table('agentes')->delete();
        
        // Não limpar veículos que já existem
        $veiculosExistentes = DB::table('veiculos')->count();
        if ($veiculosExistentes < 50) {
            $this->info("Inserindo mais veículos...");
            $veiculos = [];
            for ($i = $veiculosExistentes + 1; $i <= 50; $i++) {
                $veiculos[] = [
                    'municipio_id' => (($i % 3) + 1),
                    'placa' => sprintf('%s%s%s%d%d%d%d', 
                        chr(65 + ($i % 26)), 
                        chr(65 + (($i * 2) % 26)), 
                        chr(65 + (($i * 3) % 26)), 
                        ($i % 10), 
                        (($i * 2) % 10), 
                        (($i * 3) % 10), 
                        (($i * 4) % 10)
                    ),
                    'renavam' => sprintf('%011d', 10000000000 + $i),
                    'chassi' => sprintf('9BWZZZ377VT%06d', $i),
                    'marca' => ['Volkswagen', 'Fiat', 'Chevrolet', 'Ford', 'Honda'][$i % 5],
                    'modelo' => ['Gol', 'Uno', 'Onix', 'Ka', 'Civic'][$i % 5],
                    'cor' => ['Prata', 'Branco', 'Preto', 'Vermelho', 'Azul'][$i % 5],
                    'ano_fabricacao' => 2015 + ($i % 10),
                    'ano_modelo' => 2016 + ($i % 10),
                    'categoria' => 'particular',
                    'proprietario_nome' => 'Proprietário ' . $i,
                    'proprietario_cpf_cnpj' => sprintf('%011d', 10000000000 + $i),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('veiculos')->insert($veiculos);
            $this->info(count($veiculos) . ' veículos inseridos.');
        }

        $this->info('Inserindo agentes...');
        $agentes = [];
        for ($i = 1; $i <= 15; $i++) {
            $agentes[] = [
                'municipio_id' => (($i % 3) + 1),
                'matricula' => 'AGT-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'nome' => 'Agente ' . $i,
                'cpf' => sprintf('%011d', 20000000000 + $i),
                'cargo' => ['Agente de Trânsito', 'Agente Sênior', 'Fiscal'][$i % 3],
                'telefone' => sprintf('(11) 9%04d-%04d', $i, $i),
                'email' => 'agente' . $i . '@transito.gov.br',
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('agentes')->insert($agentes);
        $this->info('15 agentes inseridos.');

        $this->info('Inserindo multas...');
        $infracoes = DB::table('infracoes')->limit(5)->get();
        $veiculos = DB::table('veiculos')->get();
        $multas = [];

        for ($i = 1; $i <= 100; $i++) {
            $veiculo = $veiculos[($i - 1) % $veiculos->count()];
            $infracao = $infracoes[($i % 5)];
            
            $ano = 2024 + intval($i / 100);
            $mes = (($i % 12) + 1);
            $dia = (($i % 28) + 1);
            
            $multas[] = [
                'auto_infracao' => $ano . str_pad($i, 6, '0', STR_PAD_LEFT),
                'municipio_id' => (($i % 3) + 1),
                'veiculo_id' => $veiculo->id,
                'agente_id' => (($i % 15) + 1),
                'infracao_id' => $infracao->id,
                'usuario_criador_id' => 1,
                'status' => ['registrada', 'notificada', 'em_recurso', 'enviada_orgao_externo', 'encerrada'][$i % 5],
                'placa' => $veiculo->placa,
                'data_infracao' => sprintf('%04d-%02d-%02d', $ano, $mes, $dia),
                'hora_infracao' => sprintf('%02d:%02d:00', ($i % 24), ($i % 60)),
                'local_infracao' => 'Local ' . $i,
                'latitude' => -23.5 + ($i * 0.001),
                'longitude' => -46.6 + ($i * 0.001),
                'valor_multa' => $infracao->valor,
                'pontos_cnh' => $infracao->pontos,
                'created_at' => now()->subDays(100 - $i),
                'updated_at' => now()->subDays(100 - $i),
            ];
        }

        foreach (array_chunk($multas, 20) as $chunk) {
            DB::table('multas')->insert($chunk);
        }
        $this->info('100 multas inseridas.');

        $this->info('');
        $this->info('=== RESUMO ===');
        $this->info('Veículos: ' . DB::table('veiculos')->count());
        $this->info('Agentes: ' . DB::table('agentes')->count());
        $this->info('Multas: ' . DB::table('multas')->count());
        $this->info('Por status:');
        $stats = DB::table('multas')->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->get();
        foreach ($stats as $stat) {
            $this->info("  - {$stat->status}: {$stat->total}");
        }

        $this->info('');
        $this->info('✓ Dados inseridos com sucesso!');
        
        return 0;
    }
}
