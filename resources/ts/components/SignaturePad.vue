<script lang="ts" setup>
import { ref, onMounted, watch, onUnmounted } from 'vue'

interface Props {
  modelValue?: string | File | undefined
  label?: string
  required?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  label: 'Tanda Tangan',
  required: false
})

const emit = defineEmits<{
  'update:modelValue': [value: string | File | undefined]
}>()

const canvasRef = ref<HTMLCanvasElement | null>(null)
const signatureMode = ref<'draw' | 'upload'>('draw')
const uploadedImage = ref<string>('')
const isSigned = ref(false)
const isDrawing = ref(false)

let ctx: CanvasRenderingContext2D | null = null
let lastX = 0
let lastY = 0
const history: ImageData[] = []

const initCanvas = () => {
  if (!canvasRef.value) return
  
  const canvas = canvasRef.value
  ctx = canvas.getContext('2d')
  
  if (ctx) {
    ctx.strokeStyle = '#000000'
    ctx.lineWidth = 2
    ctx.lineCap = 'round'
    ctx.lineJoin = 'round'
    ctx.fillStyle = 'rgba(255, 255, 255, 0)'
    ctx.fillRect(0, 0, canvas.width, canvas.height)
  }
}

const saveState = () => {
  if (canvasRef.value && ctx) {
    history.push(ctx.getImageData(0, 0, canvasRef.value.width, canvasRef.value.height))
  }
}

const undo = () => {
  if (history.length > 0 && ctx && canvasRef.value) {
    const previousState = history.pop()
    if (previousState) {
      ctx.putImageData(previousState, 0, 0)
    }
  }
}

const clear = () => {
  if (ctx && canvasRef.value) {
    ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height)
    ctx.fillStyle = 'rgba(255, 255, 255, 0)'
    ctx.fillRect(0, 0, canvasRef.value.width, canvasRef.value.height)
    isSigned.value = false
    emit('update:modelValue', undefined)
    history.length = 0
  }
}

const save = () => {
  if (canvasRef.value && !isEmpty()) {
    const data = canvasRef.value.toDataURL('image/png')
    isSigned.value = true
    emit('update:modelValue', data)
  }
}

const isEmpty = () => {
  if (!ctx || !canvasRef.value) return true
  const pixelBuffer = ctx.getImageData(0, 0, canvasRef.value.width, canvasRef.value.height).data
  return !pixelBuffer.some(channel => channel !== 0)
}

const getCoordinates = (event: MouseEvent | TouchEvent) => {
  if (!canvasRef.value) return { x: 0, y: 0 }
  
  const rect = canvasRef.value.getBoundingClientRect()
  const scaleX = canvasRef.value.width / rect.width
  const scaleY = canvasRef.value.height / rect.height
  
  let clientX: number
  let clientY: number
  
  if (event instanceof MouseEvent) {
    clientX = event.clientX
    clientY = event.clientY
  } else {
    clientX = event.touches[0].clientX
    clientY = event.touches[0].clientY
  }
  
  return {
    x: (clientX - rect.left) * scaleX,
    y: (clientY - rect.top) * scaleY
  }
}

const startDrawing = (event: MouseEvent | TouchEvent) => {
  event.preventDefault()
  isDrawing.value = true
  const coords = getCoordinates(event)
  lastX = coords.x
  lastY = coords.y
  saveState()
}

const draw = (event: MouseEvent | TouchEvent) => {
  event.preventDefault()
  if (!isDrawing.value || !ctx) return
  
  const coords = getCoordinates(event)
  
  ctx.beginPath()
  ctx.moveTo(lastX, lastY)
  ctx.lineTo(coords.x, coords.y)
  ctx.stroke()
  
  lastX = coords.x
  lastY = coords.y
}

const stopDrawing = () => {
  isDrawing.value = false
  if (!isEmpty()) {
    save()
  }
}

const handleImageUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    
    // Check if file is PNG
    if (!file.type.includes('png')) {
      alert('Harap upload file PNG untuk tanda tangan')
      target.value = ''
      return
    }
    
    // Check file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      alert('Ukuran file maksimal 5MB')
      target.value = ''
      return
    }
    
    const reader = new FileReader()
    reader.onload = (e) => {
      uploadedImage.value = e.target?.result as string
      isSigned.value = true
      emit('update:modelValue', file)
    }
    reader.readAsDataURL(file)
  }
}

const switchMode = (mode: 'draw' | 'upload') => {
  signatureMode.value = mode
  clear()
  uploadedImage.value = ''
}

// Watch for external value changes
watch(() => props.modelValue, (newValue) => {
  if (!newValue) {
    clear()
    uploadedImage.value = ''
    isSigned.value = false
  }
})

