<script setup>
// Props recibidas desde el componente padre.
// Este formulario no gestiona su estado completo internamente,
// sino que actúa como componente controlado: recibe datos,
// estados de carga, errores y opciones desde fuera.
const props = defineProps({
    saving: {
        type: Boolean,
        default: false,
    },
    servicios: {
        type: Array,
        default: () => [],
    },
    empleados: {
        type: Array,
        default: () => [],
    },
    disponibilidad: {
        type: Array,
        default: () => [],
    },
    disponibilidadConsultada: {
        type: Boolean,
        default: false,
    },
    loadingDisponibilidad: {
        type: Boolean,
        default: false,
    },
    loadingEmpleadosDisponibilidad: {
        type: Boolean,
        default: false,
    },
    
    // Objeto principal del formulario de creación.
    // El estado se mantiene en el padre y se pasa al hijo como prop.
    formularioCrear: {
        type: Object,
        required: true,
    },
    
    // Errores de validación o negocio asociados al formulario.
    erroresCrear: {
        type: Object,
        required: true,
    },

    // Estado de búsqueda de cliente para autocompletado.
    clienteQuery: {
        type: String,
        default: '',
    },
    clientesEncontrados: {
        type: Array,
        default: () => [],
    },
    clienteSeleccionado: {
        type: Object,
        default: null,
    },
    loadingClientes: {
        type: Boolean,
        default: false,
    },
    mostrarSugerenciasClientes: {
        type: Boolean,
        default: false,
    },

    // Identificadores y estado activo para accesibilidad de la lista de cliente.
    clienteListboxId: {
        type: String,
        default: 'crear-cliente-listbox',
    },
    clienteActivoId: {
        type: String,
        default: undefined,
    },
    clienteActivoIndex: {
        type: Number,
        default: -1,
    },

    // Información derivada del servicio actualmente seleccionado.
    servicioSeleccionadoCrear: {
        type: Object,
        default: null,
    },

    // Funciones auxiliares recibidas del padre para construir etiquetas y descripciones de opciones en listas de empleados/clientes.
    nombreEmpleadoOpcion: {
        type: Function,
        required: true,
    },
    nombreClienteOpcion: {
        type: Function,
        required: true,
    },
    descripcionClienteOpcion: {
        type: Function,
        required: true,
    },
})

// Eventos emitidos al componente padre.
// El padre es quien decide cómo reaccionar: guardar, cancelar, consultar disponibilidad o gestionar la búsqueda de clientes.
const emit = defineEmits([
    'submit',
    'cancel',
    'consult-availability',
    'client-input',
    'client-focus',
    'client-blur',
    'client-keydown',
    'select-client',
    'update:cliente-query',
])

// Envía la acción principal de guardado.
// El componente hijo no realiza el guardado directamente, solo notifica al padre que debe procesarlo.
const handleSubmit = () => {
    emit('submit')
}

// Gestiona la escritura en el campo de búsqueda de clientes.
// 1. Emite el nuevo valor para actualizar el estado en el padre.
// 2. Emite un segundo evento para disparar la lógica asociada a la búsqueda.
const handleClienteInput = (event) => {
    emit('update:cliente-query', event.target.value)
    emit('client-input')
}
</script>

