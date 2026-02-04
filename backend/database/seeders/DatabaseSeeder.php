<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        echo "Limpando tabelas...\n";
        DB::table('multas')->delete();
        DB::table('agentes')->delete();
        
        // Verificar se existem infrações
        if (DB::table('infracoes')->count() == 0) {
            echo "Inserindo infrações...\n";
            DB::table('infracoes')->insert([
                ['codigo_ctb' => '50110', 'descricao' => 'Estacionar em local proibido', 'gravidade' => 'media', 'valor' => 88.38, 'pontos' => 3, 'created_at' => now(), 'updated_at' => now()],
                ['codigo_ctb' => '51950', 'descricao' => 'Ultrapassar semáforo vermelho', 'gravidade' => 'gravissima', 'valor' => 293.47, 'pontos' => 7, 'created_at' => now(), 'updated_at' => now()],
                ['codigo_ctb' => '53030', 'descricao' => 'Excesso de velocidade (20%)', 'gravidade' => 'grave', 'valor' => 130.16, 'pontos' => 4, 'created_at' => now(), 'updated_at' => now()],
                ['codigo_ctb' => '54480', 'descricao' => 'Usar celular ao volante', 'gravidade' => 'gravissima', 'valor' => 293.47, 'pontos' => 7, 'created_at' => now(), 'updated_at' => now()],
                ['codigo_ctb' => '55780', 'descricao' => 'Não usar cinto de segurança', 'gravidade' => 'grave', 'valor' => 195.23, 'pontos' => 5, 'created_at' => now(), 'updated_at' => now()],
            ]);
            echo "5 infrações criadas.\n";
        }

        echo "Inserindo agentes...\n";
        for ($i = 1; $i <= 15; $i++) {
            DB::table('agentes')->insert([
                'municipio_id' => (($i % 3) + 1),
                'matricula' => 'AGT-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'nome' => 'Agente ' . $i,
                'cpf' => sprintf('%011d', 20000000000 + $i),
                'cargo' => ['Agente de Trânsito', 'Agente Sênior', 'Fiscal'][$i % 3],
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        echo "15 agentes inseridos.\n";

        echo "Inserindo multas...\n";
        $infracoes = DB::table('infracoes')->get()->values();
        $veiculos = DB::table('veiculos')->get()->values();
        $agentes = DB::table('agentes')->get()->values();

        for ($i = 1; $i <= 100; $i++) {
            $veiculo = $veiculos[($i - 1) % $veiculos->count()];
            $infracao = $infracoes[($i - 1) % $infracoes->count()];
            $agente = $agentes[($i - 1) % $agentes->count()];
            
            $ano = 2024 + intval($i / 100);
            $mes = (($i % 12) + 1);
            $dia = (($i % 28) + 1);
            
            DB::table('multas')->insert([
                'auto_infracao' => $ano . str_pad($i, 6, '0', STR_PAD_LEFT),
                'municipio_id' => (($i % 3) + 1),
                'veiculo_id' => $veiculo->id,
                'agente_id' => $agente->id,
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
            ]);
        }
        echo "100 multas inseridas.\n";
        echo "Total de multas: " . DB::table('multas')->count() . "\n";
    }
}
