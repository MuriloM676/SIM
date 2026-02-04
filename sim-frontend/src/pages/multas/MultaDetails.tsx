import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { useAuthStore } from '@/stores/authStore';
import axios from 'axios';
import { toast } from 'sonner';
import { ArrowLeft, Edit, Send, XCircle } from 'lucide-react';

const API_URL = 'http://localhost:8000/api';

interface Multa {
  id: number;
  auto_infracao: string;
  status: string;
  placa: string;
  data_infracao: string;
  hora_infracao: string;
  local_infracao: string;
  valor_multa: number;
  pontos_cnh: number;
  observacoes: string;
  veiculo_placa: string;
  proprietario_nome: string;
  codigo_ctb: string;
  infracao_descricao: string;
  gravidade: string;
  agente_nome: string;
  agente_matricula: string;
  criador_nome: string;
  created_at: string;
}

const statusLabels: Record<string, string> = {
  rascunho: 'Rascunho',
  registrada: 'Registrada',
  enviada_orgao_externo: 'Enviada ao Órgão',
  notificada: 'Notificada',
  em_recurso: 'Em Recurso',
  deferida: 'Deferida',
  indeferida: 'Indeferida',
  encerrada: 'Encerrada',
  cancelada: 'Cancelada',
};

const statusColors: Record<string, string> = {
  rascunho: 'bg-gray-100 text-gray-800',
  registrada: 'bg-blue-100 text-blue-800',
  enviada_orgao_externo: 'bg-purple-100 text-purple-800',
  notificada: 'bg-yellow-100 text-yellow-800',
  em_recurso: 'bg-orange-100 text-orange-800',
  deferida: 'bg-green-100 text-green-800',
  indeferida: 'bg-red-100 text-red-800',
  encerrada: 'bg-gray-100 text-gray-800',
  cancelada: 'bg-red-100 text-red-800',
};

