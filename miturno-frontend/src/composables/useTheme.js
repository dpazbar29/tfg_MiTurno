import { ref, onMounted } from 'vue'

const STORAGE_KEY = 'theme'

export const useTheme = () => {
    const theme = ref('light')
    const isThemeReady = ref(false)

    const applyTheme = (value) => {
        theme.value = value
        document.documentElement.setAttribute('data-theme', value)
        localStorage.setItem(STORAGE_KEY, value)
    }

    const initTheme = () => {
        const storedTheme = localStorage.getItem(STORAGE_KEY)

        if (storedTheme === 'light' || storedTheme === 'dark') {
            applyTheme(storedTheme)
            isThemeReady.value = true
            return
        }

        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
        applyTheme(prefersDark ? 'dark' : 'light')
        isThemeReady.value = true
    }

    const toggleTheme = () => {
        applyTheme(theme.value === 'dark' ? 'light' : 'dark')
    }

    onMounted(() => {
        initTheme()
    })

    return {
        theme,
        isThemeReady,
        toggleTheme,
    }
}