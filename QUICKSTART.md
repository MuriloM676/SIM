# 🚀 QUICK START - SIM

## Instalação em 3 comandos

```bash
# 1. Copiar configurações
cp sim-backend/.env.example sim-backend/.env && cp sim-frontend/.env.example sim-frontend/.env

# 2. Subir TUDO
docker-compose up --build -d

# 3. Aguardar ~2 minutos e acessar!
```

## 🌐 Acessar o Sistema

- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000
- **Login**: admin@sim.gov.br / senha123

## 📊 Verificar Status

```bash
docker-compose ps
docker-compose logs -f
```

## 🛑 Parar

```bash
docker-compose down
```

## 🔄 Reiniciar após mudanças

```bash
docker-compose up --build -d
```

---

## 📦 O que o Docker faz automaticamente?

✅ Instala PostgreSQL 16  
✅ Instala Redis 7  
✅ Instala dependências PHP (Composer)  
✅ Instala dependências Node (npm)  
✅ Cria banco de dados  
✅ Roda migrations  
✅ Popula dados iniciais (seeders)  
✅ Inicia Laravel backend  
✅ Inicia React frontend  
✅ Inicia queue worker  

## 🎯 Serviços Rodando

| Serviço | Container | Porta | URL |
|---------|-----------|-------|-----|
| Frontend | sim-frontend | 5173 | http://localhost:5173 |
| Backend | sim-backend | 8000 | http://localhost:8000 |
| PostgreSQL | sim-postgres | 5432 | localhost:5432 |
| Redis | sim-redis | 6379 | localhost:6379 |
| Queue Worker | sim-queue-worker | - | - |

## 🐛 Problemas?

```bash
# Ver logs detalhados
docker-compose logs -f backend
docker-compose logs -f frontend

# Reiniciar do zero
docker-compose down -v
docker-compose up --build -d
```

## 📚 Documentação completa

Ver [README.md](README.md) para documentação detalhada.
