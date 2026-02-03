# Guia de Implementação - SIM

## 🚀 Como rodar o projeto completo

### ⚡ Instalação Express (Recomendado)

```bash
# 1. Configurar .env
cp sim-backend/.env.example sim-backend/.env
cp sim-frontend/.env.example sim-frontend/.env

# 2. Subir TUDO de uma vez! 🎉
docker-compose up --build -d

# 3. Aguardar inicialização (~2 minutos)
# O sistema faz TUDO automaticamente:
# ✅ Instala dependências
# ✅ Cria banco de dados
# ✅ Roda migrations
# ✅ Popula dados iniciais
# ✅ Inicia todos os serviços

# 4. Verificar se está rodando
docker-compose ps
```

**Pronto!** Acesse:
- 🌐 **Frontend**: http://localhost:5173
- 🔌 **Backend API**: http://localhost:8000/api
- 📊 **Database**: localhost:5432

**Credenciais padrão:**
- Admin: `admin@sim.gov.br` / `senha123`
- Gestor: `gestor@sim.gov.br` / `senha123`
- Operador: `operador@sim.gov.br` / `senha123`

---

### 🔧 Instalação Manual (Sem Docker)

<details>
<summary>Clique para expandir</summary>

#### 1. Backend (Laravel)

```bash
cd sim-backend

# Instalar dependências
composer install

# Configurar .env
cp .env.example .env
php artisan key:generate

# Subir banco (PostgreSQL e Redis devem estar rodando)
php artisan migrate
php artisan db:seed

# Iniciar servidor
php artisan serve

# Em outro terminal, iniciar fila
php artisan queue:work
```

#### 2. Frontend (React)

```bash
cd sim-frontend

# Instalar dependências
npm install

# Configurar .env
cp .env.example .env

# Iniciar dev server
npm run dev
```

</details>

---

## 📋 Checklist de Produção

### Antes de ir para produção:

- [ ] Alterar todas as senhas padrão
- [ ] Configurar variáveis de ambiente (.env)
- [ ] Ativar HTTPS/SSL
- [ ] Configurar firewall
- [ ] Backup automático do banco
- [ ] Monitoramento (logs, métricas)
- [ ] Rate limiting configurado
- [ ] Tokens de integração (Detran)
- [ ] Testes de carga
- [ ] Revisão de segurança

---

## 🔧 Comandos Úteis

### Laravel

```bash
# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Rodar filas
php artisan queue:work redis

# Gerar relatório de rotas
php artisan route:list

# Criar migration
php artisan make:migration create_table_name

# Criar model
php artisan make:model ModelName -m

# Rodar testes
php artisan test
```

### React

```bash
# Build de produção
npm run build

# Preview do build
npm run preview

# Lint
npm run lint
```

---

## 📁 Estrutura de Arquivos Importantes

### Backend

```
sim-backend/
├── app/
│   ├── Enums/           ← Enumerações tipadas
│   ├── DTOs/            ← Data Transfer Objects
│   ├── Services/        ← Lógica de negócio
│   ├── Repositories/    ← Acesso a dados
│   ├── Models/          ← Eloquent Models
│   ├── Observers/       ← Auditoria automática
│   ├── Policies/        ← Autorização
│   └── Jobs/            ← Processamento assíncrono
├── database/
│   ├── migrations/      ← Schema do banco
│   └── seeders/         ← Dados iniciais
├── routes/
│   └── api.php          ← Rotas da API
└── config/
    └── sim.php          ← Configurações customizadas
```

### Frontend

```
sim-frontend/
├── src/
│   ├── pages/           ← Páginas da aplicação
│   ├── components/      ← Componentes reutilizáveis
│   ├── stores/          ← Estado global (Zustand)
│   ├── lib/             ← Utilitários (api, etc)
│   └── App.tsx          ← Rotas principais
├── public/              ← Arquivos estáticos
└── index.html           ← Entry point
```

---

## 🎓 Conceitos-Chave do Sistema

### 1. Workflow de Multas

```
RASCUNHO → pode editar
    ↓
REGISTRADA → enviável ao Detran
    ↓
ENVIADA_ORGAO_EXTERNO → aguardando processamento
    ↓
NOTIFICADA → pode receber recurso
    ↓
EM_RECURSO → em análise
    ↓
RECURSO_DEFERIDO/INDEFERIDO
    ↓
ENCERRADA → fim do ciclo
```

### 2. Perfis de Usuário

- **Administrador**: Acesso total
- **Gestor**: Gestão de multas e relatórios
- **Operador**: Criação e edição de multas
- **Auditor**: Apenas visualização e auditoria

### 3. Auditoria

Toda ação crítica é registrada automaticamente:
- Quem fez
- O que fez
- Quando fez
- De onde (IP)
- Estado anterior e posterior

---

## 🐛 Troubleshooting

### Backend não inicia

```bash
# Verificar containers
docker-compose ps

# Ver logs do backend
docker-compose logs -f backend

# Ver logs de todos os serviços
docker-compose logs -f

# Recriar containers
docker-compose down
docker-compose up --build -d
```

### Erro de permissão no Laravel

```bash
docker-compose exec backend chmod -R 775 storage
docker-compose exec backend chmod -R 775 bootstrap/cache
docker-compose exec backend chown -R www-data:www-data storage
```

### Containers ficam reiniciando

```bash
# Ver o que está acontecendo
docker-compose logs --tail=100 backend
docker-compose logs --tail=100 postgres

# Verificar saúde dos serviços
docker-compose ps

# Reiniciar serviço específico
docker-compose restart backend
```

### Frontend não conecta na API

- Verificar se backend está rodando
- Verificar `.env` do frontend (VITE_API_URL)
- Verificar CORS no backend (config/cors.php)

### Migrations falhando

```bash
# Resetar banco (CUIDADO em produção!)
docker-compose exec app php artisan migrate:fresh --seed

# Ou rodar migration específica
docker-compose exec app php artisan migrate --path=/database/migrations/2024_01_01_000001_create_municipios_table.php
```

---

## 💡 Dicas de Desenvolvimento

### 1. Adicionar nova entidade

1. Criar migration: `php artisan make:migration create_entidade_table`
2. Criar model: `php artisan make:model Entidade`
3. Criar repository: `app/Repositories/Eloquent/EntidadeRepository.php`
4. Criar service: `app/Services/EntidadeService.php`
5. Criar controller: `app/Http/Controllers/Api/EntidadeController.php`
6. Criar form request: `app/Http/Requests/Entidade/StoreEntidadeRequest.php`
7. Adicionar rotas: `routes/api.php`
8. Adicionar observer se precisar auditoria

### 2. Testar API com curl

```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@sim.gov.br","password":"senha123"}'

# Listar multas
curl http://localhost:8000/api/multas \
  -H "Authorization: Bearer {seu-token}"
```

### 3. Debug no Laravel

```php
// No código
dd($variavel); // Die and dump
dump($variavel); // Dump sem parar
logger()->info('Debug', ['data' => $data]);

// Ver queries SQL
\DB::enableQueryLog();
// ... seu código
dd(\DB::getQueryLog());
```

---

## 📞 Contato

Para dúvidas técnicas ou sugestões, contate a equipe de desenvolvimento.
