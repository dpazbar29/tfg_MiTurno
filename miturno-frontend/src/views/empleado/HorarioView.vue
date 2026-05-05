<script setup>
import { ref, computed, onMounted } from 'vue'
import { getMiHorario } from '../../api/horarios'
import StatusMessage from '@/components/feedback/StatusMessage.vue'
import EmpleadoHorarioToolbar from '@/components/empleado/EmpleadoHorarioToolbar.vue'
import EmpleadoHorarioWeekGrid from '@/components/empleado/EmpleadoHorarioWeekGrid.vue'

// Estado principal de la vista.
const loading = ref(false)
const error = ref('')
const horarios = ref([])

// Orden visual deseado para la semana.
// Aunque el backend use 0=domingo, aquí se presenta primero lunes.
const diasOrdenados = [
    { key: 1, label: 'Lunes' },
    { key: 2, label: 'Martes' },
    { key: 3, label: 'Miércoles' },
    { key: 4, label: 'Jueves' },
    { key: 5, label: 'Viernes' },
    { key: 6, label: 'Sábado' },
    { key: 0, label: 'Domingo' },
]

// Carga el horario del empleado autenticado.
const cargarHorario = async () => {
    loading.value = true
    error.value = ''

    try {
        const data = await getMiHorario()

        // Garantiza que horarios siempre sea un array.
        horarios.value = Array.isArray(data) ? data : []
    } catch (err) {
        console.error(err.response?.data || err)
        error.value = err.response?.data?.message || 'No se pudo cargar tu horario.'
    } finally {
        loading.value = false
    }
}

// Formatea horas tipo HH:mm:ss a HH:mm.
// Si el valor no tiene el formato esperado, devuelve el original.
const formatearHora = (hora) => {
    if (!hora) return ''

    const partes = String(hora).split(':')
    if (partes.length < 2) return hora

    return `${partes[0]}:${partes[1]}`
}

// Traduce el tipo técnico del horario a una etiqueta legible.
const etiquetaTipo = (tipo) => {
    if (!tipo) return 'Normal'

    const mapa = {
        normal: 'Normal',
        cierre: 'Cierre',
        festivo: 'Festivo',
    }

    return mapa[tipo] || tipo
}

// Agrupa los horarios por día de la semana y ordena cada grupo por hora de inicio.
const horariosPorDia = computed(() => {
    const agrupados = {}

    // Inicializa todos los días para asegurar estructura estable, incluso si alguno no tiene franjas.
    diasOrdenados.forEach((dia) => {
        agrupados[dia.key] = []
    })

    // Reparte cada franja en su día correspondiente.
    horarios.value.forEach((horario) => {
        const dia = Number(horario.dia_semana)

        if (!Number.isNaN(dia) && agrupados[dia]) {
            agrupados[dia].push(horario)
        }
    })

    // Ordena cada día por hora de inicio.
    Object.keys(agrupados).forEach((dia) => {
        agrupados[dia].sort((a, b) =>
            String(a.hora_inicio).localeCompare(String(b.hora_inicio)),
        )
    })

    return agrupados
})

// Carga inicial al montar la vista.
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