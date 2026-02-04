import React from 'react';
import { useSearchParams } from 'react-router-dom';
import MapView from '@/components/MapView';

export default function MultasMap() {
  const [searchParams] = useSearchParams();
  const status = searchParams.get('status');
  const dataInicio = searchParams.get('data_inicio');
  const dataFim = searchParams.get('data_fim');

  const filters = {
    ...(status && { status }),
    ...(dataInicio && { data_inicio: dataInicio }),
    ...(dataFim && { data_fim: dataFim }),
  };

  return (
    <div className="container mx-auto py-6 px-4">
      <div className="mb-6">
        <h1 className="text-2xl font-bold">Mapa de Multas</h1>
        <p className="text-gray-600">Visualização geográfica das infrações</p>
      </div>

      <MapView filters={filters} />
    </div>
  );
}
