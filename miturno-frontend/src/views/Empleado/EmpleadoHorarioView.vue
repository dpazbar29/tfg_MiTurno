<script setup>
import { ref, computed, onMounted } from 'vue'
import { getMiHorario } from '../../api/horarios'
import StatusMessage from '@/components/feedback/StatusMessage.vue'

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
            <header class="empleado-horario__header">
                <div class="empleado-horario__heading">
                    <p class="empleado-horario__eyebrow">Mi horario</p>
                        <h1 class="empleado-horario__title">Horario semanal</h1>
                        <p class="empleado-horario__subtitle">
                            Consulta tus franjas de trabajo asignadas para cada día de la semana.
                        </p>
                </div>
            </header>

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

            <section
                v-if="!loading && !error"
                class="empleado-horario__grid"
                aria-label="Horario semanal del empleado"
            >
                <article
                    v-for="dia in diasOrdenados"
                    :key="dia.key"
                    class="empleado-horario__day-card"
                >
                    <header class="empleado-horario__day-header">
                        <h2 class="empleado-horario__day-title">{{ dia.label }}</h2>
                    </header>

                    <div
                        v-if="horariosPorDia[dia.key] && horariosPorDia[dia.key].length"
                        class="empleado-horario__slots"
                    >
                        <div
                            v-for="horario in horariosPorDia[dia.key]"
                            :key="horario.id"
                            class="empleado-horario__slot"
                        >
                            <div class="empleado-horario__slot-main">
                                <span class="empleado-horario__slot-time">
                                    {{ formatearHora(horario.hora_inicio) }} - {{ formatearHora(horario.hora_fin) }}
                                </span>
                            </div>

                            <div class="empleado-horario__slot-meta">
                                <span class="empleado-horario__slot-type">
                                    {{ etiquetaTipo(horario.tipo) }}
                                </span>

                                <span
                                    class="empleado-horario__slot-status"
                                    :class="{ 'empleado-horario__slot-status--inactive': !horario.activo }"
                                    :data-status="horario.activo ? 'activo' : 'inactivo'"
                                >
                                    {{ horario.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <p v-else class="empleado-horario__empty">
                        Sin horario asignado.
                    </p>
                </article>
            </section>
        </section>
    </main>
</template>