onMounted(() => {
  initCanvas()
  
  if (canvasRef.value) {
    canvasRef.value.addEventListener('mousedown', startDrawing)
    canvasRef.value.addEventListener('mousemove', draw)
    canvasRef.value.addEventListener('mouseup', stopDrawing)
    canvasRef.value.addEventListener('mouseleave', stopDrawing)
    canvasRef.value.addEventListener('touchstart', startDrawing)
    canvasRef.value.addEventListener('touchmove', draw)
    canvasRef.value.addEventListener('touchend', stopDrawing)
  }
})

onUnmounted(() => {
  if (canvasRef.value) {
    canvasRef.value.removeEventListener('mousedown', startDrawing)
    canvasRef.value.removeEventListener('mousemove', draw)
    canvasRef.value.removeEventListener('mouseup', stopDrawing)
    canvasRef.value.removeEventListener('mouseleave', stopDrawing)
    canvasRef.value.removeEventListener('touchstart', startDrawing)
    canvasRef.value.removeEventListener('touchmove', draw)
    canvasRef.value.removeEventListener('touchend', stopDrawing)
  }
})

defineExpose({
  save,
  clear,
  undo,
  isEmpty
})
</script>

<template>
  <VCard variant="outlined" class="signature-pad-card">
    <VCardTitle class="d-flex align-center pa-3">
      <VIcon icon="ri-edit-line" class="mr-2" />
      <span>{{ label }}</span>
      <VSpacer />
      <VChipGroup v-model="signatureMode" mandatory>
        <VChip
          :value="'draw'"
          size="small"
          :color="signatureMode === 'draw' ? 'primary' : 'default'"
          @click="switchMode('draw')"
        >
          DRAW
        </VChip>
        <VChip
          :value="'upload'"
          size="small"
          :color="signatureMode === 'upload' ? 'primary' : 'default'"
          @click="switchMode('upload')"
        >
          UPLOAD
        </VChip>
      </VChipGroup>
    </VCardTitle>

    <VCardText>
      <!-- Draw Mode -->
      <div v-if="signatureMode === 'draw'" class="signature-container">
        <canvas
          ref="canvasRef"
          width="600"
          height="200"
          class="signature-canvas"
        />
        <div class="signature-actions mt-2">
          <VBtn
            size="small"
            variant="outlined"
            color="error"
            @click="clear"
          >
            <VIcon icon="ri-delete-bin-line" class="mr-1" />
            Clear
          </VBtn>
          <VBtn
            size="small"
            variant="outlined"
            color="warning"
            @click="undo"
          >
            <VIcon icon="ri-arrow-go-back-line" class="mr-1" />
            Undo
          </VBtn>
        </div>
      </div>

      <!-- Upload Mode -->
      <div v-if="signatureMode === 'upload'" class="upload-container">
        <div
          class="upload-area"
          :class="{ 'has-image': uploadedImage }"
        >
          <div v-if="!uploadedImage" class="upload-placeholder">
            <VIcon icon="ri-upload-cloud-line" size="48" color="grey" />
            <p class="mt-2 text-grey">Click to upload signature</p>
          </div>
          <img
            v-if="uploadedImage"
            :src="uploadedImage"
            alt="Uploaded signature"
            class="uploaded-image"
          />
          <input
            type="file"
            accept="image/png"
            @change="handleImageUpload"
            class="upload-input"
          />
        </div>
        <VAlert
          v-if="!uploadedImage"
          type="info"
          variant="tonal"
          density="compact"
          class="mt-3"
        >
          <div class="text-caption">
            <strong>Catatan Upload:</strong>
            <ul class="mt-1 mb-0">
              <li>Format file harus PNG</li>
              <li>Background gambar harus putih atau transparan</li>
              <li>Ukuran file maksimal 5MB</li>
            </ul>
          </div>
        </VAlert>
        <VBtn
          v-if="uploadedImage"
          size="small"
          variant="outlined"
          color="error"
          class="mt-2"
          @click="clear"
        >
          <VIcon icon="ri-delete-bin-line" class="mr-1" />
          Clear
        </VBtn>
      </div>

      <!-- Validation -->
      <div v-if="required && !isSigned" class="text-error text-caption mt-2">
        * Tanda tangan wajib diisi
      </div>
    </VCardText>
  </VCard>
</template>

<style scoped>
.signature-pad-card {
  border-radius: 8px;
}

.signature-container {
  width: 100%;
}

.signature-canvas {
  width: 100%;
  background-color: #fff;
}

.signature-actions {
  display: flex;
  gap: 8px;
}

.upload-container {
  width: 100%;
}

.upload-area {
  position: relative;
  width: 100%;
  height: 200px;
  border: 2px dashed #ccc;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  overflow: hidden;
  background-color: #f9f9f9;
  transition: border-color 0.3s;
}

.upload-area:hover {
  border-color: #6366f1;
}

.upload-area.has-image {
  border-style: solid;
  border-color: #6366f1;
}

.upload-placeholder {
  text-align: center;
}

.upload-input {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.uploaded-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}
</style>
