<script setup>
import { computed, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { getNavigationByRole } from '../config/navigation'
import { useTheme } from '../composables/useTheme'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const isMenuOpen = ref(false)
const { theme, toggleTheme } = useTheme()

const guestNavigationItems = [
    {
      label: 'Login',
      to: { name: 'login' },
    },
    {
        label: 'Registro',
        to: { name: 'register' },
    },
]

const navigationItems = computed(() => {
    if (!auth.isAuthenticated) {
        return guestNavigationItems
    }

    return getNavigationByRole(router, auth.userRole)
})

const isCurrentRoute = (item) => route.name === item.to.name

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value
}

const closeMenu = () => {
    isMenuOpen.value = false
}

const onKeydown = (event) => {
    if (event.key === 'Escape') {
        closeMenu()
    }
}

watch(
    () => route.fullPath,
    () => {
        closeMenu()
    }
)
</script>

<template>
    <header class="header" @keydown="onKeydown">
        <div class="header__container">
            <RouterLink
                class="header__brand"
                :to="{ name: 'inicio' }"
                @click="closeMenu"
            >
                Mi Turno
            </RouterLink>

            <div class="header__controls">
                <button
                    type="button"
                    class="header__theme-toggle"
                    :aria-pressed="theme === 'dark' ? 'true' : 'false'"
                    :aria-label="theme === 'dark' ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
                    @click="toggleTheme"
                >
                    <span class="header__theme-icon" aria-hidden="true">
                        {{ theme === 'dark' ? '☀' : '☾' }}
                    </span>
                    <span class="header__theme-text">
                        {{ theme === 'dark' ? 'Claro' : 'Oscuro' }}
                    </span>
                </button>

                <button
                    v-if="navigationItems.length"
                    type="button"
                    class="header__menu-button"
                    :aria-expanded="isMenuOpen ? 'true' : 'false'"
                    aria-controls="primary-navigation"
                    :aria-label="isMenuOpen ? 'Cerrar menú principal' : 'Abrir menú principal'"
                    @click="toggleMenu"
                >
                    <span class="header__menu-button-line"></span>
                    <span class="header__menu-button-line"></span>
                    <span class="header__menu-button-line"></span>
                </button>
            </div>

            <nav
                v-if="navigationItems.length"
                id="primary-navigation"
                class="header__nav"
                :class="{ 'header__nav--open': isMenuOpen }"
                aria-label="Principal"
            >
                <ul class="header__list">
                    <li
                        v-for="(item, index) in navigationItems"
                        :key="item?.label || `nav-${index}`"
                        class="header__item"
                    >
                        <RouterLink
                            v-if="item"
                            :to="item.to"
                            class="header__link"
                            :aria-current="isCurrentRoute(item) ? 'page' : undefined"
                            @click="closeMenu"
                        >
                            {{ item.label }}
                        </RouterLink>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
</template>