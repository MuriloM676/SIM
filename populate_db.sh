#!/bin/bash

echo "🚀 Populando banco de dados..."

# Executar seeder dentro do container
docker exec sim-backend php artisan db:seed --force

echo ""
echo "✅ Dados populados com sucesso!"
echo ""
echo "Acesse: http://localhost:5173"
echo "Login: admin@sim.gov.br / senha123"
