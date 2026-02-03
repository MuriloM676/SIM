import { useEffect, useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { useAuthStore } from '@/stores/authStore';
import axios from 'axios';

const API_URL = 'http://localhost:8000/api';

interface Usuario {
  id: number;
  nome: string;
  email: string;
  cpf: string;
  perfil: string;
  municipio_nome: string;
  ativo: boolean;
}

const perfilLabels: Record<string, string> = {
  administrador: 'Administrador',
  gestor: 'Gestor',
  operador: 'Operador',
  auditor: 'Auditor',
};

export default function Usuarios() {
  const { token, user } = useAuthStore();
  const [usuarios, setUsuarios] = useState<Usuario[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchUsuarios();
  }, []);

  const fetchUsuarios = async () => {
    try {
      const response = await axios.get(`${API_URL}/usuarios`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setUsuarios(response.data.data);
    } catch (error) {
      console.error('Erro ao carregar usuários:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleResetPassword = async (id: number, nome: string) => {
    const password = window.prompt(`Nova senha para ${nome}:`);
    if (!password || password.length < 6) {
      alert('Senha deve ter no mínimo 6 caracteres');
      return;
    }

    try {
      await axios.post(`${API_URL}/usuarios/${id}/reset-password`, {
        password,
        password_confirmation: password,
      }, {
        headers: { Authorization: `Bearer ${token}` },
      });
      
      alert('Senha resetada com sucesso!');
    } catch (error: any) {
      alert(error.response?.data?.message || 'Erro ao resetar senha');
    }
  };

  if (loading) return <div className="p-8">Carregando...</div>;

  const podeGerenciar = user?.perfil === 'administrador' || user?.perfil === 'gestor';

  if (!podeGerenciar) {
    return (
      <div className="p-8">
        <Card>
          <CardContent className="py-8 text-center">
            <p className="text-gray-600">Acesso negado. Apenas administradores e gestores podem gerenciar usuários.</p>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="p-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold">Usuários</h1>
        <Button onClick={() => alert('Funcionalidade em desenvolvimento')}>Novo Usuário</Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Lista de Usuários</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b">
                  <th className="text-left py-2">Nome</th>
                  <th className="text-left py-2">Email</th>
                  <th className="text-left py-2">CPF</th>
                  <th className="text-left py-2">Perfil</th>
                  <th className="text-left py-2">Município</th>
                  <th className="text-left py-2">Status</th>
                  <th className="text-left py-2">Ações</th>
                </tr>
              </thead>
              <tbody>
                {usuarios.map((usuario) => (
                  <tr key={usuario.id} className="border-b hover:bg-gray-50">
                    <td className="py-3">{usuario.nome}</td>
                    <td className="py-3">{usuario.email}</td>
                    <td className="py-3 font-mono">{usuario.cpf}</td>
                    <td className="py-3">
                      <span className="px-2 py-1 rounded text-xs bg-blue-100 text-blue-800">
                        {perfilLabels[usuario.perfil]}
                      </span>
                    </td>
                    <td className="py-3">{usuario.municipio_nome}</td>
                    <td className="py-3">
                      <span className={`px-2 py-1 rounded text-xs ${
                        usuario.ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                      }`}>
                        {usuario.ativo ? 'Ativo' : 'Inativo'}
                      </span>
                    </td>
                    <td className="py-3">
                      <Button 
                        size="sm" 
                        variant="outline"
                        onClick={() => handleResetPassword(usuario.id, usuario.nome)}
                      >
                        Resetar Senha
                      </Button>
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
