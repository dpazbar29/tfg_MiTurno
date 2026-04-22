import api from "./axios"

export const getUsuarios = async (params = {}) => {
    const response = await api.get('/usuarios', { params })
    return response.data
}

export const getUsuario = async (id) => {
    const response = await api.get(`/usuarios/${id}`)
    return response.data
}

export const crearUsuario = async (data) => {
    const response = await api.post('/usuarios', data)
    return response.data
}

export const actualizarUsuario = async (id, data) => {
    const response = await api.put(`/usuarios/${id}`, data)
    return response.data
}

export const eliminarUsuario = async (id) => {
    const response = await api.delete(`/usuarios/${id}`)
    return response.data
}

export const buscarClientes = async (q) => {
    const query = String(q || '').trim()

    if (query.length < 2) {
        return []
    }

    const response = await api.get('/usuarios/clientes/buscar', {
        params: { q: query },
    })

    return response.data ?? []
}