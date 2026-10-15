<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import CourierNavbar from '../../components/courier/CourierNavbar.vue'
import CourierFooter from '../../components/courier/CourierFooter.vue'
import CourierLiveTask from '../../components/courier/CourierLiveTask.vue'
const route = useRoute(),
  awb = computed(() => String(route.params.awb || ''))
</script>
<template>
  <div>
    <CourierNavbar />
    <main class="max-w-6xl mx-auto px-4 sm:px-6 pt-28 pb-10">
      <CourierLiveTask :key="awb" :awb="awb" />
    </main>
    <CourierFooter />
  </div>
</template>
