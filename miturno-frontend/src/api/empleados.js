import api from './axios'

export const getEmpleados = async () => {
    const { data } = await api.get('/empleados')
    return data
}

export const getEmpleado = async (id) => {
    const { data } = await api.get(`/empleados/${id}`)
    return data
}

export const createEmpleado = async (payload) => {
    const { data } = await api.post('/empleados', payload)
    return data
}

export const updateEmpleado = async (id, payload) => {
    const { data } = await api.put(`/empleados/${id}`, payload)
    return data
}

export const deleteEmpleado = async (id) => {
    const { data } = await api.delete(`/empleados/${id}`)
    return data
}

export const syncServiciosEmpleado = async (empleadoId, servicioIds) => {
    const { data } = await api.put(`/empleados/${empleadoId}/servicios`, { servicio_ids: servicioIds })
    return data
}