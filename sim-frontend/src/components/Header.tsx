import { useNavigate } from 'react-router-dom'
import { LogOut, User } from 'lucide-react'
import { useAuthStore } from '@/stores/authStore'
import { toast } from 'sonner'
import api from '@/lib/api'

export default function Header() {
  const { user, logout } = useAuthStore()
  const navigate = useNavigate()

  const handleLogout = async () => {
    try {
      await api.post('/logout')
      logout()
      toast.success('Logout realizado com sucesso')
      navigate('/login')
    } catch (error) {
      logout()
      navigate('/login')
    }
  }

  return (
    <header className="bg-white shadow-sm">
      <div className="flex items-center justify-between px-6 py-4">
        <div>
          <h1 className="text-xl font-semibold text-gray-800">
            Bem-vindo, {user?.nome}
          </h1>
          <p className="text-sm text-gray-600">
            {user?.perfil_label} • {user?.municipio}
          </p>
        </div>

        <button
          onClick={handleLogout}
          className="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md transition-colors"
        >
          <LogOut size={18} />
          <span>Sair</span>
        </button>
      </div>
    </header>
  )
}
