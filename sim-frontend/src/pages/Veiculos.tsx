import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { useAuthStore } from '@/stores/authStore';
import axios from 'axios';

const API_URL = 'http://localhost:8000/api';

interface Veiculo {
  id: number;
  placa: string;
  marca: string;
  modelo: string;
  cor: string;
  ano_fabricacao: number;
  proprietario_nome: string;
  proprietario_cpf_cnpj: string;
}

export default function Veiculos() {
  const navigate = useNavigate();
  const { token } = useAuthStore();
  const [veiculos, setVeiculos] = useState<Veiculo[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchVeiculos();
  }, []);

  const fetchVeiculos = async () => {
    try {
      const response = await axios.get(`${API_URL}/veiculos`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setVeiculos(response.data.data);
    } catch (error) {
      console.error('Erro ao carregar veículos:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div className="p-8">Carregando...</div>;

  return (
    <div className="p-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold">Veículos</h1>
        <Button onClick={() => navigate('/veiculos/novo')}>Novo Veículo</Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Lista de Veículos</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b">
                  <th className="text-left py-2">Placa</th>
                  <th className="text-left py-2">Marca/Modelo</th>
                  <th className="text-left py-2">Cor</th>
                  <th className="text-left py-2">Ano</th>
                  <th className="text-left py-2">Proprietário</th>
                  <th className="text-left py-2">CPF/CNPJ</th>
                </tr>
              </thead>
              <tbody>
                {veiculos.map((veiculo) => (
                  <tr key={veiculo.id} className="border-b hover:bg-gray-50">
                    <td className="py-3 font-mono">{veiculo.placa}</td>
                    <td className="py-3">{veiculo.marca} {veiculo.modelo}</td>
                    <td className="py-3">{veiculo.cor}</td>
                    <td className="py-3">{veiculo.ano_fabricacao}</td>
                    <td className="py-3">{veiculo.proprietario_nome}</td>
                    <td className="py-3 font-mono">{veiculo.proprietario_cpf_cnpj}</td>
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
