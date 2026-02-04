-- Inserir dados de teste direto

-- Limpar tabelas
DELETE FROM auditorias;
DELETE FROM recursos;
DELETE FROM multas;
DELETE FROM agentes;
DELETE FROM veiculos;

-- Inserir 50 veículos
INSERT INTO veiculos (municipio_id, placa, renavam, chassi, marca, modelo, cor, ano_fabricacao, ano_modelo, categoria, proprietario_nome, proprietario_cpf_cnpj, created_at, updated_at) VALUES
(1, 'ABC1234', '12345678901', '9BWZZZ377VT004251', 'Volkswagen', 'Gol', 'Prata', 2020, 2021, 'particular', 'João Silva', '12345678900', NOW(), NOW()),
(1, 'DEF5678', '23456789012', '9BWZZZ377VT004252', 'Fiat', 'Uno', 'Branco', 2019, 2020, 'particular', 'Maria Santos', '23456789011', NOW(), NOW()),
(1, 'GHI9012', '34567890123', '9BWZZZ377VT004253', 'Chevrolet', 'Onix', 'Preto', 2021, 2022, 'particular', 'Pedro Costa', '34567890122', NOW(), NOW()),
(2, 'JKL3456', '45678901234', '9BWZZZ377VT004254', 'Ford', 'Ka', 'Vermelho', 2018, 2019, 'particular', 'Ana Lima', '45678901233', NOW(), NOW()),
(2, 'MNO7890', '56789012345', '9BWZZZ377VT004255', 'Honda', 'Civic', 'Azul', 2022, 2023, 'particular', 'Carlos Souza', '56789012344', NOW(), NOW());

-- Inserir 10 agentes
INSERT INTO agentes (municipio_id, matricula, nome, cpf, cargo, telefone, email, ativo, created_at, updated_at) VALUES
(1, 'AGT-001', 'Agente Pedro', '11111111111', 'Agente de Trânsito', '(11) 91111-1111', 'pedro@transito.gov.br', true, NOW(), NOW()),
(1, 'AGT-002', 'Agente Ana', '22222222222', 'Agente Sênior', '(11) 92222-2222', 'ana@transito.gov.br', true, NOW(), NOW()),
(2, 'AGT-003', 'Agente Carlos', '33333333333', 'Fiscal', '(21) 93333-3333', 'carlos@transito.gov.br', true, NOW(), NOW());

-- Inserir 20 multas
INSERT INTO multas (auto_infracao, municipio_id, veiculo_id, agente_id, infracao_id, usuario_criador_id, status, placa, data_infracao, hora_infracao, local_infracao, latitude, longitude, valor_multa, pontos_cnh, created_at, updated_at) VALUES
('2024000001', 1, 1, 1, 1, 1, 'registrada', 'ABC1234', '2024-01-15', '10:30:00', 'Av. Paulista, 1000', -23.561, -46.656, 88.38, 3, NOW() - INTERVAL '20 days', NOW()),
('2024000002', 1, 2, 1, 2, 1, 'notificada', 'DEF5678', '2024-01-20', '14:15:00', 'Rua Augusta, 500', -23.562, -46.657, 130.16, 4, NOW() - INTERVAL '15 days', NOW()),
('2024000003', 1, 3, 2, 3, 2, 'enviada_orgao_externo', 'GHI9012', '2024-01-25', '16:45:00', 'Av. Faria Lima, 2000', -23.563, -46.658, 293.47, 7, NOW() - INTERVAL '10 days', NOW()),
('2024000004', 2, 4, 3, 1, 1, 'em_recurso', 'JKL3456', '2024-02-01', '09:00:00', 'Av. Atlântica, 300', -22.971, -43.182, 88.38, 3, NOW() - INTERVAL '5 days', NOW()),
('2024000005', 2, 5, 3, 2, 2, 'encerrada', 'MNO7890', '2024-02-03', '11:20:00', 'Rua do Catete, 100', -22.972, -43.183, 130.16, 4, NOW() - INTERVAL '2 days', NOW()),
('2026000001', 1, 1, 1, 3, 1, 'registrada', 'ABC1234', '2026-02-04', '08:00:00', 'Av. Paulista, 1500', -23.564, -46.659, 293.47, 7, NOW(), NOW()),
('2026000002', 1, 2, 1, 1, 1, 'registrada', 'DEF5678', '2026-02-04', '09:30:00', 'Rua Augusta, 800', -23.565, -46.660, 88.38, 3, NOW(), NOW()),
('2026000003', 1, 3, 2, 2, 2, 'registrada', 'GHI9012', '2026-02-04', '10:45:00', 'Av. Rebouças, 300', -23.566, -46.661, 130.16, 4, NOW(), NOW()),
('2026000004', 1, 1, 1, 1, 1, 'notificada', 'ABC1234', '2026-02-03', '13:15:00', 'Rua da Consolação, 200', -23.567, -46.662, 88.38, 3, NOW(), NOW()),
('2026000005', 1, 2, 2, 3, 2, 'enviada_orgao_externo', 'DEF5678', '2026-02-03', '15:00:00', 'Av. Ibirapuera, 500', -23.568, -46.663, 293.47, 7, NOW(), NOW());

-- Inserir auditoria
INSERT INTO auditorias (usuario_id, municipio_id, tipo, entidade, entidade_id, descricao, ip, user_agent, data_hora, created_at, updated_at) VALUES
(1, 1, 'criacao', 'Multa', 1, 'Criação de multa', '127.0.0.1', 'Mozilla/5.0', NOW(), NOW(), NOW()),
(1, 1, 'criacao', 'Multa', 2, 'Criação de multa', '127.0.0.1', 'Mozilla/5.0', NOW(), NOW(), NOW());
