import { defineStore } from 'pinia'
import api from '../api/axios'

// Se recupera la sesión persistida en localStorage al cargar el módulo.
// Esto permite reconstruir el estado tras refrescar la página.
const storedToken = localStorage.getItem('token')
const storedUser = localStorage.getItem('user')

export const useAuthStore = defineStore('auth', {
    state: () => ({
        // Estado inicial del usuario autenticado.
        // Si existe sesión previa, se restaura automáticamente.
        user: storedUser ? JSON.parse(storedUser) : null,
        token: storedToken || null,
        loading: false,
        error: null,
    }),

    getters: {
        // Considera autenticado al usuario solo si hay token y datos de usuario.
        isAuthenticated: (state) => !!state.token && !!state.user,

         // Getters para controlar acceso por rol.
        userRole: (state) => state.user?.rol || null,
        isAdmin: (state) => state.user?.rol === 'admin',
        isEmpleado: (state) => state.user?.rol === 'empleado',
        isCliente: (state) => state.user?.rol === 'cliente',
    },

    actions: {
        // Guarda la sesión completa:
        // - actualiza el estado de Pinia,
        // - persiste en localStorage,
        // - y configura Axios para enviar el token en cada petición.
        setSession(user, token) {
            this.user = user
            this.token = token

            localStorage.setItem('user', JSON.stringify(user))
            localStorage.setItem('token', token)

            api.defaults.headers.common.Authorization = `Bearer ${token}`
        },

        // Actualiza solo el usuario actual, manteniendo el token.
        setUser(user) {
            this.user = user
            localStorage.setItem('user', JSON.stringify(user))
        },

        // Elimina por completo la sesión:
        // - limpia estado,
        // - borra localStorage,
        // - y elimina el header Authorization.
        clearSession() {
            this.user = null
            this.token = null
            this.error = null

            localStorage.removeItem('user')
            localStorage.removeItem('token')

            delete api.defaults.headers.common.Authorization
        },

        // Inicializa Axios con el token persistido si existe.
        initAuth() {
            if (this.token) {
                api.defaults.headers.common.Authorization = `Bearer ${this.token}`
            }
        },

        // Registro de usuario.
        // Si el backend devuelve usuario + token, la sesión queda iniciada inmediatamente tras registrarse.
        async register(form) {
            this.loading = true
            this.error = null

            try {
                const { data } = await api.post('/register', form)
                this.setSession(data.user, data.token)
                return data
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al registrarse'
                throw error
            } finally {
                this.loading = false
            }
        },

        // Inicio de sesión.
        // Guarda usuario y token si la autenticación es correcta.
        async login(form) {
            this.loading = true
            this.error = null

            try {
                const { data } = await api.post('/login', form)
                this.setSession(data.user, data.token)
                return data
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al iniciar sesión'
                throw error
            } finally {
                this.loading = false
            }
        },

        // Recupera el usuario autenticado actual usando el token.
        // Si falla, se asume sesión inválida y se limpia todo.
        async fetchUser() {
            if (!this.token) return null

            try {
                const { data } = await api.get('/me')
                this.setUser(data)
                return data
            } catch (error) {
                this.clearSession()
                throw error
            }
        },

        // Cierre de sesión.
        // Aunque falle la llamada al backend, se limpia la sesión local.
        async logout() {
            try {
                await api.post('/logout')
            } catch (error) {
                console.error(error)
            } finally {
                this.clearSession()
            }
        },
    },
})