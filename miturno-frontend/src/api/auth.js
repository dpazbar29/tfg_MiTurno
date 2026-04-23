import api from './axios'

export const getMe = async () => {
    const response = await api.get('/me')
    return response.data
}

export const updateMe = async (payload) => {
    const { data } = await api.put('/me', payload)
    return data
}

export const deleteMe = async () => {
    const { data } = await api.delete('/me')
    return data
}