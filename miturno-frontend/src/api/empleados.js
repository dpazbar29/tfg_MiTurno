import api from './axios'

export const getEmpleados = async () => {
    const { data } = await api.get('/empleados')
    return data
}

export const getEmpleado = async (id) => {
    const { data } = await api.get(`/empleados/${id}`)
    return data
}

export const syncServiciosEmpleado = async (empleadoId, servicioIds) => {
    const { data } = await api.put(`/empleados/${empleadoId}/servicios`, { servicio_ids: servicioIds })
    return data
}