import React, { useEffect, useState } from 'react';
import { Search, Download, Eye, Filter } from 'lucide-react';
import api from '@/services/api';
import { Button } from '@/components/ui/button';

interface Auditoria {
  id: number;
  usuario_nome: string;
  tipo: string;
  entidade: string;
  entidade_id: number;
  descricao: string;
  ip: string;
  data_hora: string;
}

export default function AuditoriaList() {
  const [auditorias, setAuditorias] = useState<Auditoria[]>([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [filters, setFilters] = useState({
    tipo: '',
    entidade: '',
    usuario: '',
    data_inicio: '',
    data_fim: '',
  });

  useEffect(() => {
    loadAuditorias();
  }, [page, filters]);

  const loadAuditorias = async () => {
    try {
      setLoading(true);
      const response = await api.get('/auditoria', {
        params: { page, ...filters },
      });
      setAuditorias(response.data.data);
      setTotalPages(response.data.meta.last_page);
    } catch (error) {
      console.error('Erro ao carregar auditorias:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleExport = async () => {
    try {
      const response = await api.get('/auditoria/export', {
        params: filters,
        responseType: 'blob',
      });
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `auditoria-${new Date().toISOString()}.csv`);
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (error) {
      alert('Erro ao exportar relatório');
    }
  };

  const getTipoColor = (tipo: string) => {
    const colors: Record<string, string> = {
      criacao: 'bg-green-100 text-green-800',
      edicao: 'bg-blue-100 text-blue-800',
      exclusao: 'bg-red-100 text-red-800',
      upload: 'bg-purple-100 text-purple-800',
      analise: 'bg-orange-100 text-orange-800',
      acesso: 'bg-gray-100 text-gray-800',
    };
    return colors[tipo] || 'bg-gray-100 text-gray-800';
  };

  return (
    <div className="container mx-auto py-6 px-4">
      <div className="flex justify-between items-center mb-6">
        <div>
          <h1 className="text-2xl font-bold">Auditoria do Sistema</h1>
          <p className="text-gray-600">Registro completo de todas as ações</p>
        </div>
        <Button onClick={handleExport} variant="outline">
          <Download className="w-4 h-4 mr-2" />
          Exportar CSV
        </Button>
      </div>

      {/* Filtros */}
      <div className="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div className="flex items-center gap-2 mb-4">
          <Filter className="w-5 h-5 text-gray-600" />
          <h2 className="font-semibold">Filtros</h2>
        </div>
        <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
          <div>
            <label className="block text-sm font-medium mb-1">Tipo</label>
            <select
              value={filters.tipo}
              onChange={(e) => setFilters({ ...filters, tipo: e.target.value })}
              className="w-full border border-gray-300 rounded px-3 py-2"
            >
              <option value="">Todos</option>
              <option value="criacao">Criação</option>
              <option value="edicao">Edição</option>
              <option value="exclusao">Exclusão</option>
              <option value="upload">Upload</option>
              <option value="analise">Análise</option>
              <option value="acesso">Acesso</option>
            </select>
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Entidade</label>
            <select
              value={filters.entidade}
              onChange={(e) => setFilters({ ...filters, entidade: e.target.value })}
              className="w-full border border-gray-300 rounded px-3 py-2"
            >
              <option value="">Todas</option>
              <option value="Multa">Multa</option>
              <option value="Recurso">Recurso</option>
              <option value="Evidencia">Evidência</option>
              <option value="Usuario">Usuário</option>
              <option value="Veiculo">Veículo</option>
            </select>
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Data Início</label>
            <input
              type="date"
              value={filters.data_inicio}
              onChange={(e) => setFilters({ ...filters, data_inicio: e.target.value })}
              className="w-full border border-gray-300 rounded px-3 py-2"
            />
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Data Fim</label>
            <input
              type="date"
              value={filters.data_fim}
              onChange={(e) => setFilters({ ...filters, data_fim: e.target.value })}
              className="w-full border border-gray-300 rounded px-3 py-2"
            />
          </div>
          <div className="flex items-end">
            <Button
              onClick={() => setFilters({ tipo: '', entidade: '', usuario: '', data_inicio: '', data_fim: '' })}
              variant="outline"
              className="w-full"
            >
              Limpar
            </Button>
          </div>
        </div>
      </div>

      {/* Lista */}
      <div className="bg-white rounded-lg shadow-sm overflow-hidden">
        {loading ? (
          <div className="flex justify-center items-center h-64">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
          </div>
        ) : auditorias.length === 0 ? (
          <div className="text-center py-12 text-gray-500">
            <Eye className="w-12 h-12 mx-auto mb-3 opacity-50" />
            <p>Nenhum registro encontrado</p>
          </div>
        ) : (
          <>
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gray-50 border-b">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Data/Hora
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Usuário
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Tipo
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Entidade
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Descrição
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      IP
                    </th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200">
                  {auditorias.map((audit) => (
                    <tr key={audit.id} className="hover:bg-gray-50">
                      <td className="px-6 py-4 whitespace-nowrap text-sm">
                        {new Date(audit.data_hora).toLocaleString('pt-BR')}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        {audit.usuario_nome}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className={`px-2 py-1 text-xs font-medium rounded-full ${getTipoColor(audit.tipo)}`}>
                          {audit.tipo}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm">
                        {audit.entidade} #{audit.entidade_id}
                      </td>
                      <td className="px-6 py-4 text-sm text-gray-900">
                        {audit.descricao}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {audit.ip}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            {/* Paginação */}
            <div className="bg-white px-4 py-3 flex items-center justify-between border-t">
              <div className="flex-1 flex justify-between sm:hidden">
                <Button
                  onClick={() => setPage(page - 1)}
                  disabled={page === 1}
                  variant="outline"
                >
                  Anterior
                </Button>
                <Button
                  onClick={() => setPage(page + 1)}
                  disabled={page === totalPages}
                  variant="outline"
                >
                  Próximo
                </Button>
              </div>
              <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p className="text-sm text-gray-700">
                    Página <span className="font-medium">{page}</span> de{' '}
                    <span className="font-medium">{totalPages}</span>
                  </p>
                </div>
                <div>
                  <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <button
                      onClick={() => setPage(page - 1)}
                      disabled={page === 1}
                      className="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                    >
                      Anterior
                    </button>
                    <button
                      onClick={() => setPage(page + 1)}
                      disabled={page === totalPages}
                      className="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                    >
                      Próximo
                    </button>
                  </nav>
                </div>
              </div>
            </div>
          </>
        )}
      </div>
    </div>
  );
}
