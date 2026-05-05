<script setup>
import { computed, onMounted, ref } from 'vue'
import { getEmpleados, getEmpleado, createEmpleado, syncServiciosEmpleado, updateEmpleado, deleteEmpleado, } from '../../api/empleados'
import { getServicios } from '../../api/servicios'

import EmployeeCard from '../../components/admin/employees/EmployeeCard.vue'
import EmployeeServicesModal from '../../components/admin/employees/EmployeeServicesModal.vue'
import EmployeeProfileModal from '../../components/admin/employees/EmployeeProfileModal.vue'
import StatusMessage from '../../components/feedback/StatusMessage.vue'

// Estado principal de la vista.
const empleados = ref([])
const servicios = ref([])
const loading = ref(false)
const saving = ref(false)
const deletingEmployeeId = ref(null)

// Estado de edición/visualización de modales.
const empleadoEditando = ref(null)
const empleadoPerfilEditando = ref(null)
const mostrarModalPerfil = ref(false)
const mostrarModalServicios = ref(false)
const modoPerfil = ref('crear')

// Servicios seleccionados para el modal de asignación.
const serviciosSeleccionados = ref([])

const error = ref(null)
const success = ref(null)

// Carga inicial de empleados y servicios.
// Se usa Promise.all para resolver ambas peticiones en paralelo
const cargarDatos = async () => {
    loading.value = true
    error.value = null

    try {
        const [listaEmpleados, listaServicios] = await Promise.all([
            getEmpleados(),
            getServicios(),
        ])

        empleados.value = listaEmpleados
        servicios.value = listaServicios
    } catch (err) {
        console.error(err)
        error.value = 'No se pudieron cargar los empleados o los servicios.'
    } finally {
        loading.value = false
    }
}

// Abre el modal de servicios cargando antes el detalle completo del empleado.
// Esto asegura que la asignación se haga con datos actualizados.
const abrirModalServicios = async (empleado) => {
    error.value = null
    success.value = null

    try {
        empleadoEditando.value = await getEmpleado(empleado.id)
        serviciosSeleccionados.value = empleadoEditando.value.servicios.map((s) => s.id)
        mostrarModalServicios.value = true
    } catch (err) {
        console.error(err)
        error.value = 'No se pudo cargar la información del empleado.'
    }
}

// Limpia el estado del modal de servicios al cerrarlo.
const cerrarModalServicios = () => {
    mostrarModalServicios.value = false
    empleadoEditando.value = null
    serviciosSeleccionados.value = []
}

// Prepara el modal de perfil en modo creación.
const abrirModalCrear = () => {
    error.value = null
    success.value = null
    modoPerfil.value = 'crear'
    empleadoPerfilEditando.value = null
    mostrarModalPerfil.value = true
}

// Prepara el modal de perfil en modo edición cargando el detalle del empleado.
const abrirModalEdicion = async (empleado) => {
    error.value = null
    success.value = null
    modoPerfil.value = 'editar'

    try {
        empleadoPerfilEditando.value = await getEmpleado(empleado.id)
        mostrarModalPerfil.value = true
    } catch (err) {
        console.error(err)
        error.value = 'No se pudo cargar el perfil del empleado.'
    }
}

// Limpia el estado del modal de perfil.
const cerrarModalPerfil = () => {
    mostrarModalPerfil.value = false
    empleadoPerfilEditando.value = null
    modoPerfil.value = 'crear'
}

// Guarda la relación entre empleado y servicios seleccionados.
const guardarServicios = async () => {
    if (!empleadoEditando.value) return

    saving.value = true
    error.value = null
    success.value = null

    try {
        await syncServiciosEmpleado(
            empleadoEditando.value.id,
            serviciosSeleccionados.value,
        )

        await cargarDatos()
        cerrarModalServicios()
        success.value = 'Servicios actualizados correctamente.'
    } catch (err) {
        console.error(err)
        error.value = 'No se pudieron guardar los cambios de servicios.'
    } finally {
        saving.value = false
    }
}

