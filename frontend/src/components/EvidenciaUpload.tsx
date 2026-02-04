import React, { useState } from 'react';
import { Upload, X, Image as ImageIcon } from 'lucide-react';
import api from '@/services/api';
import { Button } from '@/components/ui/button';

interface EvidenciaUploadProps {
  multaId: number;
  onUploadSuccess?: () => void;
}

interface Evidencia {
  id: number;
  tipo: string;
  descricao: string;
  url: string;
  thumbnail: string;
  created_at: string;
}

export default function EvidenciaUpload({ multaId, onUploadSuccess }: EvidenciaUploadProps) {
  const [file, setFile] = useState<File | null>(null);
  const [tipo, setTipo] = useState('foto_veiculo');
  const [descricao, setDescricao] = useState('');
  const [uploading, setUploading] = useState(false);
  const [preview, setPreview] = useState<string | null>(null);
  const [evidencias, setEvidencias] = useState<Evidencia[]>([]);
  const [loading, setLoading] = useState(false);

  React.useEffect(() => {
    loadEvidencias();
  }, [multaId]);

  const loadEvidencias = async () => {
    try {
      setLoading(true);
      const response = await api.get(`/multas/${multaId}/evidencias`);
      setEvidencias(response.data.data);
    } catch (error) {
      console.error('Erro ao carregar evidências:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    const selectedFile = e.target.files?.[0];
    if (selectedFile) {
      setFile(selectedFile);
      const reader = new FileReader();
      reader.onloadend = () => {
        setPreview(reader.result as string);
      };
      reader.readAsDataURL(selectedFile);
    }
  };

  const handleUpload = async () => {
    if (!file) return;

    const formData = new FormData();
    formData.append('arquivo', file);
    formData.append('tipo', tipo);
    formData.append('descricao', descricao);

    try {
      setUploading(true);
      await api.post(`/multas/${multaId}/evidencias`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      setFile(null);
      setPreview(null);
      setDescricao('');
      loadEvidencias();
      onUploadSuccess?.();
    } catch (error: any) {
      alert(error.response?.data?.message || 'Erro ao fazer upload');
    } finally {
      setUploading(false);
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Deseja realmente excluir esta evidência?')) return;

    try {
      await api.delete(`/multas/${multaId}/evidencias/${id}`);
      loadEvidencias();
    } catch (error: any) {
      alert(error.response?.data?.message || 'Erro ao excluir evidência');
    }
  };

  return (
    <div className="space-y-6">
      {/* Upload Form */}
      <div className="border-2 border-dashed border-gray-300 rounded-lg p-6">
        <h3 className="text-lg font-semibold mb-4">Upload de Evidências</h3>

        {!preview ? (
          <label className="flex flex-col items-center justify-center h-48 cursor-pointer hover:bg-gray-50 transition">
            <Upload className="w-12 h-12 text-gray-400 mb-2" />
            <span className="text-sm text-gray-600">Clique para selecionar uma foto</span>
            <span className="text-xs text-gray-400 mt-1">JPG, JPEG ou PNG (máx. 10MB)</span>
            <input
              type="file"
              accept="image/jpeg,image/jpg,image/png"
              onChange={handleFileSelect}
              className="hidden"
            />
          </label>
        ) : (
          <div className="space-y-4">
            <div className="relative">
              <img src={preview} alt="Preview" className="w-full h-64 object-cover rounded" />
              <button
                onClick={() => {
                  setFile(null);
                  setPreview(null);
                }}
                className="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full hover:bg-red-600"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <div className="space-y-3">
              <div>
                <label className="block text-sm font-medium mb-1">Tipo de Evidência</label>
                <select
                  value={tipo}
                  onChange={(e) => setTipo(e.target.value)}
                  className="w-full border border-gray-300 rounded px-3 py-2"
                >
                  <option value="foto_veiculo">Foto do Veículo</option>
                  <option value="foto_local">Foto do Local</option>
                  <option value="foto_infracao">Foto da Infração</option>
                  <option value="documento">Documento</option>
                  <option value="outro">Outro</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium mb-1">Descrição (opcional)</label>
                <textarea
                  value={descricao}
                  onChange={(e) => setDescricao(e.target.value)}
                  placeholder="Descreva a evidência..."
                  className="w-full border border-gray-300 rounded px-3 py-2"
                  rows={2}
                />
              </div>

              <Button
                onClick={handleUpload}
                disabled={uploading}
                className="w-full"
              >
                {uploading ? 'Enviando...' : 'Enviar Evidência'}
              </Button>
            </div>
          </div>
        )}
      </div>

      {/* Lista de Evidências */}
      <div>
        <h3 className="text-lg font-semibold mb-4">
          Evidências Anexadas ({evidencias.length})
        </h3>

        {loading ? (
          <div className="text-center py-8 text-gray-500">Carregando...</div>
        ) : evidencias.length === 0 ? (
          <div className="text-center py-8 text-gray-500">
            <ImageIcon className="w-12 h-12 mx-auto mb-2 opacity-50" />
            <p>Nenhuma evidência anexada</p>
          </div>
        ) : (
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {evidencias.map((ev) => (
              <div key={ev.id} className="relative group">
                <a
                  href={ev.url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="block"
                >
                  <img
                    src={ev.thumbnail}
                    alt={ev.tipo}
                    className="w-full h-32 object-cover rounded border"
                  />
                </a>
                <div className="mt-1 text-xs">
                  <p className="font-medium">{ev.tipo.replace('_', ' ')}</p>
                  {ev.descricao && (
                    <p className="text-gray-600 truncate">{ev.descricao}</p>
                  )}
                </div>
                <button
                  onClick={() => handleDelete(ev.id)}
                  className="absolute top-1 right-1 bg-red-500 text-white p-1 rounded opacity-0 group-hover:opacity-100 transition"
                >
                  <X className="w-3 h-3" />
                </button>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
