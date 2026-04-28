<script setup>
import { ref, computed, onMounted } from 'vue'
import { getMiHorario } from '../../api/horarios'
import StatusMessage from '@/components/feedback/StatusMessage.vue'
import EmpleadoHorarioToolbar from '@/components/empleado/EmpleadoHorarioToolbar.vue'
import EmpleadoHorarioWeekGrid from '@/components/empleado/EmpleadoHorarioWeekGrid.vue'

const loading = ref(false)
const error = ref('')
const horarios = ref([])

const diasOrdenados = [
    { key: 1, label: 'Lunes' },
    { key: 2, label: 'Martes' },
    { key: 3, label: 'Miércoles' },
    { key: 4, label: 'Jueves' },
    { key: 5, label: 'Viernes' },
    { key: 6, label: 'Sábado' },
    { key: 0, label: 'Domingo' },
]

const cargarHorario = async () => {
    loading.value = true
    error.value = ''

    try {
        const data = await getMiHorario()
        horarios.value = Array.isArray(data) ? data : []
    } catch (err) {
        console.error(err.response?.data || err)
        error.value = err.response?.data?.message || 'No se pudo cargar tu horario.'
    } finally {
        loading.value = false
    }
}

const formatearHora = (hora) => {
    if (!hora) return ''

    const partes = String(hora).split(':')
    if (partes.length < 2) return hora

    return `${partes[0]}:${partes[1]}`
}

const etiquetaTipo = (tipo) => {
    if (!tipo) return 'Normal'

    const mapa = {
        normal: 'Normal',
        cierre: 'Cierre',
        festivo: 'Festivo',
    }

    return mapa[tipo] || tipo
}

const horariosPorDia = computed(() => {
    const agrupados = {}

    diasOrdenados.forEach((dia) => {
        agrupados[dia.key] = []
    })

    horarios.value.forEach((horario) => {
        const dia = Number(horario.dia_semana)

        if (!Number.isNaN(dia) && agrupados[dia]) {
            agrupados[dia].push(horario)
        }
    })

    Object.keys(agrupados).forEach((dia) => {
        agrupados[dia].sort((a, b) =>
            String(a.hora_inicio).localeCompare(String(b.hora_inicio)),
        )
    })

    return agrupados
})

onMounted(() => {
    cargarHorario()
})
</script>

<template>
    <main class="empleado-horario">
        <section class="empleado-horario__container">
            <EmpleadoHorarioToolbar
                eyebrow="Mi horario"
                title="Horario semanal"
                subtitle="Consulta tus franjas de trabajo asignadas para cada día de la semana."
            />

            <StatusMessage
                v-if="loading"
                variant="default"
                role="status"
                live="polite"
            >
                Cargando horario...
            </StatusMessage>

            <StatusMessage
                v-if="error"
                variant="error"
                role="alert"
                live="assertive"
            >
                {{ error }}
            </StatusMessage>

            <EmpleadoHorarioWeekGrid
                v-if="!loading && !error"
                :dias="diasOrdenados"
                :horarios-por-dia="horariosPorDia"
                :formatear-hora="formatearHora"
                :etiqueta-tipo="etiquetaTipo"
            />
        </section>
    </main>
</template>