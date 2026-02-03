import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { useAuthStore } from '@/stores/authStore';
import axios from 'axios';
import { Eye, Plus } from 'lucide-react';

const API_URL = 'http://localhost:8000/api';

interface Multa {
  id: number;
  auto_infracao: string;
  status: string;
  placa: string;
  data_infracao: string;
  valor_multa: number;
  codigo_ctb: string;
  infracao_descricao: string;
  agente_nome: string;
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

export default function MultasList() {
  const navigate = useNavigate();
  const { token } = useAuthStore();
  const [multas, setMultas] = useState<Multa[]>([]);
  const [loading, setLoading] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  
  const [filters, setFilters] = useState({
    status: '',
    data_inicio: '',
    data_fim: '',
  });

  useEffect(() => {
    fetchMultas();
  }, [currentPage, filters]);

  const fetchMultas = async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams({
        page: currentPage.toString(),
        per_page: '15',
        ...Object.fromEntries(
          Object.entries(filters).filter(([_, v]) => v !== '')
        ),
      });

      const response = await axios.get(`${API_URL}/multas?${params}`, {
        headers: { Authorization: `Bearer ${token}` },
      });

      setMultas(response.data.data);
      setTotalPages(response.data.meta.last_page);
    } catch (error) {
      console.error('Erro ao carregar multas:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="p-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold">Multas</h1>
        <Button onClick={() => navigate('/multas/nova')}>
          <Plus size={16} className="mr-2" />
          Nova Multa
        </Button>
      </div>

      <Card className="mb-6">
        <CardHeader>
          <CardTitle>Filtros</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-4 gap-4">
            <div>
              <Label htmlFor="status">Status</Label>
              <Select
                id="status"
                value={filters.status}
                onChange={(e) => {
                  setFilters({ ...filters, status: e.target.value });
                  setCurrentPage(1);
                }}
              >
                <option value="">Todos</option>
                <option value="rascunho">Rascunho</option>
                <option value="registrada">Registrada</option>
                <option value="enviada_orgao_externo">Enviada ao Órgão</option>
                <option value="notificada">Notificada</option>
                <option value="em_recurso">Em Recurso</option>
                <option value="deferida">Deferida</option>
                <option value="indeferida">Indeferida</option>
                <option value="encerrada">Encerrada</option>
                <option value="cancelada">Cancelada</option>
              </Select>
            </div>

            <div>
              <Label htmlFor="data_inicio">Data Início</Label>
              <Input
                id="data_inicio"
                type="date"
                value={filters.data_inicio}
                onChange={(e) => {
                  setFilters({ ...filters, data_inicio: e.target.value });
                  setCurrentPage(1);
                }}
              />
            </div>

            <div>
              <Label htmlFor="data_fim">Data Fim</Label>
              <Input
                id="data_fim"
                type="date"
                value={filters.data_fim}
                onChange={(e) => {
                  setFilters({ ...filters, data_fim: e.target.value });
                  setCurrentPage(1);
                }}
              />
            </div>

            <div className="flex items-end">
              <Button
                variant="outline"
                onClick={() => {
                  setFilters({
                    status: '',
                    data_inicio: '',
                    data_fim: '',
                  });
                  setCurrentPage(1);
                }}
              >
                Limpar Filtros
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Lista de Multas</CardTitle>
        </CardHeader>
        <CardContent>
          {loading ? (
            <div className="text-center py-8">Carregando...</div>
          ) : (
            <>
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead>
                    <tr className="border-b">
                      <th className="text-left py-2">Auto Infração</th>
                      <th className="text-left py-2">Placa</th>
                      <th className="text-left py-2">Infração</th>
                      <th className="text-left py-2">Data</th>
                      <th className="text-left py-2">Agente</th>
                      <th className="text-left py-2">Valor</th>
                      <th className="text-left py-2">Status</th>
                      <th className="text-left py-2">Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    {multas.map((multa) => (
                      <tr key={multa.id} className="border-b hover:bg-gray-50">
                        <td className="py-3 font-mono font-semibold">{multa.auto_infracao}</td>
                        <td className="py-3 font-mono">{multa.placa}</td>
                        <td className="py-3">
                          <div>
                            <p className="font-mono text-sm font-semibold">{multa.codigo_ctb}</p>
                            <p className="text-xs text-gray-600 truncate max-w-xs">{multa.infracao_descricao}</p>
                          </div>
                        </td>
                        <td className="py-3">{new Date(multa.data_infracao).toLocaleDateString()}</td>
                        <td className="py-3 text-sm">{multa.agente_nome}</td>
                        <td className="py-3 font-semibold text-green-600">
                          R$ {multa.valor_multa.toFixed(2)}
                        </td>
                        <td className="py-3">
                          <span className={`px-2 py-1 rounded text-xs font-medium ${statusColors[multa.status]}`}>
                            {statusLabels[multa.status]}
                          </span>
                        </td>
                        <td className="py-3">
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => navigate(`/multas/${multa.id}`)}
                          >
                            <Eye size={14} className="mr-1" />
                            Ver
                          </Button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>

              <div className="flex justify-between items-center mt-4">
                <div className="text-sm text-gray-600">
                  Página {currentPage} de {totalPages}
                </div>
                <div className="flex gap-2">
                  <Button
                    variant="outline"
                    disabled={currentPage === 1}
                    onClick={() => setCurrentPage(currentPage - 1)}
                  >
                    Anterior
                  </Button>
                  <Button
                    variant="outline"
                    disabled={currentPage === totalPages}
                    onClick={() => setCurrentPage(currentPage + 1)}
                  >
                    Próxima
                  </Button>
                </div>
              </div>
            </>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
