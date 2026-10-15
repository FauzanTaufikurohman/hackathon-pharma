<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import CheckerNavbar from '../../components/checker/CheckerNavbar.vue'
import CheckerFooter from '../../components/checker/CheckerFooter.vue'
import CheckerLiveExam from '../../components/checker/CheckerLiveExam.vue'
const route = useRoute(),
  awb = computed(() =>
    typeof route.query.awb === 'string' ? route.query.awb : '',
  )
</script>
<template>
  <div>
    <CheckerNavbar :active-tab="2" />
    <main class="max-w-6xl mx-auto px-4 sm:px-6 pt-28 pb-10">
      <CheckerLiveExam :key="awb" :awb="awb" />
    </main>
    <CheckerFooter />
  </div>
</template>
