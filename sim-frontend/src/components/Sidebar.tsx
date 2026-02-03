import { Link, useLocation } from 'react-router-dom'
import { Home, FileText, Shield, Users, Car, UserCheck, Scale, FileWarning } from 'lucide-react'

export default function Sidebar() {
  const location = useLocation()

  const menuItems = [
    { path: '/', label: 'Dashboard', icon: Home },
    { path: '/multas', label: 'Multas', icon: FileText },
    { path: '/recursos', label: 'Recursos', icon: Scale },
    { path: '/veiculos', label: 'Veículos', icon: Car },
    { path: '/agentes', label: 'Agentes', icon: UserCheck },
    { path: '/infracoes', label: 'Infrações CTB', icon: FileWarning },
    { path: '/usuarios', label: 'Usuários', icon: Users },
    { path: '/auditoria', label: 'Auditoria', icon: Shield },
  ]

  return (
    <aside className="w-64 bg-blue-900 text-white">
      <div className="p-6">
        <h2 className="text-2xl font-bold">SIM</h2>
        <p className="text-blue-200 text-sm">Sistema Integrado de Multas</p>
      </div>

      <nav className="mt-6">
        {menuItems.map((item) => {
          const Icon = item.icon
          const isActive = location.pathname === item.path
          
          return (
            <Link
              key={item.path}
              to={item.path}
              className={`flex items-center gap-3 px-6 py-3 transition-colors ${
                isActive
                  ? 'bg-blue-800 border-l-4 border-white'
                  : 'hover:bg-blue-800'
              }`}
            >
              <Icon size={20} />
              <span>{item.label}</span>
            </Link>
          )
        })}
      </nav>
    </aside>
  )
}
