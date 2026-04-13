import api from "./axios";

export const getCatalogoServicios = async () => {
    const { data } = await api.get('/catalogo-servicios')
    return data
}

export const getEmpleadosPorServicio = async (servicioId) => {
    const { data } = await api.get(`/servicios/${servicioId}/empleados`)
    return data
}