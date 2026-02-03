export default function MultasList() {
  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Multas</h1>
        <a
          href="/multas/nova"
          className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
        >
          Nova Multa
        </a>
      </div>

      <div className="bg-white rounded-lg shadow">
        <div className="p-6">
          <p className="text-gray-600">
            Lista de multas será implementada aqui com filtros, paginação e ações.
          </p>
        </div>
      </div>
    </div>
  )
}
