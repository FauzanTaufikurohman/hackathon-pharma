import { createRouter, createWebHistory } from 'vue-router'
import VendorDashboardView from '../views/vendor/VendorDashboardView.vue'
import VendorPackagesView from '../views/vendor/VendorPackagesView.vue'
import VendorMonitorView from '../views/vendor/VendorMonitorView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'vendor-home',
      component: VendorDashboardView,
    },
    {
      path: '/vendor',
      redirect: '/',
    },
    {
      path: '/paket-saya',
      name: 'vendor-packages',
      component: VendorPackagesView,
    },
    {
      path: '/vendor/monitor/:awb',
      name: 'vendor-monitor',
      component: VendorMonitorView,
    },
    // Fallback redirect
    {
      path: '/:pathMatch(.*)*',
      redirect: '/',
    },
  ],
  scrollBehavior() {
    return { top: 0 }
  },
})

export default router
