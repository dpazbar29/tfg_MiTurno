import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import DashboardView from '../views/DashboardView.vue'
import ServiciosView from '../views/ServiciosView.vue'
import NuevaReservaView from '../views/NuevaReservaView.vue'
import MisReservasView from '../views/MisReservasView.vue'
import ServiciosViewAdmin from '../views/admin/ServiciosView.vue'

const routes = [
    { path: '/', redirect: '/dashboard', },
    
    { path: '/login', name: 'login', component: LoginView, meta: { guestOnly: true, }, },
    { path: '/register', name: 'register', component: RegisterView, meta: { guestOnly: true, }, },
    { path: '/dashboard', name: 'dashboard', component: DashboardView, meta: { requiresAuth: true, roles: ['cliente', 'admin', 'empleado'], navLabel: 'Dashboard', navOrder: 1, showInNav: true, }, },
    { path: '/servicios', name: 'servicios', component: ServiciosView, meta: { requiresAuth: true, roles: ['cliente'], navLabel: 'Servicios', navOrder: 2, showInNav: true, }, },
    { path: '/reservas/nueva', name: 'nueva-reserva', component: NuevaReservaView, meta: { requiresAuth: true, roles: ['cliente'], navLabel: 'Nueva reserva', navOrder: 3, showInNav: true, }, },
    { path: '/mis-reservas', name: 'mis-reservas', component: MisReservasView, meta: { requiresAuth: true, roles: ['cliente'], navLabel: 'Mis reservas', navOrder: 4, showInNav: true, }, },
    
    { path: '/admin', redirect: '/admin/servicios', meta: { requiresAuth: true, roles: ['admin'], }, },
    { path: '/admin/servicios', name: 'admin-servicios', component: ServiciosViewAdmin, meta: { requiresAuth: true, roles: ['admin'], navLabel: 'Servicios admin', navOrder: 2, showInNav: true, }, },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach(async (to) => {
    const auth = useAuthStore()

    if (auth.token && !auth.user) {
        try {
            await auth.fetchUser()
        } catch (error) {
            if (to.name !== 'login') {
                return { name: 'login' }
            }
            return
        }
    }

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        if (to.name !== 'login') {
            return {
                name: 'login',
                query: { redirect: to.fullPath },
            }
        }
        return
    }

    if (to.meta.guestOnly && auth.isAuthenticated) {
        if (to.name !== 'dashboard') {
            return { name: 'dashboard' }
        }
        return
    }

    if (to.meta.roles?.length) {
        const userRole = auth.userRole

        if (!userRole || !to.meta.roles.includes(userRole)) {
            if (to.name !== 'dashboard') {
                return { name: 'dashboard' }
            }
            return
        }
    }
})

export { routes }
export default router
