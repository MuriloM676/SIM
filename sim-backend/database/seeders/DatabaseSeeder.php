<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seed do banco de dados...');
        
        // Limpar todas as tabelas antes de popular
        $this->command->info('🧹 Limpando tabelas existentes...');
        DB::statement('SET CONSTRAINTS ALL DEFERRED');
        
        $tables = [
            'auditorias', 'log_acessos', 'integracoes_log', 'evidencias', 
            'recursos', 'multas_historico', 'multas', 'infracoes', 
            'agentes', 'veiculos', 'tokens_invalidos', 'relatorios',
            'municipio_configuracoes', 'usuarios', 'municipios'
        ];
        
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        $this->seedMunicipios();
        $this->seedConfiguracoes();
        $this->seedUsuarios();
        $this->seedVeiculos();
        $this->seedAgentes();
        $this->seedInfracoes();
        $this->seedMultas();
        $this->seedHistoricos();
        $this->seedRecursos();
        $this->seedEvidencias();
        $this->seedAuditorias();
        $this->seedLogsAcesso();
        $this->seedIntegracoesLog();

        $this->command->info('');
        $this->command->info('✅ Seed concluído com sucesso!');
        $this->command->info('');
        $this->command->info('🔑 CREDENCIAIS DE ACESSO:');
        $this->command->info('  👤 Admin Global:  admin@sim.gov.br / senha123');
        $this->command->info('  👤 Gestor SP:     gestor.sp@sim.gov.br / senha123');
        $this->command->info('  👤 Operador SP:   operador.sp@sim.gov.br / senha123');
        $this->command->info('  👤 Auditor SP:    auditor.sp@sim.gov.br / senha123');
        $this->command->info('');
        $this->command->info('📊 DADOS CRIADOS:');
        $this->command->info('  • 3 Municípios');
        $this->command->info('  • 6 Usuários (diferentes perfis)');
        $this->command->info('  • 3 Veículos');
        $this->command->info('  • 3 Agentes de Trânsito');
        $this->command->info('  • 5 Infrações (CTB)');
        $this->command->info('  • 5 Multas (diferentes status)');
        $this->command->info('  • 2 Recursos Administrativos');
        $this->command->info('  • 2 Evidências');
    }

    private function seedMunicipios()
    {
        $this->command->info('📍 Criando municípios...');
        
        $municipios = [
            ['id' => 1, 'nome' => 'São Paulo', 'uf' => 'SP', 'codigo_ibge' => '3550308', 'cnpj' => '46.395.000/0001-39', 'ativo' => true],
            ['id' => 2, 'nome' => 'Rio de Janeiro', 'uf' => 'RJ', 'codigo_ibge' => '3304557', 'cnpj' => '42.498.000/0001-48', 'ativo' => true],
            ['id' => 3, 'nome' => 'Belo Horizonte', 'uf' => 'MG', 'codigo_ibge' => '3106200', 'cnpj' => '18.715.000/0001-40', 'ativo' => true],
        ];
        
        foreach ($municipios as $municipio) {
            DB::table('municipios')->insert(array_merge($municipio, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedConfiguracoes()
    {
        $this->command->info('⚙️  Criando configurações dos municípios...');
        
        for ($i = 1; $i <= 3; $i++) {
            DB::table('municipio_configuracoes')->insert([
                'municipio_id' => $i,
                'dias_retencao_dados' => 1825,
                'lgpd_ativo' => true,
                'finalidade_tratamento_dados' => 'Gestão e fiscalização de infrações de trânsito conforme CTB',
                'base_legal_lgpd' => 'Art. 7º, II - Cumprimento de obrigação legal ou regulatória',
                'permitir_edicao_multa_registrada' => false,
                'prazo_defesa_previa_dias' => 15,
                'prazo_recurso_jari_dias' => 30,
                'prazo_recurso_cetran_dias' => 30,
                'integracao_detran_ativa' => true,
                'detran_endpoint' => 'https://api-detran.exemplo.gov.br',
                'notificar_email' => true,
                'notificar_sms' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedUsuarios()
    {
        $this->command->info('👥 Criando usuários...');
        
        $usuarios = [
            ['nome' => 'Admin Global', 'email' => 'admin@sim.gov.br', 'perfil' => 'administrador', 'municipio_id' => 1],
            ['nome' => 'Gestor SP', 'email' => 'gestor.sp@sim.gov.br', 'perfil' => 'gestor', 'municipio_id' => 1],
            ['nome' => 'Operador SP', 'email' => 'operador.sp@sim.gov.br', 'perfil' => 'operador', 'municipio_id' => 1],
            ['nome' => 'Auditor SP', 'email' => 'auditor.sp@sim.gov.br', 'perfil' => 'auditor', 'municipio_id' => 1],
            ['nome' => 'Gestor RJ', 'email' => 'gestor.rj@sim.gov.br', 'perfil' => 'gestor', 'municipio_id' => 2],
            ['nome' => 'Operador BH', 'email' => 'operador.bh@sim.gov.br', 'perfil' => 'operador', 'municipio_id' => 3],
        ];

        foreach ($usuarios as $usuario) {
            DB::table('usuarios')->insert(array_merge($usuario, [
                'cpf' => str_pad($usuario['municipio_id'] . rand(10000000, 99999999), 11, '0', STR_PAD_LEFT),
                'password' => Hash::make('senha123'),
                'ativo' => true,
                'ultimo_acesso' => now()->subDays(rand(0, 7)),
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedVeiculos()
    {
        $this->command->info('🚗 Criando veículos...');
        
        $veiculos = [
            [
                'municipio_id' => 1, 'placa' => 'ABC1234', 'renavam' => '12345678901',
                'chassi' => '9BWZZZ377VT004251', 'marca' => 'Volkswagen', 'modelo' => 'Gol',
                'cor' => 'Prata', 'ano_fabricacao' => 2020, 'ano_modelo' => 2021,
                'categoria' => 'particular', 'proprietario_nome' => 'João da Silva',
                'proprietario_cpf_cnpj' => '12345678900',
            ],
            [
                'municipio_id' => 1, 'placa' => 'XYZ5678', 'renavam' => '98765432109',
                'chassi' => '9BD158C59VW999999', 'marca' => 'Fiat', 'modelo' => 'Uno',
                'cor' => 'Branco', 'ano_fabricacao' => 2019, 'ano_modelo' => 2020,
                'categoria' => 'particular', 'proprietario_nome' => 'Maria Santos',
                'proprietario_cpf_cnpj' => '98765432100',
            ],
            [
                'municipio_id' => 2, 'placa' => 'RIO2023', 'renavam' => '11122233344',
                'chassi' => '8AP377456FU012345', 'marca' => 'Chevrolet', 'modelo' => 'Onix',
                'cor' => 'Vermelho', 'ano_fabricacao' => 2022, 'ano_modelo' => 2023,
                'categoria' => 'particular', 'proprietario_nome' => 'Carlos Oliveira',
                'proprietario_cpf_cnpj' => '11122233344',
            ],
        ];

        foreach ($veiculos as $veiculo) {
            DB::table('veiculos')->insert(array_merge($veiculo, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedAgentes()
    {
        $this->command->info('🚔 Criando agentes de trânsito...');
        
        $agentes = [
            ['municipio_id' => 1, 'matricula' => 'AGT-001', 'nome' => 'Pedro Alves', 'cpf' => '11111111111', 'cargo' => 'Agente de Trânsito'],
            ['municipio_id' => 1, 'matricula' => 'AGT-002', 'nome' => 'Ana Costa', 'cpf' => '22222222222', 'cargo' => 'Agente Sênior'],
            ['municipio_id' => 2, 'matricula' => 'AGT-RJ-001', 'nome' => 'Roberto Silva', 'cpf' => '33333333333', 'cargo' => 'Agente de Trânsito'],
        ];

        foreach ($agentes as $agente) {
            DB::table('agentes')->insert(array_merge($agente, [
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedInfracoes()
    {
        $this->command->info('📋 Criando infrações (CTB)...');
        
        $infracoes = [
            ['codigo_ctb' => '50011', 'descricao' => 'Estacionar afastado da guia', 'gravidade' => 'leve', 'pontos' => 3, 'valor' => 88.38, 'detalhamento' => 'Art. 181, I'],
            ['codigo_ctb' => '51562', 'descricao' => 'Excesso de velocidade até 20%', 'gravidade' => 'media', 'pontos' => 4, 'valor' => 130.16, 'detalhamento' => 'Art. 218, I'],
            ['codigo_ctb' => '53452', 'descricao' => 'Avançar sinal vermelho', 'gravidade' => 'gravissima', 'pontos' => 7, 'valor' => 293.47, 'detalhamento' => 'Art. 208'],
            ['codigo_ctb' => '55721', 'descricao' => 'Dirigir sob influência de álcool', 'gravidade' => 'gravissima', 'pontos' => 7, 'valor' => 2934.70, 'detalhamento' => 'Art. 165'],
            ['codigo_ctb' => '51911', 'descricao' => 'Estacionar em local proibido', 'gravidade' => 'media', 'pontos' => 4, 'valor' => 130.16, 'detalhamento' => 'Art. 181, XVI'],
        ];

        foreach ($infracoes as $infracao) {
            DB::table('infracoes')->insert(array_merge($infracao, [
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedMultas()
    {
        $this->command->info('📝 Criando multas...');
        
        $multas = [
            [
                'id' => 1, 'auto_infracao' => 'AI-2024-000001', 'municipio_id' => 1, 'veiculo_id' => 1,
                'agente_id' => 1, 'infracao_id' => 1, 'usuario_criador_id' => 3,
                'status' => 'registrada', 'placa' => 'ABC1234',
                'data_infracao' => now()->subDays(10), 'hora_infracao' => '14:30:00',
                'local_infracao' => 'Av. Paulista, 1000', 'valor_multa' => 88.38, 'pontos_cnh' => 3,
                'observacoes' => 'Veículo estacionado afastado da guia',
            ],
            [
                'id' => 2, 'auto_infracao' => 'AI-2024-000002', 'municipio_id' => 1, 'veiculo_id' => 2,
                'agente_id' => 1, 'infracao_id' => 2, 'usuario_criador_id' => 3,
                'status' => 'enviada_orgao_externo', 'placa' => 'XYZ5678',
                'data_infracao' => now()->subDays(20), 'hora_infracao' => '09:15:00',
                'local_infracao' => 'Rua Augusta, 500', 'valor_multa' => 130.16, 'pontos_cnh' => 4,
                'observacoes' => 'Radar: 72 km/h em via de 60 km/h',
            ],
            [
                'id' => 3, 'auto_infracao' => 'AI-2024-000003', 'municipio_id' => 1, 'veiculo_id' => 1,
                'agente_id' => 2, 'infracao_id' => 3, 'usuario_criador_id' => 2,
                'status' => 'notificada', 'placa' => 'ABC1234',
                'data_infracao' => now()->subDays(30), 'hora_infracao' => '18:45:00',
                'local_infracao' => 'Av. Brigadeiro, 2000', 'valor_multa' => 293.47, 'pontos_cnh' => 7,
                'observacoes' => 'Avançou sinal vermelho',
            ],
            [
                'id' => 4, 'auto_infracao' => 'AI-2024-000004', 'municipio_id' => 1, 'veiculo_id' => 2,
                'agente_id' => 1, 'infracao_id' => 5, 'usuario_criador_id' => 3,
                'status' => 'em_recurso', 'placa' => 'XYZ5678',
                'data_infracao' => now()->subDays(45), 'hora_infracao' => '12:00:00',
                'local_infracao' => 'Rua da Consolação, 100', 'valor_multa' => 130.16, 'pontos_cnh' => 4,
                'observacoes' => 'Estacionamento em fila dupla',
            ],
            [
                'id' => 5, 'auto_infracao' => 'AI-2024-000005', 'municipio_id' => 2, 'veiculo_id' => 3,
                'agente_id' => 3, 'infracao_id' => 1, 'usuario_criador_id' => 5,
                'status' => 'deferida', 'placa' => 'RIO2023',
                'data_infracao' => now()->subDays(60), 'hora_infracao' => '16:20:00',
                'local_infracao' => 'Av. Atlântica, 300', 'valor_multa' => 88.38, 'pontos_cnh' => 3,
                'observacoes' => 'Recurso deferido - sinalização irregular',
            ],
        ];

        foreach ($multas as $multa) {
            DB::table('multas')->insert(array_merge($multa, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedHistoricos()
    {
        $this->command->info('📜 Criando histórico de multas...');
        
        $historicos = [
            ['multa_id' => 1, 'status_anterior' => 'rascunho', 'status_novo' => 'registrada', 'usuario_id' => 3, 'justificativa' => 'Registro inicial', 'data_transicao' => now()->subDays(10)],
            ['multa_id' => 2, 'status_anterior' => 'rascunho', 'status_novo' => 'registrada', 'usuario_id' => 3, 'justificativa' => 'Registro inicial', 'data_transicao' => now()->subDays(20)],
            ['multa_id' => 2, 'status_anterior' => 'registrada', 'status_novo' => 'enviada_orgao_externo', 'usuario_id' => 2, 'justificativa' => 'Envio Detran', 'data_transicao' => now()->subDays(19)],
            ['multa_id' => 3, 'status_anterior' => 'registrada', 'status_novo' => 'enviada_orgao_externo', 'usuario_id' => 2, 'justificativa' => 'Envio Detran', 'data_transicao' => now()->subDays(29)],
            ['multa_id' => 3, 'status_anterior' => 'enviada_orgao_externo', 'status_novo' => 'notificada', 'usuario_id' => 2, 'justificativa' => 'Proprietário notificado', 'data_transicao' => now()->subDays(25)],
        ];

        foreach ($historicos as $historico) {
            DB::table('multas_historico')->insert($historico);
        }
    }

    private function seedRecursos()
    {
        $this->command->info('⚖️  Criando recursos administrativos...');
        
        $recursos = [
            [
                'numero_protocolo' => 'REC-2024-000001', 'multa_id' => 4, 'usuario_criador_id' => 3,
                'tipo' => 'defesa_previa',
                'fundamentacao' => 'Sinalização encoberta por árvores, impossibilitando visualização.',
                'status' => 'em_analise', 'data_protocolo' => now()->subDays(5),
            ],
            [
                'numero_protocolo' => 'REC-2024-000002', 'multa_id' => 5, 'usuario_criador_id' => 5,
                'tipo' => 'defesa_previa',
                'fundamentacao' => 'Placa não estava visível conforme fotos.',
                'status' => 'analisado', 'decisao' => 'deferido',
                'parecer_tecnico' => 'Vistoria confirmou sinalização irregular.',
                'justificativa_decisao' => 'Deferido por irregularidade.',
                'usuario_julgador_id' => 2, 'data_julgamento' => now()->subDays(2),
                'data_protocolo' => now()->subDays(55),
            ],
        ];

        foreach ($recursos as $recurso) {
            DB::table('recursos')->insert(array_merge($recurso, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedEvidencias()
    {
        $this->command->info('📎 Criando evidências...');
        
        $evidencias = [
            [
                'multa_id' => 1, 'usuario_upload_id' => 3, 'tipo' => 'foto',
                'nome_original' => 'infracao_estacionamento.jpg',
                'nome_armazenado' => 'multa_1_evidencia_1.jpg',
                'mime_type' => 'image/jpeg', 'tamanho_bytes' => 245680,
                'hash_sha256' => hash('sha256', 'fake_1'),
                'descricao' => 'Veículo estacionado irregularmente',
                'caminho_storage' => 'evidencias/2024/01/multa_1_evidencia_1.jpg',
                'data_upload' => now()->subDays(10),
            ],
            [
                'multa_id' => 3, 'usuario_upload_id' => 2, 'tipo' => 'foto',
                'nome_original' => 'semaforo_vermelho.jpg',
                'nome_armazenado' => 'multa_3_evidencia_1.jpg',
                'mime_type' => 'image/jpeg', 'tamanho_bytes' => 312450,
                'hash_sha256' => hash('sha256', 'fake_2'),
                'descricao' => 'Veículo avançando sinal',
                'caminho_storage' => 'evidencias/2024/01/multa_3_evidencia_1.jpg',
                'data_upload' => now()->subDays(30),
            ],
        ];

        foreach ($evidencias as $evidencia) {
            DB::table('evidencias')->insert(array_merge($evidencia, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedAuditorias()
    {
        $this->command->info('🔍 Criando registros de auditoria...');
        
        $auditorias = [
            [
                'usuario_id' => 1, 'municipio_id' => 1, 'tipo' => 'criacao',
                'entidade' => 'Multa', 'entidade_id' => 1,
                'dados_antes' => null,
                'dados_depois' => json_encode(['status' => 'rascunho']),
                'descricao' => 'Criação de multa',
                'ip' => '192.168.1.100', 'user_agent' => 'Mozilla/5.0',
                'base_legal_lgpd' => 'Art. 7º, II',
                'data_hora' => now()->subDays(10),
            ],
        ];

        foreach ($auditorias as $auditoria) {
            DB::table('auditorias')->insert(array_merge($auditoria, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedLogsAcesso()
    {
        $this->command->info('🔐 Criando logs de acesso...');
        
        $acessos = [
            ['usuario_id' => 1, 'municipio_id' => 1, 'acao' => 'login', 'ip' => '192.168.1.100', 'user_agent' => 'Mozilla/5.0', 'data_hora' => now()],
            ['usuario_id' => 2, 'municipio_id' => 1, 'acao' => 'login', 'ip' => '192.168.1.101', 'user_agent' => 'Chrome/90', 'data_hora' => now()->subDays(2)],
        ];

        foreach ($acessos as $acesso) {
            DB::table('log_acessos')->insert($acesso);
        }
    }

    private function seedIntegracoesLog()
    {
        $this->command->info('🔌 Criando logs de integração...');
        
        DB::table('integracoes_log')->insert([
            'sistema_externo' => 'detran',
            'operacao' => 'envio_multa',
            'tipo' => 'response',
            'multa_id' => 2,
            'usuario_id' => 3,
            'metodo_http' => 'POST',
            'endpoint' => 'https://api-detran.exemplo.gov.br/multas',
            'status_http' => 200,
            'request_headers' => json_encode(['Content-Type' => 'application/json']),
            'request_body' => json_encode(['multa_id' => 2]),
            'response_headers' => json_encode(['Trace-Id' => 'abc-123']),
            'response_body' => json_encode(['success' => true, 'protocolo' => 'DET-123456']),
            'tempo_resposta_ms' => 420,
            'sucesso' => true,
            'erro' => null,
            'data_hora' => now()->subDays(19),
        ]);
    }
}
