import { defineStore } from 'pinia'
import api from '../api/axios'

const storedToken = localStorage.getItem('token')
const storedUser = localStorage.getItem('user')

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: storedUser ? JSON.parse(storedUser) : null,
        token: storedToken || null,
        loading: false,
        error: null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token && !!state.user,
        userRole: (state) => state.user?.rol || null,
        isAdmin: (state) => state.user?.rol === 'admin',
        isEmpleado: (state) => state.user?.rol === 'empleado',
        isCliente: (state) => state.user?.rol === 'cliente',
    },

    actions: {
        setSession(user, token) {
            this.user = user
            this.token = token

            localStorage.setItem('user', JSON.stringify(user))
            localStorage.setItem('token', token)

            api.defaults.headers.common.Authorization = `Bearer ${token}`
        },

        clearSession() {
            this.user = null
            this.token = null
            this.error = null

            localStorage.removeItem('user')
            localStorage.removeItem('token')

            delete api.defaults.headers.common.Authorization
        },

        initAuth() {
            if (this.token) {
                api.defaults.headers.common.Authorization = `Bearer ${this.token}`
            }
        },

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

        async fetchUser() {
            if (!this.token) return null

            try {
                const { data } = await api.get('/me')
                this.user = data
                localStorage.setItem('user', JSON.stringify(data))
                return data
            } catch (error) {
                this.clearSession()
                throw error
            }
        },

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