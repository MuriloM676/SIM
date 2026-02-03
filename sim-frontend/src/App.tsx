import { BrowserRouter, Routes, Route, Navigate, Outlet } from 'react-router-dom'
import { Toaster } from 'sonner'
import { useAuthStore } from './stores/authStore'

// Pages
import Login from './pages/Login'
import Dashboard from './pages/Dashboard'
import MultasList from './pages/multas/MultasList'
import MultaForm from './pages/multas/MultaForm'
import MultaDetails from './pages/multas/MultaDetails'
import Auditoria from './pages/Auditoria'

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
          <Route path="/multas/:id" element={<MultaDetails />} />
          <Route path="/multas/:id/editar" element={<MultaForm />} />
          <Route path="/auditoria" element={<Auditoria />} />
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
