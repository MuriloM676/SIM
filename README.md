# SIM - Sistema Integrado de Infrações e Multas

Sistema web governamental completo para gestão de infrações de trânsito e multas, desenvolvido com foco em segurança, auditoria, compliance LGPD e alta disponibilidade.

## 📋 Índice

- [Visão Geral](#visão-geral)
- [Arquitetura](#arquitetura)
- [Tecnologias](#tecnologias)
- [Instalação](#instalação)
- [Modelagem do Banco](#modelagem-do-banco)
- [API Endpoints](#api-endpoints)
- [Decisões Técnicas](#decisões-técnicas)
- [Segurança e LGPD](#segurança-e-lgpd)
- [Testes](#testes)

---

## 🎯 Visão Geral

O SIM é um sistema crítico de Estado desenvolvido para múltiplos municípios, permitindo:

- ✅ Registro e gestão completa de multas de trânsito
- ✅ Controle de workflow com transições de estado validadas
- ✅ Gestão de recursos administrativos
- ✅ Integração com sistemas externos (Detran/RENAVAM)
- ✅ Auditoria completa e imutável de todas as ações
- ✅ Compliance total com LGPD
- ✅ RBAC (Role-Based Access Control) granular
- ✅ Multi-tenancy lógico por município

---

## 🏗️ Arquitetura

### Camadas da Aplicação

```
┌─────────────────────────────────────┐
│         FRONTEND (React)            │
│   - Interface administrativa        │
│   - Autenticação JWT               │
│   - Controle de acesso por perfil  │
└─────────────────────────────────────┘
                 ↓ REST API
┌─────────────────────────────────────┐
│         BACKEND (Laravel)           │
├─────────────────────────────────────┤
│  Controllers → Services → Repos     │
│  DTOs | Enums | Policies           │
│  Observers | Jobs | Events         │
└─────────────────────────────────────┘
                 ↓
┌──────────┬──────────┬──────────────┐
│PostgreSQL│  Redis   │ File Storage │
│ (Dados)  │(Cache/Q) │ (Evidências) │
└──────────┴──────────┴──────────────┘
```

### Padrões Utilizados

- **Clean Architecture**: Separação clara de responsabilidades
- **Repository Pattern**: Abstração da camada de dados
- **Service Layer**: Lógica de negócio centralizada
- **DTO Pattern**: Transferência de dados type-safe
- **Observer Pattern**: Auditoria automática
- **Strategy Pattern**: Workflow de estados

---

## 🛠️ Tecnologias

### Backend
- **PHP 8.3** - Linguagem base
- **Laravel 11** - Framework
- **PostgreSQL 16** - Banco de dados
- **Redis 7** - Cache e filas
- **Laravel Sanctum** - Autenticação API
- **Docker** - Containerização

### Frontend
- **React 18** - Biblioteca UI
- **TypeScript** - Type safety
- **Vite** - Build tool
- **Tailwind CSS** - Estilização
- **React Query** - Cache e state management
- **Zustand** - Estado global
- **React Hook Form + Zod** - Validação de formulários

---

## 🚀 Instalação

### Pré-requisitos

- Docker e Docker Compose
- Git

### 🎯 Instalação Rápida (Projeto Completo)

```bash
# Clone o repositório
git clone <repo-url>
cd SIM

# Configure os .env
cp sim-backend/.env.example sim-backend/.env
cp sim-frontend/.env.example sim-frontend/.env

# Suba TUDO com um único comando! 🚀
docker-compose up --build -d

# Aguarde ~2 minutos para inicialização completa
# O sistema irá automaticamente:
# ✅ Criar banco de dados
# ✅ Rodar migrations
# ✅ Popular dados iniciais (seeders)
# ✅ Iniciar backend e frontend
```

**Pronto!** O sistema estará disponível:
- 🌐 Frontend: http://localhost:5173
- 🔌 Backend API: http://localhost:8000
- 🗄️ PostgreSQL: localhost:5432
- 📦 Redis: localhost:6379

### 📋 Verificar se está rodando

```bash
# Ver status dos containers
docker-compose ps

# Ver logs
docker-compose logs -f

# Ver logs de um serviço específico
docker-compose logs -f backend
docker-compose logs -f frontend
```

### 🛑 Parar o sistema

```bash
# Parar containers (mantém dados)
docker-compose stop

# Parar e remover containers (mantém volumes/dados)
docker-compose down

# Remover TUDO incluindo volumes/dados
docker-compose down -v
```

### 🔄 Reconstruir após mudanças no código

```bash
# Rebuild e restart
docker-compose up --build -d

# Rebuild de um serviço específico
docker-compose up --build -d backend
```

### Credenciais Padrão

Após rodar o seeder, você terá:

```
Admin:     admin@sim.gov.br     / senha123
Gestor:    gestor@sim.gov.br    / senha123
Operador:  operador@sim.gov.br  / senha123
```

---

## 💾 Modelagem do Banco

### Diagrama ER Simplificado

```
municipios (multi-tenancy)
    ↓
usuarios (RBAC)
    ↓
┌─────────┬─────────┬─────────┐
│ agentes │veiculos │infracoes│
└─────────┴─────────┴─────────┘
         ↓          ↓         ↓
         └──────→ multas ←────┘
                    ↓
    ┌──────────────┼──────────────┐
    ↓              ↓              ↓
historico     recursos      evidencias
    ↓
auditorias (imutável)
integracoes_log
```

### Tabelas Principais

#### municipios
Multi-tenancy lógico. Cada município é independente.
- `id`, `codigo_ibge`, `nome`, `uf`, `cnpj`, `ativo`

#### usuarios
Autenticação e autorização.
- `id`, `municipio_id`, `nome`, `email`, `cpf`, `perfil`, `ativo`

#### multas (核心 - Core)
Registro central de multas.
- `id`, `auto_infracao` (único), `municipio_id`, `infracao_id`, `veiculo_id`, `agente_id`
- `placa`, `data_infracao`, `local_infracao`, `status`, `valor_multa`, `pontos_cnh`
- **Status**: rascunho → registrada → enviada_orgao_externo → notificada → [em_recurso] → encerrada

#### multas_historico
Log de todas as transições de status (imutável).
- `multa_id`, `usuario_id`, `status_anterior`, `status_novo`, `justificativa`, `ip`, `data_transicao`

#### auditorias ⚠️ IMUTÁVEL
**Não pode ser atualizada ou deletada após inserção.**
- Registra TODAS as ações críticas do sistema
- Campos: `usuario_id`, `tipo_acao`, `entidade`, `entidade_id`, `dados_antes`, `dados_depois`, `ip`, `user_agent`
- Compliance LGPD: marca ações críticas (visualização de dados pessoais)

---

## 🔌 API Endpoints

### Autenticação

```http
POST /api/login
Body: { email, password }
Response: { user, token }

POST /api/logout
Headers: Authorization: Bearer {token}

GET /api/me
Headers: Authorization: Bearer {token}
```

### Multas

```http
GET /api/multas
Query: ?status=registrada&placa=ABC1234&per_page=15

POST /api/multas
Body: CreateMultaDTO

GET /api/multas/{id}

PUT /api/multas/{id}
Body: UpdateMultaDTO

PATCH /api/multas/{id}/status
Body: { status, justificativa? }

POST /api/multas/{id}/cancel
Body: { justificativa }

POST /api/multas/{id}/send-detran
```

### Auditoria

```http
GET /api/auditoria
Query: ?entidade=multas&entidade_id=123&usuario_id=1

GET /api/auditoria/{id}
```

### Resposta Padrão

```json
{
  "success": true,
  "message": "Operação realizada com sucesso",
  "data": { ... },
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```

### Códigos de Status

- `200` - Sucesso
- `201` - Criado com sucesso
- `401` - Não autenticado
- `403` - Sem permissão
- `404` - Não encontrado
- `422` - Erro de validação
- `503` - Erro de integração externa

---

## 🎨 Decisões Técnicas

### Por que Laravel?

✅ **Maduro e estável**: Framework consolidado em ambiente corporativo  
✅ **Eloquent ORM**: Facilita relações complexas  
✅ **Queues**: Jobs assíncronos nativos  
✅ **Sanctum**: Autenticação simples e segura  
✅ **Observers**: Auditoria automática  
✅ **Policies**: Autorização granular  
✅ **Validação robusta**: Form Requests + Rules

### Arquitetura Limpa

1. **Controllers**: Apenas recebem requisições e delegam
2. **Services**: Contêm toda a lógica de negócio
3. **Repositories**: Abstraem acesso a dados (fácil troca de ORM)
4. **DTOs**: Type-safety entre camadas
5. **Enums**: Estados e constantes fortemente tipados

### Auditoria Imutável

- Observer registra automaticamente mudanças
- Tabela `auditorias` não permite UPDATE ou DELETE
- Dados sensíveis são sanitizados (senhas removidas)
- Log de acesso a dados pessoais (LGPD)

### Workflow de Estados

```php
MultaStatus::RASCUNHO->transicoesPossiveis()
// → [REGISTRADA, CANCELADA]

$multa->status->podeTransitarPara(MultaStatus::NOTIFICADA)
// → false (transição inválida)
```

Garante integridade do fluxo e previne estados inválidos.

### Integrações Assíncronas

- Jobs com retry automático (3 tentativas)
- Backoff exponencial: 1min → 5min → 15min
- Log completo de requests/responses
- Falhas notificam gestor

### Multi-Tenancy Lógico

- Cada município vê apenas seus dados
- Políticas Laravel verificam `municipio_id`
- Administradores têm acesso global

---

## 🔒 Segurança e LGPD

### Autenticação e Autorização

✅ **Tokens JWT** via Laravel Sanctum  
✅ **RBAC granular**: Perfis com permissões específicas  
✅ **Políticas Laravel**: Autorização em cada ação  
✅ **Middleware de município**: Valida acesso por tenant  
✅ **Rate limiting**: Proteção contra brute force

### Compliance LGPD

✅ **Auditoria de acessos**: Log de quem viu dados pessoais  
✅ **Retenção configurável**: Dados mantidos pelo prazo legal  
✅ **Anonimização**: Possibilidade de anonimizar dados antigos  
✅ **Portabilidade**: Export de dados do cidadão  
✅ **Transparência**: Cidadão pode ver quem acessou seus dados

### Validações

✅ **Form Requests**: Validação em todas as entradas  
✅ **Sanitização**: Dados limpos antes de persistir  
✅ **Mass Assignment Protection**: Fillable/Guarded nos Models  
✅ **SQL Injection**: Protegido pelo Eloquent  
✅ **XSS**: Escapamento automático no frontend

### Integridade de Dados

✅ **Multas não podem ser deletadas**, apenas canceladas  
✅ **Histórico imutável** de mudanças  
✅ **Hash SHA256** de evidências (detecção de adulteração)  
✅ **Soft Deletes**: Recuperação de registros

---

## 🧪 Testes

### Backend

```bash
# Rodar todos os testes
docker-compose exec app php artisan test

# Testes com coverage
docker-compose exec app php artisan test --coverage
```

### Estrutura de Testes

```
tests/
├── Unit/
│   ├── Services/
│   │   ├── MultaServiceTest.php
│   │   └── WorkflowServiceTest.php
│   └── DTOs/
│       └── CreateMultaDTOTest.php
└── Feature/
    ├── MultaTest.php
    ├── AuthTest.php
    └── AuditoriaTest.php
```

### Exemplos de Testes

```php
// Teste de workflow
public function test_nao_pode_transitar_de_rascunho_para_notificada()
{
    $multa = Multa::factory()->create([
        'status' => MultaStatus::RASCUNHO
    ]);

    $this->expectException(BusinessException::class);
    
    $this->multaService->mudarStatus(
        $multa->id, 
        MultaStatus::NOTIFICADA
    );
}

// Teste de auditoria
public function test_registro_de_auditoria_ao_criar_multa()
{
    $this->actingAs($this->usuario);
    
    $response = $this->postJson('/api/multas', $multaData);
    
    $this->assertDatabaseHas('auditorias', [
        'tipo_acao' => 'criacao',
        'entidade' => 'multas',
        'usuario_id' => $this->usuario->id
    ]);
}
```

---

## 📊 Performance e Escalabilidade

### Otimizações

✅ **Eager Loading**: `with()` para evitar N+1 queries  
✅ **Índices**: Todas as foreign keys e campos de busca  
✅ **Cache Redis**: Dados estáticos (infrações, municípios)  
✅ **Filas**: Processamento assíncrono de integrações  
✅ **Paginação**: Todas as listagens paginadas

### Monitoramento

- Logs estruturados (JSON)
- Métricas de performance de APIs externas
- Alertas para filas travadas
- Dashboard de saúde do sistema

---

## 📝 Próximos Passos

### Features Pendentes

- [ ] Módulo de recursos administrativos completo
- [ ] Upload e gestão de evidências (fotos)
- [ ] Geração de relatórios (PDF)
- [ ] Notificações por e-mail
- [ ] Dashboard analytics avançado
- [ ] Exportação de dados (LGPD)
- [ ] API pública para consultas
- [ ] App mobile (futuro)

### Melhorias Técnicas

- [ ] Testes E2E com Cypress
- [ ] CI/CD com GitHub Actions
- [ ] Monitoramento com Sentry
- [ ] APM com New Relic
- [ ] Documentação OpenAPI/Swagger
- [ ] Cache distribuído (Redis Cluster)

---

## 👥 Contribuindo

Este é um sistema governamental. Contribuições devem seguir:

1. PSR-12 (PHP)
2. TypeScript strict mode
3. Testes para novas features
4. Documentação atualizada

---

## 📄 Licença

Proprietary - Uso restrito a órgãos públicos autorizados.

---

## 📞 Suporte

Para suporte técnico, abra uma issue ou contate a equipe de TI do município.

---

**Desenvolvido com ❤️ para o setor público brasileiro**
