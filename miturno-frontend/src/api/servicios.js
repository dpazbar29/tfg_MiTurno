import api from "./axios";

export const getCatalogoServicios = async () => {
    const { data } = await api.get('/catalogo-servicios')
    return data
}