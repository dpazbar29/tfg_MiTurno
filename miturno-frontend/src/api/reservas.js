import api from "./axios"

export const getDisponibilidad = async (params) => {
    const { data } = await api.get('/disponibilidad', { params })
    return data
}

export const crearReserva = async (payload) => {
    const { data } = await api.post('/reservas', payload)
    return data
}

export const getReservas = async () => {
    const { data } = await api.get('/reservas')
    return data
}

export const cancelarReserva = async (reservaId) => {
    const { data } = await api.patch(`/reservas/${reservaId}/cancelar`)
    return data
}