// Guarda el perfil del empleado.
// En modo crear llama a createEmpleado; en modo editar, a updateEmpleado.
// Si el backend devuelve errores de validación, se mapean a vee-validate
const guardarPerfil = async (payload, setErrors) => {
    saving.value = true
    error.value = null
    success.value = null

    try {
        if (modoPerfil.value === 'crear') {
            await createEmpleado(payload)
            success.value = 'Empleado creado correctamente.'
        } else {
            await updateEmpleado(empleadoPerfilEditando.value.id, payload)
            success.value = 'Perfil del empleado actualizado correctamente.'
        }

        await cargarDatos()
        cerrarModalPerfil()
    } catch (err) {
        console.error(err.response?.data || err)

        const backendErrors = err.response?.data?.errors

        if (backendErrors && setErrors) {
            setErrors({
                nombre: backendErrors.nombre?.[0],
                apellidos: backendErrors.apellidos?.[0],
                email: backendErrors.email?.[0],
                telefono: backendErrors.telefono?.[0],
                password: backendErrors.password?.[0],
                especialidades: backendErrors.especialidades?.[0],
                fecha_contratacion: backendErrors.fecha_contratacion?.[0],
                activo: backendErrors.activo?.[0],
                fecha_nacimiento: backendErrors.fecha_nacimiento?.[0],
            })
        } else {
            error.value =
                modoPerfil.value === 'crear'
                    ? 'No se pudo crear el empleado.'
                    : 'No se pudo actualizar el perfil del empleado.'
                }
    } finally {
        saving.value = false
    }
}

// Elimina un empleado tras confirmación del usuario.
const eliminarEmpleadoConfirmado = async (empleado) => {
    const nombreCompleto = `${empleado.usuario?.nombre || ''} ${empleado.usuario?.apellidos || ''}`.trim()

    if (!window.confirm(`¿Seguro que quieres eliminar a ${nombreCompleto}?`)) return

    deletingEmployeeId.value = empleado.id
    error.value = null
    success.value = null

    try {
        await deleteEmpleado(empleado.id)
        await cargarDatos()
        success.value = 'Empleado eliminado correctamente.'
    } catch (err) {
        console.error(err.response?.data || err)
        error.value = 'No se pudo eliminar el empleado.'
    } finally {
        deletingEmployeeId.value = null
    }
}

const empleadosDisponibles = computed(() => empleados.value || [])

// Carga inicial al montar la vista.
onMounted(cargarDatos)
</script>

<template>
    <main class="admin-employees">
        <section class="admin-employees__container" aria-labelledby="admin-employees-title">
            <header class="admin-employees__header">
                <div class="admin-employees__heading">
                    <h1 id="admin-employees-title" class="admin-employees__title">
                        Gestión de empleados
                    </h1>
                    <p class="admin-employees__intro">
                        Consulta empleados, crea nuevos perfiles, edita sus datos y asigna servicios.
                    </p>
                </div>

                <div class="admin-employees__toolbar">
                    <button
                        type="button"
                        class="admin-employees__button admin-employees__button--primary"
                        @click="abrirModalCrear"
                        :disabled="saving || deletingEmployeeId !== null"
                    >
                        Nuevo empleado
                    </button>
                </div>
            </header>

            <StatusMessage
                v-if="loading"
                variant="default"
                role="status"
                live="polite"
            >
                Cargando empleados...
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

            <div
                v-if="!loading && empleadosDisponibles.length"
                class="admin-employees__grid"
            >
                <EmployeeCard
                    v-for="empleado in empleadosDisponibles"
                    :key="empleado.id"
                    :empleado="empleado"
                    :deleting-employee-id="deletingEmployeeId"
                    @edit-services="abrirModalServicios"
                    @edit-profile="abrirModalEdicion"
                    @delete="eliminarEmpleadoConfirmado"
                />
            </div>

            <p
                v-else-if="!loading && !empleadosDisponibles.length"
                class="admin-employees__empty"
                role="status"
                aria-live="polite"
            >
                No hay empleados disponibles.
            </p>
        </section>

        <EmployeeServicesModal
            :visible="mostrarModalServicios"
            :empleado="empleadoEditando"
            :servicios="servicios"
            :servicios-seleccionados="serviciosSeleccionados"
            :saving="saving"
            @close="cerrarModalServicios"
            @save="guardarServicios"
            @update:serviciosSeleccionados="serviciosSeleccionados = $event"
        />

        <EmployeeProfileModal
            :visible="mostrarModalPerfil"
            :modo="modoPerfil"
            :empleado="empleadoPerfilEditando"
            :saving="saving"
            @close="cerrarModalPerfil"
            @submit="guardarPerfil"
        />
    </main>
</template>