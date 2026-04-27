import api from './axios'

export const getHorarios = async () => {
    const { data } = await api.get('/horarios')
    return data
}

export const getMiHorario = async () => {
    const { data } = await api.get('/empleado/mi-horario')
    return data
}

export const createHorario = async (payload) => {
    const { data } = await api.post('/horarios', payload)
    return data
}

export const updateHorario = async (id, payload) => {
    const { data } = await api.put(`/horarios/${id}`, payload)
    return data
}

export const deleteHorario = async (id) => {
    await api.delete(`/horarios/${id}`)
}