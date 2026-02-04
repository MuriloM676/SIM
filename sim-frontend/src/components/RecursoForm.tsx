import React, { useState } from 'react';
import { FileText, Send } from 'lucide-react';
import api from '@/services/api';
import { Button } from '@/components/ui/button';

interface RecursoFormProps {
  multaId: number;
  onSuccess?: () => void;
}

export default function RecursoForm({ multaId, onSuccess }: RecursoFormProps) {
  const [tipo, setTipo] = useState('defesa_previa');
  const [argumentacao, setArgumentacao] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (argumentacao.length < 50) {
      alert('A argumentação deve ter pelo menos 50 caracteres');
      return;
    }

    try {
      setSubmitting(true);
      const response = await api.post('/recursos', {
        multa_id: multaId,
        tipo,
        argumentacao,
      });

      alert(`Recurso aberto com sucesso! Protocolo: ${response.data.data.protocolo}`);
      setArgumentacao('');
      onSuccess?.();
    } catch (error: any) {
      alert(error.response?.data?.message || 'Erro ao abrir recurso');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div>
        <h3 className="text-lg font-semibold mb-4 flex items-center gap-2">
          <FileText className="w-5 h-5" />
          Abrir Recurso
        </h3>
      </div>

      <div>
        <label className="block text-sm font-medium mb-1">Tipo de Recurso</label>
        <select
          value={tipo}
          onChange={(e) => setTipo(e.target.value)}
          className="w-full border border-gray-300 rounded px-3 py-2"
          required
        >
          <option value="defesa_previa">Defesa Prévia</option>
          <option value="recurso_primeira_instancia">Recurso de 1ª Instância</option>
          <option value="recurso_segunda_instancia">Recurso de 2ª Instância</option>
        </select>
        <p className="text-xs text-gray-500 mt-1">
          {tipo === 'defesa_previa' && 'Apresentar defesa antes da notificação de autuação'}
          {tipo === 'recurso_primeira_instancia' && 'Recurso contra a notificação de autuação'}
          {tipo === 'recurso_segunda_instancia' && 'Recurso após julgamento de 1ª instância'}
        </p>
      </div>

      <div>
        <label className="block text-sm font-medium mb-1">
          Argumentação *
          <span className="text-xs text-gray-500 ml-2">
            ({argumentacao.length}/50 mínimo)
          </span>
        </label>
        <textarea
          value={argumentacao}
          onChange={(e) => setArgumentacao(e.target.value)}
          placeholder="Descreva detalhadamente os motivos do recurso..."
          className="w-full border border-gray-300 rounded px-3 py-2 h-48"
          required
          minLength={50}
        />
        <p className="text-xs text-gray-500 mt-1">
          Seja claro e objetivo. Apresente fatos, argumentos jurídicos e provas que justifiquem
          seu recurso.
        </p>
      </div>

      <div className="bg-blue-50 border border-blue-200 rounded p-4 text-sm">
        <p className="font-medium text-blue-900 mb-2">Informações Importantes:</p>
        <ul className="list-disc list-inside space-y-1 text-blue-800">
          <li>O recurso será analisado por um gestor</li>
          <li>Você será notificado da decisão</li>
          <li>Pode anexar documentos e evidências adicionais</li>
          <li>O prazo de análise é de até 30 dias</li>
        </ul>
      </div>

      <Button
        type="submit"
        disabled={submitting || argumentacao.length < 50}
        className="w-full"
      >
        <Send className="w-4 h-4 mr-2" />
        {submitting ? 'Enviando...' : 'Protocolar Recurso'}
      </Button>
    </form>
  );
}
