import { useEffect, useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { useAuthStore } from '@/stores/authStore';
import axios from 'axios';

const API_URL = 'http://localhost:8000/api';

interface Recurso {
  id: number;
  numero_protocolo: string;
  auto_infracao: string;
  tipo: string;
  status: string;
  decisao: string | null;
  data_protocolo: string;
  data_julgamento: string | null;
  criador_nome: string;
}

const tipoLabels: Record<string, string> = {
  defesa_previa: 'Defesa Prévia',
  recurso_primeira_instancia: 'Recurso 1ª Instância (JARI)',
  recurso_segunda_instancia: 'Recurso 2ª Instância (CETRAN)',
};

const statusLabels: Record<string, string> = {
  em_analise: 'Em Análise',
  analisado: 'Analisado',
};

export default function Recursos() {
  const { token, user } = useAuthStore();
  const [recursos, setRecursos] = useState<Recurso[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchRecursos();
  }, []);

  const fetchRecursos = async () => {
    try {
      const response = await axios.get(`${API_URL}/recursos`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setRecursos(response.data.data);
    } catch (error) {
      console.error('Erro ao carregar recursos:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleJulgar = async (id: number) => {
    const decisao = window.confirm('Deferir recurso?') ? 'deferido' : 'indeferido';
    const justificativa = window.prompt('Justificativa da decisão:');
    
    if (!justificativa) return;

    try {
      await axios.post(`${API_URL}/recursos/${id}/julgar`, {
        decisao,
        justificativa_decisao: justificativa,
      }, {
        headers: { Authorization: `Bearer ${token}` },
      });
      
      alert('Recurso julgado com sucesso!');
      fetchRecursos();
    } catch (error: any) {
      alert(error.response?.data?.message || 'Erro ao julgar recurso');
    }
  };

  if (loading) return <div className="p-8">Carregando...</div>;

  const podeJulgar = user?.perfil === 'administrador' || user?.perfil === 'gestor';

  return (
    <div className="p-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold">Recursos Administrativos</h1>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Lista de Recursos</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b">
                  <th className="text-left py-2">Protocolo</th>
                  <th className="text-left py-2">Auto Infração</th>
                  <th className="text-left py-2">Tipo</th>
                  <th className="text-left py-2">Status</th>
                  <th className="text-left py-2">Decisão</th>
                  <th className="text-left py-2">Data Protocolo</th>
                  <th className="text-left py-2">Criado Por</th>
                  {podeJulgar && <th className="text-left py-2">Ações</th>}
                </tr>
              </thead>
              <tbody>
                {recursos.map((recurso) => (
                  <tr key={recurso.id} className="border-b hover:bg-gray-50">
                    <td className="py-3 font-mono">{recurso.numero_protocolo}</td>
                    <td className="py-3 font-mono">{recurso.auto_infracao}</td>
                    <td className="py-3">{tipoLabels[recurso.tipo]}</td>
                    <td className="py-3">
                      <span className={`px-2 py-1 rounded text-xs ${
                        recurso.status === 'em_analise' 
                          ? 'bg-yellow-100 text-yellow-800' 
                          : 'bg-blue-100 text-blue-800'
                      }`}>
                        {statusLabels[recurso.status]}
                      </span>
                    </td>
                    <td className="py-3">
                      {recurso.decisao ? (
                        <span className={`px-2 py-1 rounded text-xs ${
                          recurso.decisao === 'deferido' 
                            ? 'bg-green-100 text-green-800' 
                            : 'bg-red-100 text-red-800'
                        }`}>
                          {recurso.decisao.toUpperCase()}
                        </span>
                      ) : '-'}
                    </td>
                    <td className="py-3">{new Date(recurso.data_protocolo).toLocaleDateString()}</td>
                    <td className="py-3">{recurso.criador_nome}</td>
                    {podeJulgar && (
                      <td className="py-3">
                        {recurso.status === 'em_analise' && (
                          <Button 
                            size="sm" 
                            onClick={() => handleJulgar(recurso.id)}
                          >
                            Julgar
                          </Button>
                        )}
                      </td>
                    )}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
