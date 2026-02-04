-- Popular banco de dados SIM

-- Limpar dados existentes (cuidado!)
TRUNCATE TABLE auditorias, recursos, multas, agentes, veiculos, usuarios, municipios RESTART IDENTITY CASCADE;

-- Inserir municípios
INSERT INTO municipios (nome, uf, codigo_ibge, cnpj, ativo, created_at, updated_at) VALUES
('São Paulo', 'SP', '3550308', '46.395.000/0001-39', true, NOW(), NOW()),
('Rio de Janeiro', 'RJ', '3304557', '42.498.000/0001-48', true, NOW(), NOW()),
('Belo Horizonte', 'MG', '3106200', '18.715.000/0001-40', true, NOW(), NOW());

-- Inserir usuários
INSERT INTO usuarios (nome, email, cpf, password, perfil, municipio_id, ativo, created_at, updated_at) VALUES
('Admin Global', 'admin@sim.gov.br', '12345678901', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador', 1, true, NOW(), NOW()),
('Gestor SP', 'gestor.sp@sim.gov.br', '12345678902', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gestor', 1, true, NOW(), NOW()),
('Operador SP', 'operador.sp@sim.gov.br', '12345678903', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'operador', 1, true, NOW(), NOW());

-- Inserir veículos
INSERT INTO veiculos (municipio_id, placa, renavam, chassi, marca, modelo, cor, ano_fabricacao, ano_modelo, categoria, proprietario_nome, proprietario_cpf_cnpj, created_at, updated_at)
SELECT 
    ((n % 3) + 1)::int as municipio_id,
    CHR(65 + (n % 26)) || CHR(65 + ((n+1) % 26)) || CHR(65 + ((n+2) % 26)) || LPAD((1000 + n)::text, 4, '0') as placa,
    LPAD((10000000000 + n)::text, 11, '0') as renavam,
    '9BW' || UPPER(SUBSTRING(MD5(n::text), 1, 14)) as chassi,
    (ARRAY['Volkswagen', 'Fiat', 'Chevrolet', 'Ford', 'Honda'])[((n % 5) + 1)] as marca,
    (ARRAY['Gol', 'Uno', 'Onix', 'Ka', 'Civic'])[((n % 5) + 1)] as modelo,
    (ARRAY['Prata', 'Branco', 'Preto', 'Vermelho', 'Azul'])[((n % 5) + 1)] as cor,
    2018 + (n % 6) as ano_fabricacao,
    2019 + (n % 6) as ano_modelo,
    CASE WHEN n % 10 = 0 THEN 'comercial' ELSE 'particular' END as categoria,
    'Proprietário ' || n::text as proprietario_nome,
    LPAD((10000000000 + n)::text, 11, '0') as proprietario_cpf_cnpj,
    NOW() - (n || ' days')::interval as created_at,
    NOW() as updated_at
FROM generate_series(1, 50) as n;

-- Inserir agentes
INSERT INTO agentes (municipio_id, matricula, nome, cpf, cargo, telefone, email, ativo, created_at, updated_at)
SELECT 
    ((n % 3) + 1)::int as municipio_id,
    'AGT-' || LPAD(n::text, 4, '0') as matricula,
    'Agente ' || n::text as nome,
    LPAD((10000000000 + n)::text, 11, '0') as cpf,
    (ARRAY['Agente de Trânsito', 'Agente Sênior', 'Fiscal'])[((n % 3) + 1)] as cargo,
    '(11) 9' || LPAD((1000 + n)::text, 4, '0') || '-' || LPAD((n)::text, 4, '0') as telefone,
    'agente' || n::text || '@transito.gov.br' as email,
    true as ativo,
    NOW() - (n || ' days')::interval as created_at,
    NOW() as updated_at
FROM generate_series(1, 15) as n;

-- Inserir multas
INSERT INTO multas (auto_infracao, municipio_id, veiculo_id, agente_id, infracao_id, usuario_criador_id, status, placa, data_infracao, hora_infracao, local_infracao, latitude, longitude, valor_multa, pontos_cnh, observacoes, created_at, updated_at)
SELECT 
    (2024 + (n / 100))::text || LPAD(n::text, 6, '0') as auto_infracao,
    ((n % 3) + 1)::int as municipio_id,
    ((n % 50) + 1)::int as veiculo_id,
    ((n % 15) + 1)::int as agente_id,
    ((n % 5) + 1)::int as infracao_id,
    ((n % 3) + 1)::int as usuario_criador_id,
    (ARRAY['rascunho', 'registrada', 'enviada_orgao_externo', 'notificada', 'em_recurso'])[((n % 5) + 1)] as status,
    v.placa,
    (NOW() - (n || ' days')::interval)::date as data_infracao,
    LPAD((n % 24)::text, 2, '0') || ':' || LPAD((n % 60)::text, 2, '0') || ':00' as hora_infracao,
    'Av. Principal, ' || (100 + n)::text as local_infracao,
    -23.5505 + (n::float / 1000) as latitude,
    -46.6333 + (n::float / 1000) as longitude,
    inf.valor as valor_multa,
    inf.pontos as pontos_cnh,
    'Infração registrada conforme CTB' as observacoes,
    NOW() - (n || ' days')::interval as created_at,
    NOW() as updated_at
FROM generate_series(1, 100) as n
CROSS JOIN LATERAL (SELECT id, placa FROM veiculos WHERE id = ((n % 50) + 1) LIMIT 1) v
CROSS JOIN LATERAL (SELECT valor, pontos FROM infracoes WHERE id = ((n % 5) + 1) LIMIT 1) inf;
