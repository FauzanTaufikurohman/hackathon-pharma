import { createRouter, createWebHistory } from 'vue-router'
import VendorDashboardView from '../views/vendor/VendorDashboardView.vue'
import VendorPackagesView from '../views/vendor/VendorPackagesView.vue'
import VendorMonitorView from '../views/vendor/VendorMonitorView.vue'
import CourierHomeView from '../views/courier/CourierHomeView.vue'
import CourierTasksPlaceholderView from '../views/courier/CourierTasksPlaceholderView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // Vendor Role Routes
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

    // Courier Role Routes
    {
      path: '/courier',
      name: 'courier-home',
      component: CourierHomeView,
      alias: ['/kurir', '/courier/beranda'],
    },
    {
      path: '/courier/tugas-saya',
      name: 'courier-tasks',
      component: CourierTasksPlaceholderView,
      alias: ['/courier/tasks', '/kurir/tugas-saya'],
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
