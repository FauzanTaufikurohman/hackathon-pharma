import { createRouter, createWebHistory } from "vue-router";
import type { RouteRecordRaw } from "vue-router";
import VendorDashboardView from "../views/vendor/VendorDashboardView.vue";
import VendorPackagesView from "../views/vendor/VendorPackagesView.vue";
import VendorMonitorView from "../views/vendor/VendorMonitorView.vue";
import CourierHomeView from "../views/courier/CourierHomeView.vue";
import CourierTasksView from "../views/courier/CourierTasksView.vue";
import CourierTaskDetailView from "../views/courier/CourierTaskDetailView.vue";
import CheckerQueueView from "../views/checker/CheckerQueueView.vue";
import CheckerHistoryView from "../views/checker/CheckerHistoryView.vue";
import CheckerExamView from "../views/checker/CheckerExamView.vue";
import AdminShipmentsView from "../views/admin/AdminShipmentsView.vue";
import AdminApprovalsView from "../views/admin/AdminApprovalsView.vue";
import AdminLiveMonitorView from "../views/admin/AdminLiveMonitorView.vue";
import AdminThermalSimulationView from "../views/admin/AdminThermalSimulationView.vue";
import AdminReportsView from "../views/admin/AdminReportsView.vue";
import AdminAccessManagementView from "../views/admin/AdminAccessManagementView.vue";
import LoginView from "../views/auth/LoginView.vue";
import NotFoundView from "../views/NotFoundView.vue";
import { currentUser, restoreSession } from "../services/auth";
import type { AppRole } from "../services/auth";

declare module "vue-router" {
  interface RouteMeta {
    roles?: AppRole[];
  }
}

const roleHome: Record<AppRole, string> = {
  admin: "/admin/manajemen-pengiriman",
  checker: "/checker",
  courier: "/courier",
  buyer: "/",
  vendor: "/",
};

const routes: RouteRecordRaw[] = [
  {
    path: "/login",
    name: "login",
    component: LoginView,
  },
  {
    path: "/",
    name: "vendor-home",
    component: VendorDashboardView,
    meta: { roles: ["buyer", "vendor"] },
  },
  {
    path: "/vendor",
    redirect: "/",
  },
  {
    path: "/paket-saya",
    name: "vendor-packages",
    component: VendorPackagesView,
    meta: { roles: ["buyer", "vendor"] },
  },
  {
    path: "/vendor/monitor/:awb",
    name: "vendor-monitor",
    component: VendorMonitorView,
    meta: { roles: ["buyer", "vendor"] },
  },
  {
    path: "/courier",
    name: "courier-home",
    component: CourierHomeView,
    alias: [
      "/kurir",
      "/courier/beranda",
      "/courier/tugas-masuk",
      "/kurir/tugas-masuk",
    ],
    meta: { roles: ["courier"] },
  },
  {
    path: "/courier/tugas-saya",
    name: "courier-tasks",
    component: CourierTasksView,
    alias: ["/courier/tasks", "/kurir/tugas-saya"],
    meta: { roles: ["courier"] },
  },
  {
    path: "/courier/tugas/:awb?",
    name: "courier-task-detail",
    component: CourierTaskDetailView,
    alias: ["/courier/task/:awb?", "/kurir/tugas/:awb?"],
    meta: { roles: ["courier"] },
  },
  {
    path: "/checker",
    name: "checker-queue",
    component: CheckerQueueView,
    alias: ["/checker/antrean", "/checker/queue", "/pemeriksa"],
    meta: { roles: ["checker"] },
  },
  {
    path: "/checker/form-initial-exam",
    name: "checker-exam",
    component: CheckerExamView,
    alias: ["/checker/exam", "/checker/initial-exam"],
    meta: { roles: ["checker"] },
  },
  {
    path: "/checker/riwayat",
    name: "checker-history",
    component: CheckerHistoryView,
    alias: [
      "/checker/history",
      "/checker/status-approval",
      "/checker/approvals",
    ],
    meta: { roles: ["checker"] },
  },
  {
    path: "/admin",
    redirect: "/admin/manajemen-pengiriman",
  },
  {
    path: "/admin/manajemen-pengiriman",
    name: "admin-shipments",
    component: AdminShipmentsView,
    alias: ["/admin/shipments", "/admin/pengiriman"],
    meta: { roles: ["admin"] },
  },
  {
    path: "/admin/approval-qc-kurir",
    name: "admin-approvals",
    component: AdminApprovalsView,
    alias: ["/admin/approval", "/admin/approvals", "/admin/qc-kurir"],
    meta: { roles: ["admin"] },
  },
  {
    path: "/admin/live-monitor-kendala",
    name: "admin-monitor",
    component: AdminLiveMonitorView,
    alias: ["/admin/monitor", "/admin/live-monitor", "/admin/kendala"],
    meta: { roles: ["admin"] },
  },
  {
    path: "/admin/simulasi-termal",
    name: "admin-thermal-simulation",
    component: AdminThermalSimulationView,
    meta: { roles: ["admin"] },
  },
  {
    path: "/admin/laporan-audit-log",
    name: "admin-reports",
    component: AdminReportsView,
    alias: [
      "/admin/laporan",
      "/admin/audit",
      "/admin/reports",
      "/admin/audit-log",
    ],
    meta: { roles: ["admin"] },
  },
  {
    path: "/admin/pengguna",
    name: "admin-users",
    component: AdminAccessManagementView,
    meta: { roles: ["admin"] },
  },
  {
    path: "/:pathMatch(.*)*",
    name: "not-found",
    component: NotFoundView,
  },
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});

router.beforeEach(async (to) => {
  await restoreSession();
  const user = currentUser.value;
  if (to.name === "login") {
    return user ? roleHome[user.role] : true;
  }
  if (!user) {
    return { name: "login", query: { redirect: to.fullPath } };
  }

  const allowedRoles = to.meta.roles;
  if (allowedRoles && !allowedRoles.includes(user.role)) {
    return roleHome[user.role];
  }

  return true;
});

const handleUnauthorized = () => {
  if (router.currentRoute.value.name !== 'login') {
    void router.replace({ name: 'login', query: { redirect: router.currentRoute.value.fullPath } });
  }
};
window.addEventListener('pharma:unauthorized', handleUnauthorized);
import.meta.hot?.dispose(() => window.removeEventListener('pharma:unauthorized', handleUnauthorized));

export default router;
