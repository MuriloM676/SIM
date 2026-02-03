import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useAuthStore } from '@/stores/authStore';
import axios from 'axios';
import { toast } from 'sonner';

const API_URL = 'http://localhost:8000/api';

interface Veiculo {
  id: number;
  placa: string;
  marca: string;
  modelo: string;
}

interface Agente {
  id: number;
  matricula: string;
  nome: string;
}

interface Infracao {
  id: number;
  codigo_ctb: string;
  descricao: string;
  valor: number;
  pontos: number;
}

export default function MultaForm() {
  const navigate = useNavigate();
  const { id } = useParams();
  const { token } = useAuthStore();
  const [loading, setLoading] = useState(false);
  const [veiculos, setVeiculos] = useState<Veiculo[]>([]);
  const [agentes, setAgentes] = useState<Agente[]>([]);
  const [infracoes, setInfracoes] = useState<Infracao[]>([]);
  
  const [formData, setFormData] = useState({
    veiculo_id: '',
    agente_id: '',
    infracao_id: '',
    placa: '',
    data_infracao: '',
    hora_infracao: '',
    local_infracao: '',
    latitude: '',
    longitude: '',
    velocidade_medida: '',
    velocidade_maxima: '',
    observacoes: '',
  });

  useEffect(() => {
    fetchVeiculos();
    fetchAgentes();
    fetchInfracoes();
    if (id) {
      fetchMulta();
    }
  }, [id]);

  const fetchVeiculos = async () => {
    try {
      const response = await axios.get(`${API_URL}/veiculos`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setVeiculos(response.data.data);
    } catch (error) {
      console.error('Erro ao carregar veículos:', error);
    }
  };

  const fetchAgentes = async () => {
    try {
      const response = await axios.get(`${API_URL}/agentes`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setAgentes(response.data.data);
    } catch (error) {
      console.error('Erro ao carregar agentes:', error);
    }
  };

  const fetchInfracoes = async () => {
    try {
      const response = await axios.get(`${API_URL}/infracoes`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setInfracoes(response.data.data);
    } catch (error) {
      console.error('Erro ao carregar infrações:', error);
    }
  };

  const fetchMulta = async () => {
    try {
      const response = await axios.get(`${API_URL}/multas/${id}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      const multa = response.data.data;
      setFormData({
        veiculo_id: multa.veiculo_id,
        agente_id: multa.agente_id,
        infracao_id: multa.infracao_id,
        placa: multa.placa,
        data_infracao: multa.data_infracao,
        hora_infracao: multa.hora_infracao,
        local_infracao: multa.local_infracao,
        latitude: multa.latitude || '',
        longitude: multa.longitude || '',
        velocidade_medida: multa.velocidade_medida || '',
        velocidade_maxima: multa.velocidade_maxima || '',
        observacoes: multa.observacoes || '',
      });
    } catch (error) {
      toast.error('Erro ao carregar multa');
      navigate('/multas');
    }
  };

  const handleVeiculoChange = (veiculoId: string) => {
    const veiculo = veiculos.find(v => v.id === parseInt(veiculoId));
    if (veiculo) {
      setFormData(prev => ({
        ...prev,
        veiculo_id: veiculoId,
        placa: veiculo.placa,
      }));
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      if (id) {
        await axios.put(`${API_URL}/multas/${id}`, formData, {
          headers: { Authorization: `Bearer ${token}` },
        });
        toast.success('Multa atualizada com sucesso!');
      } else {
        await axios.post(`${API_URL}/multas`, formData, {
          headers: { Authorization: `Bearer ${token}` },
        });
        toast.success('Multa criada com sucesso!');
      }
      navigate('/multas');
    } catch (error: any) {
      const message = error.response?.data?.message || 'Erro ao salvar multa';
      toast.error(message);
      console.error('Erro:', error.response?.data);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="p-8">
      <div className="mb-6">
        <h1 className="text-3xl font-bold">{id ? 'Editar Multa' : 'Nova Multa'}</h1>
      </div>

      <form onSubmit={handleSubmit}>
        <Card className="mb-6">
          <CardHeader>
            <CardTitle>Dados da Infração</CardTitle>
          </CardHeader>
          <CardContent className="grid grid-cols-2 gap-4">
            <div>
              <Label htmlFor="veiculo_id">Veículo *</Label>
              <Select
                id="veiculo_id"
                value={formData.veiculo_id}
                onChange={(e) => handleVeiculoChange(e.target.value)}
                required
              >
                <option value="">Selecione um veículo</option>
                {veiculos.map((v) => (
                  <option key={v.id} value={v.id}>
                    {v.placa} - {v.marca} {v.modelo}
                  </option>
                ))}
              </Select>
            </div>

            <div>
              <Label htmlFor="placa">Placa *</Label>
              <Input
                id="placa"
                value={formData.placa}
                onChange={(e) => setFormData({ ...formData, placa: e.target.value.toUpperCase() })}
                maxLength={7}
                required
                readOnly
              />
            </div>

            <div>
              <Label htmlFor="agente_id">Agente *</Label>
              <Select
                id="agente_id"
                value={formData.agente_id}
                onChange={(e) => setFormData({ ...formData, agente_id: e.target.value })}
                required
              >
                <option value="">Selecione um agente</option>
                {agentes.map((a) => (
                  <option key={a.id} value={a.id}>
                    {a.matricula} - {a.nome}
                  </option>
                ))}
              </Select>
            </div>

            <div>
              <Label htmlFor="infracao_id">Infração *</Label>
              <Select
                id="infracao_id"
                value={formData.infracao_id}
                onChange={(e) => setFormData({ ...formData, infracao_id: e.target.value })}
                required
              >
                <option value="">Selecione uma infração</option>
                {infracoes.map((i) => (
                  <option key={i.id} value={i.id}>
                    {i.codigo_ctb} - {i.descricao} (R$ {i.valor.toFixed(2)} - {i.pontos} pts)
                  </option>
                ))}
              </Select>
            </div>

            <div>
              <Label htmlFor="data_infracao">Data *</Label>
              <Input
                id="data_infracao"
                type="date"
                value={formData.data_infracao}
                onChange={(e) => setFormData({ ...formData, data_infracao: e.target.value })}
                required
              />
            </div>

            <div>
              <Label htmlFor="hora_infracao">Hora *</Label>
              <Input
                id="hora_infracao"
                type="time"
                value={formData.hora_infracao}
                onChange={(e) => setFormData({ ...formData, hora_infracao: e.target.value })}
                required
              />
            </div>

            <div className="col-span-2">
              <Label htmlFor="local_infracao">Local da Infração *</Label>
              <Input
                id="local_infracao"
                value={formData.local_infracao}
                onChange={(e) => setFormData({ ...formData, local_infracao: e.target.value })}
                placeholder="Endereço completo"
                required
              />
            </div>

            <div>
              <Label htmlFor="latitude">Latitude</Label>
              <Input
                id="latitude"
                value={formData.latitude}
                onChange={(e) => setFormData({ ...formData, latitude: e.target.value })}
                placeholder="-23.5505"
              />
            </div>

            <div>
              <Label htmlFor="longitude">Longitude</Label>
              <Input
                id="longitude"
                value={formData.longitude}
                onChange={(e) => setFormData({ ...formData, longitude: e.target.value })}
                placeholder="-46.6333"
              />
            </div>

            <div>
              <Label htmlFor="velocidade_medida">Velocidade Medida (km/h)</Label>
              <Input
                id="velocidade_medida"
                type="number"
                value={formData.velocidade_medida}
                onChange={(e) => setFormData({ ...formData, velocidade_medida: e.target.value })}
              />
            </div>

            <div>
              <Label htmlFor="velocidade_maxima">Velocidade Máxima (km/h)</Label>
              <Input
                id="velocidade_maxima"
                type="number"
                value={formData.velocidade_maxima}
                onChange={(e) => setFormData({ ...formData, velocidade_maxima: e.target.value })}
              />
            </div>

            <div className="col-span-2">
              <Label htmlFor="observacoes">Observações</Label>
              <Textarea
                id="observacoes"
                value={formData.observacoes}
                onChange={(e) => setFormData({ ...formData, observacoes: e.target.value })}
                rows={4}
              />
            </div>
          </CardContent>
        </Card>

        <div className="flex gap-4">
          <Button type="submit" disabled={loading}>
            {loading ? 'Salvando...' : id ? 'Atualizar Multa' : 'Criar Multa'}
          </Button>
          <Button type="button" variant="outline" onClick={() => navigate('/multas')}>
            Cancelar
          </Button>
        </div>
      </form>
    </div>
  );
}
