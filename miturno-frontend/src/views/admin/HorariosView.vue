<script setup>
import { ref, computed, onMounted, nextTick, onBeforeUnmount } from 'vue'
import { getEmpleados } from '../../api/empleados'
import { getHorarios, createHorario, updateHorario, deleteHorario } from '../../api/horarios'

import EmployeeScheduleFilter from '../../components/admin/schedule/EmployeeScheduleFilter.vue'
import ScheduleWeekGrid from '../../components/admin/schedule/ScheduleWeekGrid.vue'
import ScheduleModal from '../../components/admin/schedule/ScheduleModal.vue'
import StatusMessage from '../../components/feedback/StatusMessage.vue'

const empleados = ref([])
const horarios = ref([])
const loading = ref(false)
const saving = ref(false)
const deletingHorarioId = ref(null)

const empleadoSeleccionadoId = ref('')
const mostrarModalHorario = ref(false)
const horarioEditando = ref(null)
const modoHorario = ref('crear')

const error = ref(null)
const success = ref(null)

const lastTriggerRef = ref(null)

const diasSemana = [
    { value: 0, label: 'Domingo' },
    { value: 1, label: 'Lunes' },
    { value: 2, label: 'Martes' },
    { value: 3, label: 'Miércoles' },
    { value: 4, label: 'Jueves' },
    { value: 5, label: 'Viernes' },
    { value: 6, label: 'Sábado' },
]

const empleadosActivos = computed(() =>
    empleados.value.filter((empleado) => empleado.activo && empleado.usuario?.activo !== false),
)

const empleadoSeleccionado = computed(() =>
    empleados.value.find((empleado) => String(empleado.id) === String(empleadoSeleccionadoId.value)),
)

const horariosFiltrados = computed(() => {
    if (!empleadoSeleccionadoId.value) return []
    return horarios.value
        .filter((horario) => String(horario.empleado_id) === String(empleadoSeleccionadoId.value))
        .sort((a, b) => {
            if (a.dia_semana !== b.dia_semana) return a.dia_semana - b.dia_semana
            return a.hora_inicio.localeCompare(b.hora_inicio)
        })
})

const horariosPorDia = computed(() =>
    diasSemana.map((dia) => ({
        ...dia,
        horarios: horariosFiltrados.value.filter((horario) => horario.dia_semana === dia.value),
    })),
)

const tituloModal = computed(() =>
    modoHorario.value === 'crear' ? 'Nueva franja horaria' : 'Editar franja horaria',
)

const cargarDatos = async () => {
    loading.value = true
    error.value = null

    try {
        const [listaEmpleados, listaHorarios] = await Promise.all([
            getEmpleados(),
            getHorarios(),
        ])

        empleados.value = listaEmpleados
        horarios.value = listaHorarios

        if (!empleadoSeleccionadoId.value && listaEmpleados.length) {
            const primerEmpleadoActivo = listaEmpleados.find(
                (empleado) => empleado.activo && empleado.usuario?.activo !== false,
            )
            empleadoSeleccionadoId.value = primerEmpleadoActivo ? String(primerEmpleadoActivo.id) : ''
        }
    } catch (err) {
        console.error(err)
        error.value = 'No se pudieron cargar los empleados o los horarios.'
    } finally {
        loading.value = false
    }
}

const abrirModalCrear = async (event = null, dia = null) => {
    lastTriggerRef.value = event?.currentTarget || document.activeElement
    error.value = null
    success.value = null
    modoHorario.value = 'crear'
    horarioEditando.value = null
    mostrarModalHorario.value = true
}

const abrirModalEdicion = async (horario, event = null) => {
    lastTriggerRef.value = event?.currentTarget || document.activeElement
    error.value = null
    success.value = null
    modoHorario.value = 'editar'
    horarioEditando.value = horario
    mostrarModalHorario.value = true
}

const cerrarModal = () => {
    mostrarModalHorario.value = false
    horarioEditando.value = null
    modoHorario.value = 'crear'

    if (lastTriggerRef.value && typeof lastTriggerRef.value.focus === 'function') {
        lastTriggerRef.value.focus()
    }
}

