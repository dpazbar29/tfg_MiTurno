import api from "./axios"

export const getDisponibilidad = async (params) => {
    const { data } = await api.get('/disponibilidad', { params })
    return data
}

export const crearReserva = async (payload) => {
    const { data } = await api.post('/reservas', payload)
    return data
}