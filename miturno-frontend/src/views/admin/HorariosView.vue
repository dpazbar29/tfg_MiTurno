<script setup>
import { ref, computed, onMounted, nextTick, onBeforeUnmount } from 'vue'
import { getEmpleados } from '../../api/empleados'
import { getHorarios, createHorario, updateHorario, deleteHorario } from '../../api/horarios'

import EmployeeScheduleFilter from '../../components/admin/schedule/EmployeeScheduleFilter.vue'
import ScheduleWeekGrid from '../../components/admin/schedule/ScheduleWeekGrid.vue'
import ScheduleModal from '../../components/admin/schedule/ScheduleModal.vue'
import StatusMessage from '../../components/feedback/StatusMessage.vue'

// Estado principal de la vista.
const empleados = ref([])
const horarios = ref([])
const loading = ref(false)
const saving = ref(false)
const deletingHorarioId = ref(null)

// Estado de filtro y modal.
const empleadoSeleccionadoId = ref('')
const mostrarModalHorario = ref(false)
const horarioEditando = ref(null)
const modoHorario = ref('crear')

// Mensajes globales.
const error = ref(null)
const success = ref(null)

// Referencia al elemento que abrió el modal.
// Se usa para devolverle el foco al cerrar.
const lastTriggerRef = ref(null)

// Definición estática de los días de la semana para construir la cuadrícula.
const diasSemana = [
    { value: 0, label: 'Domingo' },
    { value: 1, label: 'Lunes' },
    { value: 2, label: 'Martes' },
    { value: 3, label: 'Miércoles' },
    { value: 4, label: 'Jueves' },
    { value: 5, label: 'Viernes' },
    { value: 6, label: 'Sábado' },
]

// Lista derivada de empleados activos.
// Excluye empleados desactivados o usuarios asociados inactivos.
const empleadosActivos = computed(() =>
    empleados.value.filter((empleado) => empleado.activo && empleado.usuario?.activo !== false),
)

// Devuelve el objeto completo del empleado actualmente seleccionado en el filtro.
const empleadoSeleccionado = computed(() =>
    empleados.value.find((empleado) => String(empleado.id) === String(empleadoSeleccionadoId.value)),
)

// Filtra los horarios por empleado seleccionado y los ordena:
// 1. por día de la semana,
// 2. y dentro del día, por hora de inicio.
const horariosFiltrados = computed(() => {
    if (!empleadoSeleccionadoId.value) return []
    return horarios.value
        .filter((horario) => String(horario.empleado_id) === String(empleadoSeleccionadoId.value))
        .sort((a, b) => {
            if (a.dia_semana !== b.dia_semana) return a.dia_semana - b.dia_semana
            return a.hora_inicio.localeCompare(b.hora_inicio)
        })
})

// Agrupa los horarios filtrados por día de la semana.
const horariosPorDia = computed(() =>
    diasSemana.map((dia) => ({
        ...dia,
        horarios: horariosFiltrados.value.filter((horario) => horario.dia_semana === dia.value),
    })),
)

// Título dinámico del modal según si se crea o se edita una franja.
const tituloModal = computed(() =>
    modoHorario.value === 'crear' ? 'Nueva franja horaria' : 'Editar franja horaria',
)

// Carga inicial de empleados y horarios en paralelo.
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

// Abre el modal en modo creación.
// Guarda también el elemento que disparó la acción para restaurar foco después.
const abrirModalCrear = async (event = null, dia = null) => {
    lastTriggerRef.value = event?.currentTarget || document.activeElement
    error.value = null
    success.value = null
    modoHorario.value = 'crear'
    horarioEditando.value = null
    mostrarModalHorario.value = true
}

// Abre el modal en modo edición con la franja seleccionada.
const abrirModalEdicion = async (horario, event = null) => {
    lastTriggerRef.value = event?.currentTarget || document.activeElement
    error.value = null
    success.value = null
    modoHorario.value = 'editar'
    horarioEditando.value = horario
    mostrarModalHorario.value = true
}

// Cierra el modal y devuelve el foco al elemento que lo abrió.
const cerrarModal = () => {
    mostrarModalHorario.value = false
    horarioEditando.value = null
    modoHorario.value = 'crear'

    if (lastTriggerRef.value && typeof lastTriggerRef.value.focus === 'function') {
        lastTriggerRef.value.focus()
    }
}

// Guarda una franja nueva o actualiza una existente.
// Convierte ciertos campos a Number para evitar inconsistencias de tipo al enviar datos al backend.
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

        // Si el backend devuelve errores por campo, se mapean al formulario.
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

// Elimina una franja horaria tras confirmación.
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

// Carga inicial al montar la vista.
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