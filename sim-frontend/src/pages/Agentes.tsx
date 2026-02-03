import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { useAuthStore } from '@/stores/authStore';
import axios from 'axios';

const API_URL = 'http://localhost:8000/api';

interface Agente {
  id: number;
  matricula: string;
  nome: string;
  cpf: string;
  cargo: string;
  telefone: string;
  email: string;
  ativo: boolean;
}

export default function Agentes() {
  const navigate = useNavigate();
  const { token } = useAuthStore();
  const [agentes, setAgentes] = useState<Agente[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchAgentes();
  }, []);

  const fetchAgentes = async () => {
    try {
      const response = await axios.get(`${API_URL}/agentes`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setAgentes(response.data.data);
    } catch (error) {
      console.error('Erro ao carregar agentes:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div className="p-8">Carregando...</div>;

  return (
    <div className="p-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold">Agentes de Trânsito</h1>
        <Button onClick={() => navigate('/agentes/novo')}>Novo Agente</Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Lista de Agentes</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b">
                  <th className="text-left py-2">Matrícula</th>
                  <th className="text-left py-2">Nome</th>
                  <th className="text-left py-2">Cargo</th>
                  <th className="text-left py-2">Telefone</th>
                  <th className="text-left py-2">Email</th>
                  <th className="text-left py-2">Status</th>
                </tr>
              </thead>
              <tbody>
                {agentes.map((agente) => (
                  <tr key={agente.id} className="border-b hover:bg-gray-50">
                    <td className="py-3 font-mono">{agente.matricula}</td>
                    <td className="py-3">{agente.nome}</td>
                    <td className="py-3">{agente.cargo}</td>
                    <td className="py-3">{agente.telefone || '-'}</td>
                    <td className="py-3">{agente.email || '-'}</td>
                    <td className="py-3">
                      <span className={`px-2 py-1 rounded text-xs ${
                        agente.ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                      }`}>
                        {agente.ativo ? 'Ativo' : 'Inativo'}
                      </span>
                    </td>
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
