import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import DashboardView from '../views/DashboardView.vue'
import ServiciosView from '../views/ServiciosView.vue'
import NuevaReservaView from '../views/NuevaReservaView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/dashboard' },
    { path: '/login', name: 'login', component: LoginView, meta: { guestOnly: true} },
    { path: '/register', name: 'register', component: RegisterView, meta: { guestOnly: true } },
    { path: '/dashboard', name: 'dashboard', component: DashboardView, meta: { requiresAuth: true } },
    { path: '/servicios', name: 'servicios', component: ServiciosView, meta: { requiresAuth: true } },
    { path: '/reservas/nueva', name: 'nueva-reserva', component: NuevaReservaView, meta: { requiresAuth: true } },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (auth.token && !auth.user) {
    await auth.fetchUser()
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return '/login'
  }

  if (to.meta.guestOnly && auth.isAuthenticated) {
    return '/dashboard'
  }
})

export default router
