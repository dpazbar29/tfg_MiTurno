<script setup>
import { ref, onMounted, nextTick, onBeforeUnmount } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/yup'
import * as yup from 'yup'
import {
  getServicios,
  crearServicio,
  actualizarServicio,
  eliminarServicio,
} from '../../api/servicios'

const servicios = ref([])
const loading = ref(false)
const modalVisible = ref(false)
const servicioEditando = ref(null)
const error = ref(null)
const success = ref(null)
const lastTriggerRef = ref(null)
const modalRef = ref(null)
const modalTitleRef = ref(null)

const validationSchema = toTypedSchema(
  yup.object({
    nombre: yup
      .string()
      .trim()
      .required('El nombre es obligatorio.'),
    descripcion: yup
      .string()
      .nullable(),
    duracion_minutos: yup
      .number()
      .typeError('La duración es obligatoria.')
      .required('La duración es obligatoria.')
      .min(1, 'La duración debe ser mayor que 0.'),
    precio: yup
      .number()
      .typeError('El precio es obligatorio.')
      .required('El precio es obligatorio.')
      .min(0, 'El precio no puede ser negativo.'),
    activo: yup
      .boolean()
      .required(),
  }),
)

const {
  defineField,
  handleSubmit,
  errors,
  isSubmitting,
  resetForm,
} = useForm({
  validationSchema,
  initialValues: {
    nombre: '',
    descripcion: '',
    duracion_minutos: '',
    precio: '',
    activo: true,
  },
})

const [nombre, nombreAttrs] = defineField('nombre')
const [descripcion, descripcionAttrs] = defineField('descripcion')
const [duracionMinutos, duracionMinutosAttrs] = defineField('duracion_minutos')
const [precio, precioAttrs] = defineField('precio')
const [activo, activoAttrs] = defineField('activo')

const cargarServicios = async () => {
  loading.value = true
  error.value = null

  try {
    servicios.value = await getServicios()
  } catch (err) {
    console.error(err)
    error.value = 'No se pudieron cargar los servicios.'
  } finally {
    loading.value = false
  }
}

const abrirModal = async (servicio = null, event = null) => {
  lastTriggerRef.value = event?.currentTarget || document.activeElement
  error.value = null
  success.value = null

  if (servicio) {
    servicioEditando.value = servicio
    resetForm({
      values: {
        nombre: servicio.nombre || '',
        descripcion: servicio.descripcion || '',
        duracion_minutos: servicio.duracion_minutos ?? '',
        precio: servicio.precio ?? '',
        activo: Boolean(servicio.activo),
      },
    })
  } else {
    servicioEditando.value = null
    resetForm({
      values: {
        nombre: '',
        descripcion: '',
        duracion_minutos: '',
        precio: '',
        activo: true,
      },
    })
  }

  modalVisible.value = true

  await nextTick()
  modalTitleRef.value?.focus()
}

const cerrarModal = () => {
  modalVisible.value = false
  servicioEditando.value = null

  resetForm({
    values: {
      nombre: '',
      descripcion: '',
      duracion_minutos: '',
      precio: '',
      activo: true,
    },
  })

  lastTriggerRef.value?.focus?.()
}

const guardarServicio = handleSubmit(async (values) => {
  error.value = null
  success.value = null

  try {
    if (servicioEditando.value) {
      await actualizarServicio(servicioEditando.value.id, values)
      success.value = 'Servicio actualizado correctamente.'
    } else {
      await crearServicio(values)
      success.value = 'Servicio creado correctamente.'
    }

    cerrarModal()
    await cargarServicios()
  } catch (err) {
    console.error(err)
    error.value = 'No se pudo guardar el servicio.'
  }
})

const eliminarServicioLocal = async (id, nombre) => {
  const confirmacion = window.confirm(`¿Eliminar el servicio ${nombre}?`)
  if (!confirmacion) return

  error.value = null
  success.value = null

  try {
    await eliminarServicio(id)
    success.value = 'Servicio eliminado correctamente.'
    await cargarServicios()
  } catch (err) {
    console.error(err)
    error.value = 'No se pudo eliminar el servicio.'
  }
}

const getFocusableElements = () => {
  if (!modalRef.value) return []

  return modalRef.value.querySelectorAll(
    'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
  )
}

