import { useEffect, useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { useAuthStore } from '@/stores/authStore';
import axios from 'axios';
import { Download } from 'lucide-react';

const API_URL = 'http://localhost:8000/api';

interface Auditoria {
  id: number;
  tipo: string;
  entidade: string;
  entidade_id: number;
  descricao: string;
  usuario_nome: string;
  usuario_email: string;
  ip: string;
  data_hora: string;
}

const tipoLabels: Record<string, string> = {
  criacao: 'Criação',
  alteracao: 'Alteração',
  visualizacao: 'Visualização',
  exclusao: 'Exclusão',
  exportacao: 'Exportação',
};

export default function Auditoria() {
  const { token } = useAuthStore();
  const [auditorias, setAuditorias] = useState<Auditoria[]>([]);
  const [loading, setLoading] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  
  const [filters, setFilters] = useState({
    entidade: '',
    tipo: '',
    usuario_id: '',
    data_inicio: '',
    data_fim: '',
  });

  useEffect(() => {
    fetchAuditorias();
  }, [currentPage, filters]);

  const fetchAuditorias = async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams({
        page: currentPage.toString(),
        per_page: '20',
        ...Object.fromEntries(
          Object.entries(filters).filter(([_, v]) => v !== '')
        ),
      });

      const response = await axios.get(`${API_URL}/auditoria?${params}`, {
        headers: { Authorization: `Bearer ${token}` },
      });

      setAuditorias(response.data.data);
      setTotalPages(response.data.meta.last_page);
    } catch (error) {
      console.error('Erro ao carregar auditoria:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleExport = async () => {
    try {
      const params = new URLSearchParams(
        Object.fromEntries(
          Object.entries(filters).filter(([_, v]) => v !== '')
        )
      );

      const response = await axios.get(`${API_URL}/auditoria/export?${params}`, {
        headers: { Authorization: `Bearer ${token}` },
      });

      // Criar CSV
      const csv = [
        ['ID', 'Tipo', 'Entidade', 'Descrição', 'Usuário', 'Email', 'IP', 'Data/Hora'].join(','),
        ...response.data.data.map((a: Auditoria) =>
          [a.id, a.tipo, a.entidade, a.descricao, a.usuario_nome, a.usuario_email, a.ip, a.data_hora].join(',')
        ),
      ].join('\n');

      // Download
      const blob = new Blob([csv], { type: 'text/csv' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `auditoria_${new Date().toISOString()}.csv`;
      a.click();
    } catch (error) {
      console.error('Erro ao exportar:', error);
    }
  };

  return (
    <div className="p-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold">Auditoria</h1>
        <Button onClick={handleExport}>
          <Download size={16} className="mr-2" />
          Exportar CSV
        </Button>
      </div>

      <Card className="mb-6">
        <CardHeader>
          <CardTitle>Filtros</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-5 gap-4">
            <div>
              <Label htmlFor="entidade">Entidade</Label>
              <Select
                id="entidade"
                value={filters.entidade}
                onChange={(e) => {
                  setFilters({ ...filters, entidade: e.target.value });
                  setCurrentPage(1);
                }}
              >
                <option value="">Todas</option>
                <option value="Multa">Multa</option>
                <option value="Recurso">Recurso</option>
                <option value="Usuario">Usuário</option>
                <option value="Veiculo">Veículo</option>
                <option value="Agente">Agente</option>
              </Select>
            </div>

            <div>
              <Label htmlFor="tipo">Tipo</Label>
              <Select
                id="tipo"
                value={filters.tipo}
                onChange={(e) => {
                  setFilters({ ...filters, tipo: e.target.value });
                  setCurrentPage(1);
                }}
              >
                <option value="">Todos</option>
                <option value="criacao">Criação</option>
                <option value="alteracao">Alteração</option>
                <option value="visualizacao">Visualização</option>
                <option value="exclusao">Exclusão</option>
                <option value="exportacao">Exportação</option>
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
                    entidade: '',
                    tipo: '',
                    usuario_id: '',
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
          <CardTitle>Logs de Auditoria</CardTitle>
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
                      <th className="text-left py-2">ID</th>
                      <th className="text-left py-2">Tipo</th>
                      <th className="text-left py-2">Entidade</th>
                      <th className="text-left py-2">Descrição</th>
                      <th className="text-left py-2">Usuário</th>
                      <th className="text-left py-2">IP</th>
                      <th className="text-left py-2">Data/Hora</th>
                    </tr>
                  </thead>
                  <tbody>
                    {auditorias.map((auditoria) => (
                      <tr key={auditoria.id} className="border-b hover:bg-gray-50">
                        <td className="py-3 font-mono">{auditoria.id}</td>
                        <td className="py-3">
                          <span className="px-2 py-1 rounded text-xs bg-blue-100 text-blue-800">
                            {tipoLabels[auditoria.tipo]}
                          </span>
                        </td>
                        <td className="py-3">{auditoria.entidade}</td>
                        <td className="py-3 max-w-md truncate">{auditoria.descricao}</td>
                        <td className="py-3">
                          <div>
                            <p className="font-semibold text-sm">{auditoria.usuario_nome}</p>
                            <p className="text-xs text-gray-600">{auditoria.usuario_email}</p>
                          </div>
                        </td>
                        <td className="py-3 font-mono text-sm">{auditoria.ip}</td>
                        <td className="py-3 text-sm">
                          {new Date(auditoria.data_hora).toLocaleString()}
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
