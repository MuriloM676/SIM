import { useQuery } from '@tanstack/react-query'
import api from '@/lib/api'
import { FileText, Clock, CheckCircle, AlertCircle } from 'lucide-react'

export default function Dashboard() {
  const { data: stats } = useQuery({
    queryKey: ['multas-statistics'],
    queryFn: async () => {
      const response = await api.get('/multas/statistics')
      return response.data.data
    },
  })

  const cards = [
    {
      title: 'Total de Multas',
      value: stats?.total || 0,
      icon: FileText,
      color: 'blue',
    },
    {
      title: 'Rascunhos',
      value: stats?.rascunhos || 0,
      icon: Clock,
      color: 'yellow',
    },
    {
      title: 'Em Recurso',
      value: stats?.em_recurso || 0,
      icon: AlertCircle,
      color: 'orange',
    },
    {
      title: 'Encerradas',
      value: stats?.encerradas || 0,
      icon: CheckCircle,
      color: 'green',
    },
  ]

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {cards.map((card) => {
          const Icon = card.icon
          return (
            <div
              key={card.title}
              className="bg-white rounded-lg shadow p-6"
            >
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600">{card.title}</p>
                  <p className="text-3xl font-bold mt-2">{card.value}</p>
                </div>
                <div className={`p-3 rounded-full bg-${card.color}-100`}>
                  <Icon className={`text-${card.color}-600`} size={24} />
                </div>
              </div>
            </div>
          )
        })}
      </div>

      <div className="mt-8 bg-white rounded-lg shadow p-6">
        <h2 className="text-lg font-semibold mb-4">Ações Rápidas</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <a
            href="/multas/nova"
            className="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors text-center"
          >
            <FileText className="mx-auto mb-2 text-gray-400" size={32} />
            <p className="font-medium">Nova Multa</p>
          </a>
          <a
            href="/multas"
            className="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors text-center"
          >
            <FileText className="mx-auto mb-2 text-gray-400" size={32} />
            <p className="font-medium">Consultar Multas</p>
          </a>
          <a
            href="/auditoria"
            className="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors text-center"
          >
            <AlertCircle className="mx-auto mb-2 text-gray-400" size={32} />
            <p className="font-medium">Auditoria</p>
          </a>
        </div>
      </div>
    </div>
  )
}