const guardarHorario = async (payload, setErrors) => {
    saving.value = true
    error.value = null
    success.value = null

    try {
        const horarioPayload = {
            ...payload,
            empleado_id: Number(payload.empleado_id),
            dia_semana: Number(payload.dia_semana),
        }

        if (modoHorario.value === 'crear') {
            await createHorario(horarioPayload)
            success.value = 'Horario creado correctamente.'
        } else {
            await updateHorario(horarioEditando.value.id, horarioPayload)
            success.value = 'Horario actualizado correctamente.'
        }

        await cargarDatos()
        cerrarModal()
    } catch (err) {
        console.error(err.response?.data || err)

        const backendErrors = err.response?.data?.errors

        if (backendErrors && setErrors) {
            setErrors({
                empleado_id: backendErrors.empleado_id?.[0],
                dia_semana: backendErrors.dia_semana?.[0],
                hora_inicio: backendErrors.hora_inicio?.[0],
                hora_fin: backendErrors.hora_fin?.[0],
                tipo: backendErrors.tipo?.[0],
                activo: backendErrors.activo?.[0],
            })
        } else {
            error.value =
                modoHorario.value === 'crear'
                ? 'No se pudo crear el horario.'
                : 'No se pudo actualizar el horario.'
        }
    } finally {
        saving.value = false
    }
}

const eliminarHorarioConfirmado = async (horario) => {
    if (!window.confirm('¿Seguro que quieres eliminar esta franja horaria?')) return

    deletingHorarioId.value = horario.id
    error.value = null
    success.value = null

    try {
        await deleteHorario(horario.id)
        await cargarDatos()
        success.value = 'Horario eliminado correctamente.'
    } catch (err) {
        console.error(err.response?.data || err)
        error.value = 'No se pudo eliminar el horario.'
    } finally {
        deletingHorarioId.value = null
    }
}

onMounted(cargarDatos)
</script>

<template>
    <main class="admin-schedules">
        <section class="admin-schedules__container" aria-labelledby="admin-schedules-title">
            <header class="admin-schedules__header">
                <div class="admin-schedules__heading">
                    <h1 id="admin-schedules-title" class="admin-schedules__title">
                        Gestión de horarios
                    </h1>
                    <p class="admin-schedules__intro">
                        Configura las franjas horarias de trabajo de cada empleado.
                    </p>
                </div>

                <EmployeeScheduleFilter
                    :empleados="empleadosActivos"
                    :empleado-seleccionado-id="empleadoSeleccionadoId"
                    :loading="loading"
                    :saving="saving"
                    :modal-abierto="mostrarModalHorario"
                    @update:empleadoSeleccionadoId="empleadoSeleccionadoId = $event"
                    @create-schedule="abrirModalCrear"
                />
            </header>

            <StatusMessage
                v-if="loading"
                variant="default"
                role="status"
                live="polite"
            >
                Cargando horarios...
            </StatusMessage>

            <StatusMessage
                v-if="error"
                variant="error"
                role="alert"
                live="assertive"
            >
                {{ error }}
            </StatusMessage>

            <StatusMessage
                v-if="success"
                variant="success"
                role="status"
                live="polite"
            >
                {{ success }}
            </StatusMessage>

            <ScheduleWeekGrid
                v-if="!loading && empleadoSeleccionado"
                :dias-semana="horariosPorDia"
                :empleado-seleccionado="empleadoSeleccionado"
                :deleting-horario-id="deletingHorarioId"
                :saving="saving"
                @edit-schedule="abrirModalEdicion"
                @delete-schedule="eliminarHorarioConfirmado"
                @create-schedule="abrirModalCrear"
            />

            <p
                v-else-if="!loading"
                class="admin-schedules__empty"
                role="status"
                aria-live="polite"
            >
                Selecciona un empleado para gestionar sus horarios.
            </p>
        </section>

        <ScheduleModal
            :visible="mostrarModalHorario"
            :modo="modoHorario"
            :horario-editando="horarioEditando"
            :empleado-seleccionado-id="empleadoSeleccionadoId"
            :empleados="empleadosActivos"
            :dias-semana="diasSemana"
            :saving="saving"
            :titulo-modal="tituloModal"
            @close="cerrarModal"
            @submit="guardarHorario"
        />
    </main>
</template>