import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useAuthStore } from '@/stores/authStore';
import axios from 'axios';
import { FileText, Clock, AlertCircle, CheckCircle, DollarSign, TrendingUp } from 'lucide-react';

const API_URL = 'http://localhost:8000/api';

interface DashboardData {
  totais: {
    total_multas: number;
    multas_mes: number;
    arrecadacao_estimada: number;
    recursos_pendentes: number;
  };
  multas_por_status: any[];
  evolucao_mensal: any[];
  top_infracoes: any[];
  atividades_recentes: any[];
}

export default function Dashboard() {
  const navigate = useNavigate();
  const { token, user } = useAuthStore();
  const [data, setData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchDashboard();
  }, []);

  const fetchDashboard = async () => {
    try {
      const response = await axios.get(`${API_URL}/dashboard`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setData(response.data.data);
    } catch (error) {
      console.error('Erro ao carregar dashboard:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div className="p-8">Carregando...</div>;
  if (!data) return <div className="p-8">Erro ao carregar dados</div>;

  const cards = [
    {
      title: 'Total de Multas',
      value: data.totais.total_multas,
      icon: FileText,
      color: 'blue',
    },
    {
      title: 'Multas no Mês',
      value: data.totais.multas_mes,
      icon: Clock,
      color: 'yellow',
    },
    {
      title: 'Recursos Pendentes',
      value: data.totais.recursos_pendentes,
      icon: AlertCircle,
      color: 'orange',
    },
    {
      title: 'Arrecadação Estimada',
      value: `R$ ${data.totais.arrecadacao_estimada.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`,
      icon: DollarSign,
      color: 'green',
    },
  ];

  return (
    <div className="p-8">
      <div className="mb-8">
        <h1 className="text-3xl font-bold">Dashboard</h1>
        <p className="text-gray-600">Bem-vindo, {user?.nome}</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {cards.map((card) => {
          const Icon = card.icon;
          return (
            <Card key={card.title}>
              <CardContent className="pt-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm text-gray-600">{card.title}</p>
                    <p className="text-2xl font-bold mt-2">{card.value}</p>
                  </div>
                  <div className={`p-3 rounded-full bg-${card.color}-100`}>
                    <Icon className={`text-${card.color}-600`} size={24} />
                  </div>
                </div>
              </CardContent>
            </Card>
          );
        })}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <Card>
          <CardHeader>
            <CardTitle>Multas por Status</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {Object.values(data.multas_por_status || {}).map((item: any) => (
                <div key={item.status} className="flex items-center justify-between">
                  <span className="text-sm capitalize">{item.status.replace(/_/g, ' ')}</span>
                  <span className="font-semibold">{item.total}</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Top 5 Infrações</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {(data.top_infracoes || []).map((item: any) => (
                <div key={item.codigo_ctb} className="flex items-center justify-between">
                  <div className="flex-1">
                    <p className="text-sm font-mono font-semibold">{item.codigo_ctb}</p>
                    <p className="text-xs text-gray-600 truncate">{item.descricao}</p>
                  </div>
                  <span className="ml-4 px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm font-semibold">
                    {item.total}
                  </span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Ações Rápidas</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-2 gap-4">
              <button
                onClick={() => navigate('/multas/nova')}
                className="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors text-center"
              >
                <FileText className="mx-auto mb-2 text-gray-400" size={32} />
                <p className="font-medium text-sm">Nova Multa</p>
              </button>
              <button
                onClick={() => navigate('/multas')}
                className="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors text-center"
              >
                <FileText className="mx-auto mb-2 text-gray-400" size={32} />
                <p className="font-medium text-sm">Consultar Multas</p>
              </button>
              <button
                onClick={() => navigate('/recursos')}
                className="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors text-center"
              >
                <AlertCircle className="mx-auto mb-2 text-gray-400" size={32} />
                <p className="font-medium text-sm">Recursos</p>
              </button>
              <button
                onClick={() => navigate('/veiculos')}
                className="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors text-center"
              >
                <TrendingUp className="mx-auto mb-2 text-gray-400" size={32} />
                <p className="font-medium text-sm">Veículos</p>
              </button>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Atividades Recentes</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3 max-h-64 overflow-y-auto">
              {(data.atividades_recentes || []).map((item: any) => (
                <div key={item.id} className="border-l-2 border-blue-500 pl-3">
                  <p className="text-sm font-semibold">{item.descricao}</p>
                  <p className="text-xs text-gray-600">
                    {item.usuario_nome} • {new Date(item.data_hora).toLocaleString()}
                  </p>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
