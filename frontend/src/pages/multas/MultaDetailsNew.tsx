import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { useAuthStore } from '@/stores/authStore';
import EvidenciaUpload from '@/components/EvidenciaUpload';
import RecursoForm from '@/components/RecursoForm';
import axios from 'axios';
import { toast } from 'sonner';
import { ArrowLeft, Edit, Send, XCircle, Download, FileText, Image } from 'lucide-react';

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
  latitude?: number;
  longitude?: number;
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
  const [activeTab, setActiveTab] = useState<'detalhes' | 'evidencias' | 'recursos'>('detalhes');

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
      toast.success('Status atualizado com sucesso');
      fetchMulta();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Erro ao atualizar status');
    }
  };

  const handleDownloadPDF = () => {
    window.open(`${API_URL}/relatorios/multa/${id}/pdf`, '_blank');
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-96">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  if (!multa) return null;

  return (
    <div className="container mx-auto py-6 px-4">
      <div className="mb-6 flex justify-between items-center">
        <div className="flex items-center gap-4">
          <Button variant="outline" size="sm" onClick={() => navigate('/multas')}>
            <ArrowLeft size={16} className="mr-2" />
            Voltar
          </Button>
          <div>
            <h1 className="text-2xl font-bold">Auto de Infração #{multa.auto_infracao}</h1>
            <p className="text-gray-600">Placa: {multa.placa}</p>
          </div>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" onClick={handleDownloadPDF}>
            <Download size={16} className="mr-2" />
            Baixar PDF
          </Button>
          <Button onClick={() => navigate(`/multas/${id}/editar`)}>
            <Edit size={16} className="mr-2" />
            Editar
          </Button>
        </div>
      </div>

      {/* Tabs */}
      <div className="border-b mb-6">
        <div className="flex gap-6">
          <button
            onClick={() => setActiveTab('detalhes')}
            className={`pb-3 px-1 border-b-2 transition ${
              activeTab === 'detalhes'
                ? 'border-blue-600 text-blue-600 font-medium'
                : 'border-transparent text-gray-600 hover:text-gray-900'
            }`}
          >
            <FileText className="inline w-4 h-4 mr-2" />
            Detalhes
          </button>
          <button
            onClick={() => setActiveTab('evidencias')}
            className={`pb-3 px-1 border-b-2 transition ${
              activeTab === 'evidencias'
                ? 'border-blue-600 text-blue-600 font-medium'
                : 'border-transparent text-gray-600 hover:text-gray-900'
            }`}
          >
            <Image className="inline w-4 h-4 mr-2" />
            Evidências
          </button>
          <button
            onClick={() => setActiveTab('recursos')}
            className={`pb-3 px-1 border-b-2 transition ${
              activeTab === 'recursos'
                ? 'border-blue-600 text-blue-600 font-medium'
                : 'border-transparent text-gray-600 hover:text-gray-900'
            }`}
          >
            <FileText className="inline w-4 h-4 mr-2" />
            Recursos
          </button>
        </div>
      </div>

      {/* Conteúdo das Tabs */}
      {activeTab === 'detalhes' && (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2 space-y-6">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between">
                <CardTitle>Status da Multa</CardTitle>
                <Badge className={statusColors[multa.status]}>
                  {statusLabels[multa.status]}
                </Badge>
              </CardHeader>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Dados da Infração</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <p className="text-sm text-gray-600">Código CTB</p>
                    <p className="font-semibold">{multa.codigo_ctb}</p>
                  </div>
                  <div>
                    <p className="text-sm text-gray-600">Gravidade</p>
                    <p className="font-semibold capitalize">{multa.gravidade}</p>
                  </div>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Descrição</p>
                  <p className="font-semibold">{multa.infracao_descricao}</p>
                </div>
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <p className="text-sm text-gray-600">Valor</p>
                    <p className="font-semibold text-green-600">
                      R$ {Number(multa.valor_multa || 0).toFixed(2)}
                    </p>
                  </div>
                  <div>
                    <p className="text-sm text-gray-600">Pontos CNH</p>
                    <p className="font-semibold">{multa.pontos_cnh}</p>
                  </div>
                </div>
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <p className="text-sm text-gray-600">Data</p>
                    <p className="font-semibold">
                      {new Date(multa.data_infracao).toLocaleDateString('pt-BR')}
                    </p>
                  </div>
                  <div>
                    <p className="text-sm text-gray-600">Hora</p>
                    <p className="font-semibold">{multa.hora_infracao}</p>
                  </div>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Local</p>
                  <p className="font-semibold">{multa.local_infracao}</p>
                </div>
                {multa.observacoes && (
                  <div>
                    <p className="text-sm text-gray-600">Observações</p>
                    <p className="font-semibold">{multa.observacoes}</p>
                  </div>
                )}
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Dados do Veículo</CardTitle>
              </CardHeader>
              <CardContent className="space-y-2">
                <div>
                  <p className="text-sm text-gray-600">Placa</p>
                  <p className="font-semibold">{multa.veiculo_placa}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Proprietário</p>
                  <p className="font-semibold">{multa.proprietario_nome}</p>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Agente Autuador</CardTitle>
              </CardHeader>
              <CardContent className="space-y-2">
                <div>
                  <p className="text-sm text-gray-600">Nome</p>
                  <p className="font-semibold">{multa.agente_nome}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Matrícula</p>
                  <p className="font-semibold">{multa.agente_matricula}</p>
                </div>
              </CardContent>
            </Card>
          </div>

          <div className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Ações</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                {multa.status === 'registrada' && (
                  <Button 
                    className="w-full" 
                    onClick={() => handleUpdateStatus('enviada_orgao_externo')}
                  >
                    <Send size={16} className="mr-2" />
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

            {multa.latitude && multa.longitude && (
              <Card>
                <CardHeader>
                  <CardTitle>Localização</CardTitle>
                </CardHeader>
                <CardContent>
                  <a
                    href={`https://www.google.com/maps?q=${multa.latitude},${multa.longitude}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-blue-600 hover:underline text-sm"
                  >
                    Ver no Google Maps →
                  </a>
                </CardContent>
              </Card>
            )}
          </div>
        </div>
      )}

      {activeTab === 'evidencias' && (
        <div className="max-w-4xl">
          <EvidenciaUpload multaId={Number(id)} onUploadSuccess={fetchMulta} />
        </div>
      )}

      {activeTab === 'recursos' && (
        <div className="max-w-2xl">
          <RecursoForm multaId={Number(id)} onSuccess={fetchMulta} />
        </div>
      )}
    </div>
  );
}
