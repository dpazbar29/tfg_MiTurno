import api from "./axios"

export const getReservas = async () => {
    const { data } = await api.get('/mis-reservas')
    return data
}

export const getReservasAdmin = async (filters = {}) => {
    const { data } = await api.get('/admin/reservas', {
      params: filters,
    })
    return data
}

export const getReservasEmpleado = async (params = {}) => {
    const response = await api.get('/empleado/mis-reservas', { params })
    return response.data
}

export const crearReserva = async (payload) => {
    const { data } = await api.post('/reservas', payload)
    return data
}

export const updateReserva = async (id, payload) => {
    const { data } = await api.put(`/reservas/${id}`, payload)
    return data
}

export const deleteReserva = async (id) => {
    await api.delete(`/reservas/${id}`)
}

export const cancelarReserva = async (id) => {
    const { data } = await api.patch(`/reservas/${id}/cancelar`)
    return data
}

export const getDisponibilidad = async (params) => {
    const { data } = await api.get('/disponibilidad', { params })
    return data
}