const manejarTabModal = (event) => {
  if (!modalVisible.value || event.key !== 'Tab') return

  const focusableElements = [...getFocusableElements()]
  if (!focusableElements.length) return

  const firstElement = focusableElements[0]
  const lastElement = focusableElements[focusableElements.length - 1]

  if (event.shiftKey && document.activeElement === firstElement) {
    event.preventDefault()
    lastElement.focus()
  } else if (!event.shiftKey && document.activeElement === lastElement) {
    event.preventDefault()
    firstElement.focus()
  }
}

const manejarTecladoModal = (event) => {
  if (!modalVisible.value) return

  if (event.key === 'Escape') {
    cerrarModal()
    return
  }

  manejarTabModal(event)
}

onMounted(cargarServicios)
onMounted(() => document.addEventListener('keydown', manejarTecladoModal))
onBeforeUnmount(() => document.removeEventListener('keydown', manejarTecladoModal))
</script>

<template>
  <main class="admin-services">
    <section class="admin-services__container" aria-labelledby="admin-services-title">
      <header class="admin-services__header">
        <div class="admin-services__heading">
          <h1 id="admin-services-title" class="admin-services__title">
            Gestión de servicios
          </h1>
          <p class="admin-services__intro">
            Crea, edita y organiza los servicios disponibles para reserva.
          </p>
        </div>

        <button
          type="button"
          class="admin-services__button admin-services__button--primary"
          @click="abrirModal(null, $event)"
          aria-label="Crear nuevo servicio"
        >
          Nuevo servicio
        </button>
      </header>

      <p
        v-if="loading"
        class="admin-services__status"
        role="status"
        aria-live="polite"
      >
        Cargando servicios...
      </p>

      <p
        v-if="error"
        class="admin-services__message admin-services__message--error"
        role="alert"
        aria-live="assertive"
      >
        {{ error }}
      </p>

      <p
        v-if="success"
        class="admin-services__message admin-services__message--success"
        role="status"
        aria-live="polite"
      >
        {{ success }}
      </p>

      <div
        v-if="!loading && !servicios.length"
        class="admin-services__empty"
        role="status"
        aria-live="polite"
      >
        No hay servicios configurados.
      </div>

      <div v-if="!loading && servicios.length" class="admin-services__grid">
        <article
          v-for="servicio in servicios"
          :key="servicio.id"
          class="service-card"
          :aria-labelledby="`service-card-title-${servicio.id}`"
        >
          <header class="service-card__header">
            <h2
              :id="`service-card-title-${servicio.id}`"
              class="service-card__title"
            >
              {{ servicio.nombre }}
            </h2>
          </header>

          <dl class="service-card__meta">
            <div class="service-card__meta-item">
              <dt>Duración</dt>
              <dd>{{ servicio.duracion_minutos }} min</dd>
            </div>

            <div class="service-card__meta-item">
              <dt>Precio</dt>
              <dd>{{ servicio.precio }} €</dd>
            </div>

            <div class="service-card__meta-item">
              <dt>Estado</dt>
              <dd>
                <span
                  class="service-card__status"
                  :class="{
                    'service-card__status--active': servicio.activo,
                    'service-card__status--inactive': !servicio.activo,
                  }"
                >
                  {{ servicio.activo ? 'Activo' : 'Inactivo' }}
                </span>
              </dd>
            </div>
          </dl>

          <p v-if="servicio.descripcion" class="service-card__description">
            {{ servicio.descripcion }}
          </p>

          <div class="service-card__actions">
              <button
                type="button"
                class="service-card__button service-card__button--secondary"
                @click="abrirModal(servicio, $event)"
                :aria-label="`Editar servicio ${servicio.nombre}`"
              >
                Editar
              </button>

              <button
                type="button"
                class="service-card__button service-card__button--danger"
                @click="eliminarServicioLocal(servicio.id, servicio.nombre)"
                :aria-label="`Eliminar servicio ${servicio.nombre}`"
              >
                Eliminar
              </button>
            </div>
        </article>
      </div>
    </section>

    <div
      v-if="modalVisible"
      class="admin-services-modal"
      @click.self="cerrarModal"
    >
      <div
        ref="modalRef"
        class="admin-services-modal__dialog"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="servicioEditando ? 'service-edit-title' : 'service-create-title'"
        aria-describedby="service-modal-description"
      >
        <header class="admin-services-modal__header">
          <h2
            :id="servicioEditando ? 'service-edit-title' : 'service-create-title'"
            ref="modalTitleRef"
            class="admin-services-modal__title"
            tabindex="-1"
          >
            {{ servicioEditando ? 'Editar servicio' : 'Nuevo servicio' }}
          </h2>

          <button
            type="button"
            class="admin-services-modal__close"
            @click="cerrarModal"
            aria-label="Cerrar formulario de servicio"
          >
            ×
          </button>
        </header>

        <p id="service-modal-description" class="admin-services-modal__description">
          Completa los campos obligatorios para guardar el servicio.
        </p>

        <form
          class="admin-services-form"
          @submit="guardarServicio"
          :aria-busy="isSubmitting ? 'true' : 'false'"
          novalidate
        >
          <div class="admin-services-form__field admin-services-form__field--full">
            <label class="admin-services-form__label" for="nombre">Nombre *</label>
            <input
              id="nombre"
              v-model="nombre"
              v-bind="nombreAttrs"
              class="admin-services-form__input"
              :class="{ 'admin-services-form__input--error': errors.nombre }"
              type="text"
              autocomplete="off"
              :aria-invalid="errors.nombre ? 'true' : 'false'"
              :aria-describedby="errors.nombre ? 'nombre-error' : undefined"
            />
            <p
              v-if="errors.nombre"
              id="nombre-error"
              class="admin-services-form__error"
              aria-live="polite"
            >
              {{ errors.nombre }}
            </p>
          </div>

          <div class="admin-services-form__field admin-services-form__field--full">
            <label class="admin-services-form__label" for="descripcion">Descripción</label>
            <textarea
              id="descripcion"
              v-model="descripcion"
              v-bind="descripcionAttrs"
              class="admin-services-form__textarea"
              rows="4"
            ></textarea>
          </div>

          <div class="admin-services-form__row">
            <div class="admin-services-form__field">
              <label class="admin-services-form__label" for="duracion">
                Duración (min) *
              </label>
              <input
                id="duracion"
                v-model="duracionMinutos"
                v-bind="duracionMinutosAttrs"
                class="admin-services-form__input"
                :class="{ 'admin-services-form__input--error': errors.duracion_minutos }"
                type="number"
                min="1"
                inputmode="numeric"
                :aria-invalid="errors.duracion_minutos ? 'true' : 'false'"
                :aria-describedby="errors.duracion_minutos ? 'duracion-error' : undefined"
              />
              <p
                v-if="errors.duracion_minutos"
                id="duracion-error"
                class="admin-services-form__error"
                aria-live="polite"
              >
                {{ errors.duracion_minutos }}
              </p>
            </div>

            <div class="admin-services-form__field">
              <label class="admin-services-form__label" for="precio">Precio (€) *</label>
              <input
                id="precio"
                v-model="precio"
                v-bind="precioAttrs"
                class="admin-services-form__input"
                :class="{ 'admin-services-form__input--error': errors.precio }"
                type="number"
                step="0.01"
                min="0"
                inputmode="decimal"
                :aria-invalid="errors.precio ? 'true' : 'false'"
                :aria-describedby="errors.precio ? 'precio-error' : undefined"
              />
              <p
                v-if="errors.precio"
                id="precio-error"
                class="admin-services-form__error"
                aria-live="polite"
              >
                {{ errors.precio }}
              </p>
            </div>
          </div>

          <div class="admin-services-form__field admin-services-form__field--full">
            <label class="admin-services-form__checkbox">
              <input
                v-model="activo"
                v-bind="activoAttrs"
                class="admin-services-form__checkbox-input"
                type="checkbox"
              />
              <span class="admin-services-form__checkbox-label">Activo</span>
            </label>
          </div>

          <div class="admin-services-form__actions">
            <button
              type="button"
              class="admin-services-form__button admin-services-form__button--secondary"
              @click="cerrarModal"
            >
              Cancelar
            </button>

            <button
              type="submit"
              class="admin-services-form__button admin-services-form__button--primary"
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? 'Guardando...' : servicioEditando ? 'Actualizar' : 'Crear' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>
</template>