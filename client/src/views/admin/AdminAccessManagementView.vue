<script setup lang="ts">
import { computed, ref } from 'vue'
import AdminNavbar from '../../components/admin/AdminNavbar.vue'

type AccessRole = 'admin' | 'checker' | 'courier' | 'vendor'
type AccountStatus = 'Aktif' | 'Nonaktif'

interface ManagedUser {
  id: number
  name: string
  email: string
  role: AccessRole
  status: AccountStatus
}

const roles: { value: AccessRole; label: string; access: string }[] = [
  { value: 'admin', label: 'Admin', access: 'Pengiriman, persetujuan, pemantauan, laporan, pengguna' },
  { value: 'checker', label: 'Checker', access: 'Pemeriksaan awal dan bukti barang' },
  { value: 'courier', label: 'Kurir', access: 'Tugas, status perjalanan, dan kendala' },
  { value: 'vendor', label: 'Vendor / Customer', access: 'Pantau pengiriman dan lihat laporan' },
]

const users = ref<ManagedUser[]>([
  { id: 1, name: 'Ayu Pratama', email: 'ayu.admin@pharma.test', role: 'admin', status: 'Aktif' },
  { id: 2, name: 'Rian Hendra', email: 'rian.checker@pharma.test', role: 'checker', status: 'Aktif' },
  { id: 3, name: 'Bambang Suryono', email: 'bambang.kurir@pharma.test', role: 'courier', status: 'Aktif' },
  { id: 4, name: 'RS Medika Sehat', email: 'farmasi@medika.test', role: 'vendor', status: 'Aktif' },
])

const isAddingUser = ref(false)
const newName = ref('')
const newEmail = ref('')
const newRole = ref<AccessRole>('checker')
const notice = ref('')

const activeUserCount = computed(() => users.value.filter((user) => user.status === 'Aktif').length)

function roleLabel(role: AccessRole): string {
  return roles.find((item) => item.value === role)?.label ?? role
}

function toggleStatus(user: ManagedUser) {
  user.status = user.status === 'Aktif' ? 'Nonaktif' : 'Aktif'
  notice.value = `Status ${user.name} diubah menjadi ${user.status.toLowerCase()}.`
}

function addUser() {
  const name = newName.value.trim()
  const email = newEmail.value.trim().toLowerCase()
  if (!name || !email) return
  if (users.value.some((user) => user.email === email)) {
    notice.value = 'Email tersebut sudah terdaftar pada daftar prototipe.'
    return
  }

  users.value.push({
    id: Date.now(),
    name,
    email,
    role: newRole.value,
    status: 'Aktif',
  })
  notice.value = `${name} ditambahkan sebagai ${roleLabel(newRole.value)}. Perubahan ini hanya berlaku di prototipe.`
  newName.value = ''
  newEmail.value = ''
  newRole.value = 'checker'
  isAddingUser.value = false
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-900">
    <AdminNavbar active-tab="users" />

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
      <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Pengguna &amp; hak akses</h1>
          <p class="mt-1 text-sm text-slate-600">Atur peran untuk membatasi fitur yang dapat digunakan setiap akun.</p>
        </div>
        <button
          type="button"
          @click="isAddingUser = !isAddingUser"
          class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:opacity-90"
        >
          {{ isAddingUser ? 'Tutup' : 'Tambah pengguna' }}
        </button>
      </div>

      <div class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <section
          v-for="role in roles"
          :key="role.value"
          class="rounded-xl border border-slate-200 bg-white p-4"
        >
          <h2 class="text-sm font-semibold">{{ role.label }}</h2>
          <p class="mt-1 text-sm text-slate-600">{{ role.access }}</p>
        </section>
      </div>

      <form
        v-if="isAddingUser"
        class="mb-6 grid gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-4"
        @submit.prevent="addUser"
      >
        <label class="text-sm font-medium text-slate-700">
          Nama
          <input v-model="newName" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 font-normal" />
        </label>
        <label class="text-sm font-medium text-slate-700">
          Email
          <input v-model="newEmail" required type="email" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 font-normal" />
        </label>
        <label class="text-sm font-medium text-slate-700">
          Peran
          <select v-model="newRole" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 font-normal">
            <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
          </select>
        </label>
        <div class="flex items-end">
          <button type="submit" class="w-full rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
            Simpan pengguna
          </button>
        </div>
      </form>

      <p
        v-if="notice"
        role="status"
        class="mb-4 rounded-lg bg-blue-50 px-4 py-3 text-sm text-blue-900"
      >
        {{ notice }}
      </p>

      <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
          <h2 class="font-semibold">Daftar pengguna</h2>
          <span class="text-sm text-slate-500">{{ activeUserCount }} aktif</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full min-w-[640px] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
              <tr>
                <th class="px-4 py-3 font-medium">Pengguna</th>
                <th class="px-4 py-3 font-medium">Peran</th>
                <th class="px-4 py-3 font-medium">Status</th>
                <th class="px-4 py-3 font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="user in users" :key="user.id">
                <td class="px-4 py-3">
                  <p class="font-medium text-slate-900">{{ user.name }}</p>
                  <p class="mt-0.5 text-slate-500">{{ user.email }}</p>
                </td>
                <td class="px-4 py-3">
                  <select
                    v-model="user.role"
                    :aria-label="`Peran ${user.name}`"
                    class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5"
                    @change="notice = `Peran ${user.name} diubah menjadi ${roleLabel(user.role)}. Perubahan ini hanya berlaku di prototipe.`"
                  >
                    <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
                  </select>
                </td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                    :class="user.status === 'Aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'"
                  >
                    {{ user.status }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <button
                    type="button"
                    class="font-medium text-primary hover:underline"
                    @click="toggleStatus(user)"
                  >
                    {{ user.status === 'Aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <p class="mt-4 text-xs text-slate-500">
        Prototipe: perubahan pengguna dan peran hanya tersimpan sementara di halaman ini. Penerapan hak akses sebenarnya memerlukan API backend.
      </p>
    </main>
  </div>
</template>
