<script setup lang="ts">
import { ref } from 'vue'

const isVisible = ref(false)
const title = ref('')
const subtitle = ref('')
let timer: ReturnType<typeof setTimeout> | null = null

function show(newTitle: string, newSubtitle: string, durationMs = 3500) {
  if (timer) clearTimeout(timer)
  title.value = newTitle
  subtitle.value = newSubtitle
  isVisible.value = true

  timer = setTimeout(() => {
    isVisible.value = false
  }, durationMs)
}

defineExpose({
  show,
})
</script>

<template>
  <div
    class="fixed bottom-6 right-6 z-50 transform transition-all duration-300 pointer-events-none"
    :class="isVisible ? 'translate-y-0 opacity-100 scale-100' : 'translate-y-8 opacity-0 scale-95'"
  >
    <div class="bg-inverse-surface text-inverse-on-surface px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3.5 border border-surface-variant/40 max-w-md pointer-events-auto">
      <div class="w-8 h-8 rounded-xl bg-secondary-container flex items-center justify-center text-on-secondary-container shrink-0">
        <span class="material-symbols-outlined text-[20px]">check_circle</span>
      </div>
      <div class="flex flex-col min-w-0">
        <span class="font-label-md text-label-md font-bold text-inverse-on-surface truncate">{{ title }}</span>
        <span class="font-body-sm text-body-sm text-surface-variant truncate">{{ subtitle }}</span>
      </div>
      <button
        type="button"
        @click="isVisible = false"
        class="ml-auto text-outline hover:text-inverse-on-surface transition-colors cursor-pointer"
      >
        <span class="material-symbols-outlined text-[18px]">close</span>
      </button>
    </div>
  </div>
</template>
