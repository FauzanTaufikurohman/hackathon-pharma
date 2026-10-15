<script setup lang="ts">
import { onScopeDispose, ref, watch } from 'vue'
import { privateImage, errorText } from '../../services/api'
const props = defineProps<{ path: string; alt: string }>()
const source = ref(''),
  error = ref('')
let controller: AbortController | undefined
let generation = 0
watch(
  () => props.path,
  async (path) => {
    const mine = ++generation
    controller?.abort()
    if (source.value) URL.revokeObjectURL(source.value)
    source.value = ''
    error.value = ''
    if (!path) return
    controller = new AbortController()
    try {
      const url = await privateImage(path, controller.signal)
      if (mine === generation) source.value = url
      else URL.revokeObjectURL(url)
    } catch (cause) {
      if (mine === generation) error.value = errorText(cause)
    }
  },
  { immediate: true },
)
onScopeDispose(() => {
  generation++
  controller?.abort()
  if (source.value) URL.revokeObjectURL(source.value)
})
</script>
<template>
  <figure>
    <img
      v-if="source"
      :src="source"
      :alt="alt"
      class="w-full max-h-80 object-contain rounded-xl"
    />
    <p v-else role="status">{{ error || 'Memuat bukti privat…' }}</p>
  </figure>
</template>
