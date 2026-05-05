export const getNavigationByRole = (router, role) => {

    // Si no hay router o no se ha pasado rol, no se puede construir navegación.
    if (!router || !role) return []

    return router
        //Recupera todas las rutas
        .getRoutes()
        // Solo muestras las rutas marcadas para aparecer en el menú
        .filter((route) => route.meta?.showInNav === true)
        // Filtra por rol.
        .filter((route) => Array.isArray(route.meta?.roles) && route.meta.roles.includes(role))
        // Solo se usan rutas con nombre válido, porque luego se navega por { name }.
        .filter((route) => typeof route.name === 'string' && route.name.length > 0)
        // También exige una etiqueta de navegación para mostrar texto en el menú.
        .filter((route) => typeof route.meta?.navLabel === 'string' && route.meta.navLabel.length > 0)
        // Ordena el menú por el campo navOrder.
        .sort((a, b) => (a.meta?.navOrder || 0) - (b.meta?.navOrder || 0))
        // Convierte las rutas en un formato simple para pintar el menú.
        .map((route) => ({
            label: route.meta.navLabel,
            to: { name: route.name },
        }))
}