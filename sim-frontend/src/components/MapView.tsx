import React, { useEffect, useState } from 'react';
import { MapContainer, TileLayer, Marker, Popup, useMap } from 'react-leaflet';
import MarkerClusterGroup from 'react-leaflet-cluster';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import api from '@/services/api';

// Fix para ícones do Leaflet
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

interface Multa {
  id: number;
  auto_infracao: string;
  placa: string;
  latitude: number;
  longitude: number;
  data_infracao: string;
  local_infracao: string;
  status: string;
  valor_multa: number;
}

interface MapViewProps {
  filters?: any;
}

function RecenterMap({ center }: { center: [number, number] }) {
  const map = useMap();
  useEffect(() => {
    map.setView(center);
  }, [center, map]);
  return null;
}

export default function MapView({ filters }: MapViewProps) {
  const [multas, setMultas] = useState<Multa[]>([]);
  const [loading, setLoading] = useState(true);
  const [center, setCenter] = useState<[number, number]>([-23.55, -46.63]); // SP centro

  useEffect(() => {
    loadMultas();
  }, [filters]);

  const loadMultas = async () => {
    try {
      setLoading(true);
      const response = await api.get('/multas', {
        params: {
          ...filters,
          per_page: 500, // Carregar mais para visualização no mapa
          with_location: true,
        },
      });

      const multasComLocalizacao = response.data.data.filter(
        (m: Multa) => m.latitude && m.longitude
      );

      setMultas(multasComLocalizacao);

      // Centralizar no primeiro resultado
      if (multasComLocalizacao.length > 0) {
        setCenter([multasComLocalizacao[0].latitude, multasComLocalizacao[0].longitude]);
      }
    } catch (error) {
      console.error('Erro ao carregar multas:', error);
    } finally {
      setLoading(false);
    }
  };

  const getMarkerColor = (status: string) => {
    const colors: Record<string, string> = {
      registrada: '#3b82f6',
      notificada: '#f59e0b',
      em_recurso: '#8b5cf6',
      enviada_orgao_externo: '#06b6d4',
      cancelada: '#ef4444',
      encerrada: '#10b981',
    };
    return colors[status] || '#6b7280';
  };

  const createCustomIcon = (color: string) => {
    return L.divIcon({
      className: 'custom-marker',
      html: `<div style="background-color: ${color}; width: 25px; height: 25px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
      iconSize: [25, 25],
      iconAnchor: [12, 12],
    });
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-96">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div className="relative">
      <div className="mb-4 flex items-center justify-between">
        <div>
          <h3 className="text-lg font-semibold">Mapa de Multas</h3>
          <p className="text-sm text-gray-600">
            {multas.length} multas com localização
          </p>
        </div>

        <div className="flex gap-2 text-xs">
          <div className="flex items-center gap-1">
            <div className="w-3 h-3 rounded-full bg-blue-500"></div>
            <span>Registrada</span>
          </div>
          <div className="flex items-center gap-1">
            <div className="w-3 h-3 rounded-full bg-orange-500"></div>
            <span>Notificada</span>
          </div>
          <div className="flex items-center gap-1">
            <div className="w-3 h-3 rounded-full bg-purple-500"></div>
            <span>Em Recurso</span>
          </div>
          <div className="flex items-center gap-1">
            <div className="w-3 h-3 rounded-full bg-green-500"></div>
            <span>Encerrada</span>
          </div>
        </div>
      </div>

      <MapContainer
        center={center}
        zoom={13}
        style={{ height: '600px', width: '100%', borderRadius: '8px' }}
      >
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        <RecenterMap center={center} />

        <MarkerClusterGroup chunkedLoading>
          {multas.map((multa) => (
            <Marker
              key={multa.id}
              position={[multa.latitude, multa.longitude]}
              icon={createCustomIcon(getMarkerColor(multa.status))}
            >
              <Popup>
                <div className="p-2">
                  <h4 className="font-semibold mb-1">
                    Auto: {multa.auto_infracao}
                  </h4>
                  <p className="text-sm mb-1">
                    <strong>Placa:</strong> {multa.placa}
                  </p>
                  <p className="text-sm mb-1">
                    <strong>Data:</strong>{' '}
                    {new Date(multa.data_infracao).toLocaleDateString('pt-BR')}
                  </p>
                  <p className="text-sm mb-1">
                    <strong>Local:</strong> {multa.local_infracao}
                  </p>
                  <p className="text-sm mb-1">
                    <strong>Valor:</strong> R$ {Number(multa.valor_multa || 0).toFixed(2)}
                  </p>
                  <p className="text-sm">
                    <span
                      className={`inline-block px-2 py-0.5 rounded text-xs font-medium ${
                        multa.status === 'registrada'
                          ? 'bg-blue-100 text-blue-800'
                          : multa.status === 'notificada'
                          ? 'bg-orange-100 text-orange-800'
                          : multa.status === 'em_recurso'
                          ? 'bg-purple-100 text-purple-800'
                          : 'bg-gray-100 text-gray-800'
                      }`}
                    >
                      {multa.status.replace('_', ' ')}
                    </span>
                  </p>
                  <a
                    href={`/multas/${multa.id}`}
                    className="text-blue-600 text-sm mt-2 inline-block hover:underline"
                  >
                    Ver detalhes →
                  </a>
                </div>
              </Popup>
            </Marker>
          ))}
        </MarkerClusterGroup>
      </MapContainer>
    </div>
  );
}