<template>
    <form
        class="create-reservation-form"
        :aria-busy="saving ? 'true' : 'false'"
        novalidate
        @submit.prevent="handleSubmit"
    >
        <div class="create-reservation-form__field create-reservation-form__field--full">
            <label class="create-reservation-form__label" for="crear-cliente-busqueda">
                Cliente
            </label>

            <div class="create-reservation-form__combobox">
                <input
                    id="crear-cliente-busqueda"
                    :value="clienteQuery"
                    class="create-reservation-form__input"
                    :class="{ 'create-reservation-form__input--error': erroresCrear.usuarioid }"
                    type="text"
                    placeholder="Busca por nombre, apellidos, email o teléfono"
                    role="combobox"
                    aria-autocomplete="list"
                    :aria-expanded="mostrarSugerenciasClientes ? 'true' : 'false'"
                    :aria-controls="clienteListboxId"
                    :aria-activedescendant="clienteActivoId"
                    :aria-invalid="erroresCrear.usuarioid ? 'true' : 'false'"
                    :aria-describedby="erroresCrear.usuarioid ? 'crear-cliente-error crear-cliente-help' : 'crear-cliente-help'"
                    @input="handleClienteInput"
                    @focus="$emit('client-focus')"
                    @blur="$emit('client-blur')"
                    @keydown="$emit('client-keydown', $event)"
                />

                <p id="crear-cliente-help" class="create-reservation-form__help">
                    Escribe al menos 2 caracteres y selecciona un cliente de la lista.
                </p>

                <div
                    v-if="loadingClientes"
                    class="create-reservation-form__loading-text"
                    role="status"
                    aria-live="polite"
                >
                    Buscando clientes...
                </div>

                <ul
                    v-if="mostrarSugerenciasClientes && clientesEncontrados.length"
                    :id="clienteListboxId"
                    class="create-reservation-form__suggestions"
                    role="listbox"
                >
                    <li
                        v-for="(cliente, index) in clientesEncontrados"
                        :id="`crear-cliente-opcion-${cliente.id}`"
                        :key="cliente.id"
                        class="create-reservation-form__suggestion"
                        :class="{ 'create-reservation-form__suggestion--active': index === clienteActivoIndex }"
                        role="option"
                        :aria-selected="index === clienteActivoIndex ? 'true' : 'false'"
                        @mousedown.prevent="$emit('select-client', cliente)"
                    >
                        <span class="create-reservation-form__suggestion-name">
                            {{ nombreClienteOpcion(cliente) }}
                        </span>
                        <span class="create-reservation-form__suggestion-meta">
                            {{ descripcionClienteOpcion(cliente) }}
                        </span>
                    </li>
                </ul>
            </div>

            <p
                v-if="clienteSeleccionado"
                class="create-reservation-form__selected-client"
            >
                Cliente seleccionado: {{ nombreClienteOpcion(clienteSeleccionado) }}
                <span v-if="descripcionClienteOpcion(clienteSeleccionado)">
                    — {{ descripcionClienteOpcion(clienteSeleccionado) }}
                </span>
            </p>

            <p
                v-if="erroresCrear.usuarioid"
                id="crear-cliente-error"
                class="create-reservation-form__error"
            >
                {{ erroresCrear.usuarioid }}
            </p>
        </div>

        <div class="create-reservation-form__field create-reservation-form__field--full">
            <label class="create-reservation-form__label" for="crear-servicio">
                Servicio
            </label>

            <select
                id="crear-servicio"
                v-model="formularioCrear.servicioid"
                class="create-reservation-form__input"
                :class="{ 'create-reservation-form__input--error': erroresCrear.servicioid }"
                :aria-invalid="erroresCrear.servicioid ? 'true' : 'false'"
                :aria-describedby="erroresCrear.servicioid ? 'crear-servicio-error' : undefined"
            >
                <option value="">Selecciona un servicio</option>
                <option
                    v-for="servicio in servicios"
                    :key="servicio.id"
                    :value="servicio.id"
                >
                    {{ servicio.nombre }} - {{ servicio.duracionminutos || servicio.duracion_minutos }} min - {{ servicio.precio }}
                </option>
            </select>

            <p
                v-if="erroresCrear.servicioid"
                id="crear-servicio-error"
                class="create-reservation-form__error"
            >
                {{ erroresCrear.servicioid }}
            </p>
        </div>

        <article
            v-if="servicioSeleccionadoCrear"
            class="create-reservation-form__summary create-reservation-form__field--full"
            aria-label="Resumen del servicio seleccionado"
        >
            <h3 class="create-reservation-form__summary-title">
                {{ servicioSeleccionadoCrear.nombre }}
            </h3>

            <p class="create-reservation-form__summary-text">
                {{ servicioSeleccionadoCrear.descripcion }}
            </p>

            <dl class="create-reservation-form__summary-meta">
                <div>
                    <dt>Duración</dt>
                    <dd>{{ servicioSeleccionadoCrear.duracionminutos || servicioSeleccionadoCrear.duracion_minutos }} min</dd>
                </div>
                <div>
                    <dt>Precio</dt>
                    <dd>{{ servicioSeleccionadoCrear.precio }}</dd>
                </div>
            </dl>
        </article>

        <div class="create-reservation-form__field">
            <label class="create-reservation-form__label" for="crear-empleado">
                Empleado
            </label>

            <select
                id="crear-empleado"
                v-model="formularioCrear.empleadoid"
                class="create-reservation-form__input"
                :disabled="!empleados.length || loadingEmpleadosDisponibilidad"
            >
                <option value="">Cualquier profesional disponible</option>
                <option
                    v-for="empleado in empleados"
                    :key="empleado.id"
                    :value="empleado.id"
                >
                    {{ nombreEmpleadoOpcion(empleado) }}
                </option>
            </select>
        </div>

        <div class="create-reservation-form__field">
            <label class="create-reservation-form__label" for="crear-fecha">
                Fecha
            </label>

            <input
                id="crear-fecha"
                v-model="formularioCrear.fecha"
                class="create-reservation-form__input"
                :class="{ 'create-reservation-form__input--error': erroresCrear.fecha }"
                :aria-invalid="erroresCrear.fecha ? 'true' : 'false'"
                :aria-describedby="erroresCrear.fecha ? 'crear-fecha-error' : undefined"
                type="date"
            />

            <p
                v-if="erroresCrear.fecha"
                id="crear-fecha-error"
                class="create-reservation-form__error"
            >
                {{ erroresCrear.fecha }}
            </p>
        </div>

        <div class="create-reservation-form__actions create-reservation-form__actions--full">
            <button
                type="button"
                class="create-reservation-form__button create-reservation-form__button--secondary"
                :disabled="loadingDisponibilidad"
                @click="$emit('consult-availability')"
            >
                {{ loadingDisponibilidad ? 'Consultando...' : 'Consultar disponibilidad' }}
            </button>
        </div>

        <fieldset
            v-if="disponibilidad.length"
            class="create-reservation-form__field create-reservation-form__field--full"
            :aria-describedby="erroresCrear.hora ? 'crear-hora-error' : undefined"
        >
            <legend class="create-reservation-form__label">
                Horas disponibles
            </legend>

            <div class="create-reservation-form__hours-grid">
                <label
                    v-for="hora in disponibilidad"
                    :key="hora"
                    class="create-reservation-form__hour-option"
                    :class="{ 'create-reservation-form__hour-option--selected': formularioCrear.hora === hora }"
                >
                    <input
                        v-model="formularioCrear.hora"
                        class="create-reservation-form__hour-input"
                        type="radio"
                        name="hora-disponible-admin"
                        :value="hora"
                    />
                    <span>{{ hora }}</span>
                </label>
            </div>

            <p
                v-if="erroresCrear.hora"
                id="crear-hora-error"
                class="create-reservation-form__error"
            >
                {{ erroresCrear.hora }}
            </p>
        </fieldset>

        <div
            v-else-if="disponibilidadConsultada && !loadingDisponibilidad"
            class="create-reservation-form__empty"
            role="status"
            aria-live="polite"
        >
            No hay horas disponibles para esa fecha.
        </div>

        <div class="create-reservation-form__field create-reservation-form__field--full">
            <label class="create-reservation-form__label" for="crear-notas">
                Notas
            </label>

            <textarea
                id="crear-notas"
                v-model="formularioCrear.notas"
                class="create-reservation-form__textarea"
                rows="4"
                placeholder="Añade una nota para la reserva"
            ></textarea>
        </div>

        <footer class="create-reservation-form__actions create-reservation-form__actions--full">
            <button
                type="button"
                class="create-reservation-form__button create-reservation-form__button--secondary"
                :disabled="saving"
                @click="$emit('cancel')"
            >
                Cancelar
            </button>

            <button
                type="submit"
                class="create-reservation-form__button create-reservation-form__button--primary"
                :disabled="saving"
            >
                {{ saving ? 'Guardando...' : 'Crear reserva' }}
            </button>
        </footer>
    </form>
</template>