export default function MultaDetails() {
  const navigate = useNavigate();
  const { id } = useParams();
  const { token, user } = useAuthStore();
  const [multa, setMulta] = useState<Multa | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchMulta();
  }, [id]);

  const fetchMulta = async () => {
    try {
      const response = await axios.get(`${API_URL}/multas/${id}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setMulta(response.data.data);
    } catch (error) {
      toast.error('Erro ao carregar multa');
      navigate('/multas');
    } finally {
      setLoading(false);
    }
  };

  const handleUpdateStatus = async (novoStatus: string) => {
    let justificativa = '';
    if (novoStatus === 'cancelada') {
      justificativa = window.prompt('Justificativa para cancelamento:') || '';
      if (!justificativa) {
        toast.error('Justificativa é obrigatória para cancelamento');
        return;
      }
    }

    try {
      await axios.patch(`${API_URL}/multas/${id}/status`, {
        status: novoStatus,
        justificativa,
      }, {
        headers: { Authorization: `Bearer ${token}` },
      });
      toast.success('Status atualizado com sucesso!');
      fetchMulta();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Erro ao atualizar status');
    }
  };

  const handleSendToDetran = async () => {
    if (!window.confirm('Enviar multa para o Detran?')) return;

    try {
      await axios.post(`${API_URL}/multas/${id}/send-detran`, {}, {
        headers: { Authorization: `Bearer ${token}` },
      });
      toast.success('Multa enviada ao Detran!');
      fetchMulta();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Erro ao enviar para Detran');
    }
  };

  if (loading) return <div className="p-8">Carregando...</div>;
  if (!multa) return <div className="p-8">Multa não encontrada</div>;

  const podeEditar = multa.status === 'rascunho' && (user?.perfil === 'administrador' || user?.perfil === 'gestor' || user?.perfil === 'operador');
  const podeEnviarDetran = multa.status === 'registrada' && (user?.perfil === 'administrador' || user?.perfil === 'gestor');

  return (
    <div className="p-8">
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center gap-4">
          <Button variant="outline" onClick={() => navigate('/multas')}>
            <ArrowLeft size={20} />
          </Button>
          <div>
            <h1 className="text-3xl font-bold">{multa.auto_infracao}</h1>
            <p className="text-gray-600">Detalhes da Multa</p>
          </div>
        </div>
        <div className="flex gap-2">
          {podeEditar && (
            <Button onClick={() => navigate(`/multas/${id}/editar`)}>
              <Edit size={16} className="mr-2" />
              Editar
            </Button>
          )}
          {podeEnviarDetran && (
            <Button onClick={handleSendToDetran}>
              <Send size={16} className="mr-2" />
              Enviar ao Detran
            </Button>
          )}
        </div>
      </div>

      <div className="grid grid-cols-3 gap-6">
        <div className="col-span-2 space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Informações da Infração</CardTitle>
            </CardHeader>
            <CardContent className="grid grid-cols-2 gap-4">
              <div>
                <p className="text-sm text-gray-600">Status</p>
                <span className={`inline-block px-3 py-1 rounded-full text-sm font-medium ${statusColors[multa.status]}`}>
                  {statusLabels[multa.status]}
                </span>
              </div>
              <div>
                <p className="text-sm text-gray-600">Placa</p>
                <p className="font-mono font-bold text-lg">{multa.placa}</p>
              </div>
              <div>
                <p className="text-sm text-gray-600">Data/Hora</p>
                <p className="font-semibold">{new Date(multa.data_infracao).toLocaleDateString()} às {multa.hora_infracao}</p>
              </div>
              <div>
                <p className="text-sm text-gray-600">Local</p>
                <p className="font-semibold">{multa.local_infracao}</p>
              </div>
              <div>
                <p className="text-sm text-gray-600">Infração</p>
                <p className="font-semibold">{multa.codigo_ctb}</p>
                <p className="text-sm text-gray-600">{multa.infracao_descricao}</p>
              </div>
              <div>
                <p className="text-sm text-gray-600">Gravidade</p>
                <p className="font-semibold capitalize">{multa.gravidade}</p>
              </div>
              <div>
                <p className="text-sm text-gray-600">Valor</p>
                <p className="font-bold text-xl text-green-600">R$ {Number(multa.valor_multa || 0).toFixed(2)}</p>
              </div>
              <div>
                <p className="text-sm text-gray-600">Pontos CNH</p>
                <p className="font-bold text-xl text-red-600">{multa.pontos_cnh || 0}</p>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Agente e Proprietário</CardTitle>
            </CardHeader>
            <CardContent className="grid grid-cols-2 gap-4">
              <div>
                <p className="text-sm text-gray-600">Agente Autuador</p>
                <p className="font-semibold">{multa.agente_nome}</p>
                <p className="text-sm text-gray-600">Matrícula: {multa.agente_matricula}</p>
              </div>
              <div>
                <p className="text-sm text-gray-600">Proprietário do Veículo</p>
                <p className="font-semibold">{multa.proprietario_nome}</p>
              </div>
            </CardContent>
          </Card>

          {multa.observacoes && (
            <Card>
              <CardHeader>
                <CardTitle>Observações</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-gray-700">{multa.observacoes}</p>
              </CardContent>
            </Card>
          )}
        </div>

        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Ações</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2">
              {multa.status === 'rascunho' && (
                <Button 
                  className="w-full" 
                  onClick={() => handleUpdateStatus('registrada')}
                >
                  Registrar Multa
                </Button>
              )}
              {multa.status === 'registrada' && (
                <Button 
                  className="w-full" 
                  onClick={() => handleUpdateStatus('enviada_orgao_externo')}
                >
                  Enviar ao Órgão
                </Button>
              )}
              {multa.status === 'enviada_orgao_externo' && (
                <Button 
                  className="w-full" 
                  onClick={() => handleUpdateStatus('notificada')}
                >
                  Marcar como Notificada
                </Button>
              )}
              {['rascunho', 'registrada', 'enviada_orgao_externo'].includes(multa.status) && (
                <Button 
                  variant="destructive" 
                  className="w-full"
                  onClick={() => handleUpdateStatus('cancelada')}
                >
                  <XCircle size={16} className="mr-2" />
                  Cancelar Multa
                </Button>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Informações do Sistema</CardTitle>
            </CardHeader>
            <CardContent className="text-sm space-y-2">
              <div>
                <p className="text-gray-600">Criado por</p>
                <p className="font-semibold">{multa.criador_nome}</p>
              </div>
              <div>
                <p className="text-gray-600">Data de criação</p>
                <p className="font-semibold">{new Date(multa.created_at).toLocaleString()}</p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
