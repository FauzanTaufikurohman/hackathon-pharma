import { createRouter, createWebHistory } from 'vue-router'
import VendorDashboardView from '../views/vendor/VendorDashboardView.vue'
import VendorPackagesView from '../views/vendor/VendorPackagesView.vue'
import VendorMonitorView from '../views/vendor/VendorMonitorView.vue'
import CourierHomeView from '../views/courier/CourierHomeView.vue'
import CourierTasksView from '../views/courier/CourierTasksView.vue'
import CourierTaskDetailView from '../views/courier/CourierTaskDetailView.vue'
import CheckerQueueView from '../views/checker/CheckerQueueView.vue'
import CheckerHistoryView from '../views/checker/CheckerHistoryView.vue'
import CheckerExamView from '../views/checker/CheckerExamView.vue'
import AdminShipmentsView from '../views/admin/AdminShipmentsView.vue'

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
      component: CourierTasksView,
      alias: ['/courier/tasks', '/kurir/tugas-saya'],
    },
    {
      path: '/courier/tugas/:awb',
      name: 'courier-task-detail',
      component: CourierTaskDetailView,
      alias: ['/courier/task/:awb', '/kurir/tugas/:awb'],
    },

    // Checker Role Routes
    {
      path: '/checker',
      name: 'checker-queue',
      component: CheckerQueueView,
      alias: ['/checker/antrean', '/checker/queue', '/pemeriksa'],
    },
    {
      path: '/checker/form-initial-exam',
      name: 'checker-exam',
      component: CheckerExamView,
      alias: ['/checker/exam', '/checker/initial-exam'],
    },
    {
      path: '/checker/riwayat',
      name: 'checker-history',
      component: CheckerHistoryView,
      alias: ['/checker/history', '/checker/status-approval', '/checker/approvals'],
    },

    // Admin Role Routes
    {
      path: '/admin',
      redirect: '/admin/manajemen-pengiriman',
    },
    {
      path: '/admin/manajemen-pengiriman',
      name: 'admin-shipments',
      component: AdminShipmentsView,
      alias: ['/admin/shipments', '/admin/pengiriman'],
    },
    {
      path: '/admin/approval-qc-kurir',
      name: 'admin-approvals',
      redirect: '/admin/manajemen-pengiriman',
    },
    {
      path: '/admin/live-monitor-kendala',
      name: 'admin-monitor',
      redirect: '/admin/manajemen-pengiriman',
    },
    {
      path: '/admin/laporan-audit-log',
      name: 'admin-reports',
      redirect: '/admin/manajemen-pengiriman',
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
