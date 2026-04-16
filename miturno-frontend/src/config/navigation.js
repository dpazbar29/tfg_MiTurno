export const getNavigationByRole = (router, role) => {
    if (!router || !role) return []

    return router
        .getRoutes()
        .filter((route) => route.meta?.showInNav === true)
        .filter((route) => Array.isArray(route.meta?.roles) && route.meta.roles.includes(role))
        .filter((route) => typeof route.name === 'string' && route.name.length > 0)
        .filter((route) => typeof route.meta?.navLabel === 'string' && route.meta.navLabel.length > 0)
        .sort((a, b) => (a.meta?.navOrder || 0) - (b.meta?.navOrder || 0))
        .map((route) => ({
            label: route.meta.navLabel,
            to: { name: route.name },
        }))
}