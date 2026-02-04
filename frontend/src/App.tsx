import { BrowserRouter, Routes, Route, Navigate, Outlet } from 'react-router-dom'
import { Toaster } from 'sonner'
import { useAuthStore } from './stores/authStore'

// Pages
import Login from './pages/Login'
import Dashboard from './pages/Dashboard'
import MultasList from './pages/multas/MultasList'
import MultaForm from './pages/multas/MultaForm'
import MultaDetails from './pages/multas/MultaDetails'
import MultasMap from './pages/MultasMap'
import AuditoriaList from './pages/AuditoriaList'
import Auditoria from './pages/Auditoria'
import Veiculos from './pages/Veiculos'
import Agentes from './pages/Agentes'
import Recursos from './pages/Recursos'
import Usuarios from './pages/Usuarios'
import Infracoes from './pages/Infracoes'

// Layout
import Layout from './components/Layout'

function App() {
  return (
    <BrowserRouter>
      <Toaster position="top-right" richColors />
      <Routes>
        <Route path="/login" element={<Login />} />
        
        <Route element={<ProtectedRoute />}>
          <Route path="/" element={<Dashboard />} />
          <Route path="/multas" element={<MultasList />} />
          <Route path="/multas/nova" element={<MultaForm />} />
          <Route path="/multas/mapa" element={<MultasMap />} />
          <Route path="/multas/:id" element={<MultaDetails />} />
          <Route path="/multas/:id/editar" element={<MultaForm />} />
          <Route path="/veiculos" element={<Veiculos />} />
          <Route path="/agentes" element={<Agentes />} />
          <Route path="/recursos" element={<Recursos />} />
          <Route path="/usuarios" element={<Usuarios />} />
          <Route path="/infracoes" element={<Infracoes />} />
          <Route path="/auditoria" element={<Auditoria />} />
          <Route path="/auditoria/completa" element={<AuditoriaList />} />
        </Route>
      </Routes>
    </BrowserRouter>
  )
}

// Rota protegida
function ProtectedRoute() {
  const { isAuthenticated } = useAuthStore()
  
  if (!isAuthenticated) {
    return <Navigate to="/login" replace />
  }
  
  return <Layout><Outlet /></Layout>
}

export default App
