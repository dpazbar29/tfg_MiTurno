import api from "./axios";

export const getCatalogoServicios = async () => {
    const { data } = await api.get('/catalogo-servicios')
    return data
}

export const getEmpleadosPorServicio = async (servicioId) => {
    const { data } = await api.get(`/servicios/${servicioId}/empleados`)
    return data
}

export const getServicios = async () => {
    const { data } = await api.get('/servicios')
    return data
}

export const crearServicio = async (data) => {
  const { data: response } = await api.post('/servicios', data)
  return response
}

export const actualizarServicio = async (id, data) => {
  const { data: response } = await api.put(`/servicios/${id}`, data)
  return response
}

export const eliminarServicio = async (id) => {
  const { data: response } = await api.delete(`/servicios/${id}`)
  return response
}