import { useEffect, useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useAuthStore } from '@/stores/authStore';
import axios from 'axios';

const API_URL = 'http://localhost:8000/api';

interface Infracao {
  id: number;
  codigo_ctb: string;
  descricao: string;
  gravidade: string;
  pontos: number;
  valor: number;
}

const gravidadeLabels: Record<string, string> = {
  leve: 'Leve',
  media: 'Média',
  grave: 'Grave',
  gravissima: 'Gravíssima',
};

const gravidadeColors: Record<string, string> = {
  leve: 'bg-green-100 text-green-800',
  media: 'bg-yellow-100 text-yellow-800',
  grave: 'bg-orange-100 text-orange-800',
  gravissima: 'bg-red-100 text-red-800',
};

export default function Infracoes() {
  const { token } = useAuthStore();
  const [infracoes, setInfracoes] = useState<Infracao[]>([]);
  const [filteredInfracoes, setFilteredInfracoes] = useState<Infracao[]>([]);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchInfracoes();
  }, []);

  useEffect(() => {
    if (search) {
      const filtered = infracoes.filter(inf => 
        inf.codigo_ctb.toLowerCase().includes(search.toLowerCase()) ||
        inf.descricao.toLowerCase().includes(search.toLowerCase())
      );
      setFilteredInfracoes(filtered);
    } else {
      setFilteredInfracoes(infracoes);
    }
  }, [search, infracoes]);

  const fetchInfracoes = async () => {
    try {
      const response = await axios.get(`${API_URL}/infracoes`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setInfracoes(response.data.data);
      setFilteredInfracoes(response.data.data);
    } catch (error) {
      console.error('Erro ao carregar infrações:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div className="p-8">Carregando...</div>;

  return (
    <div className="p-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold">Infrações CTB</h1>
      </div>

      <Card className="mb-4">
        <CardContent className="pt-6">
          <Input 
            placeholder="Buscar por código ou descrição..." 
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Lista de Infrações ({filteredInfracoes.length})</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b">
                  <th className="text-left py-2">Código CTB</th>
                  <th className="text-left py-2">Descrição</th>
                  <th className="text-left py-2">Gravidade</th>
                  <th className="text-left py-2">Pontos</th>
                  <th className="text-left py-2">Valor (R$)</th>
                </tr>
              </thead>
              <tbody>
                {filteredInfracoes.map((infracao) => (
                  <tr key={infracao.id} className="border-b hover:bg-gray-50">
                    <td className="py-3 font-mono font-bold">{infracao.codigo_ctb}</td>
                    <td className="py-3 max-w-md">{infracao.descricao}</td>
                    <td className="py-3">
                      <span className={`px-2 py-1 rounded text-xs ${gravidadeColors[infracao.gravidade]}`}>
                        {gravidadeLabels[infracao.gravidade]}
                      </span>
                    </td>
                    <td className="py-3 text-center">{infracao.pontos}</td>
                    <td className="py-3 font-mono">R$ {Number(infracao.valor).toFixed(2)}</td>
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
