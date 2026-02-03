import { create } from 'zustand'
import { persist } from 'zustand/middleware'

interface User {
  id: number
  nome: string
  email: string
  perfil: string
  perfil_label: string
  municipio_id: number
  municipio: string
}

interface AuthState {
  user: User | null
  token: string | null
  isAuthenticated: boolean
  setAuth: (user: User, token: string) => void
  logout: () => void
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set) => ({
      user: null,
      token: null,
      isAuthenticated: false,
      setAuth: (user, token) => {
        localStorage.setItem('sim_token', token)
        localStorage.setItem('sim_user', JSON.stringify(user))
        set({ user, token, isAuthenticated: true })
      },
      logout: () => {
        localStorage.removeItem('sim_token')
        localStorage.removeItem('sim_user')
        set({ user: null, token: null, isAuthenticated: false })
      },
    }),
    {
      name: 'sim-auth-storage',
    }
  )
)
