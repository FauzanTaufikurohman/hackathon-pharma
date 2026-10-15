<script setup lang="ts">
import { ref } from 'vue'
import AdminNavbar from '../../components/admin/AdminNavbar.vue'
import ApiState from '../../components/layout/ApiState.vue'
import { useApi, useMutation } from '../../services/useApi'
import { query } from '../../services/api'
import { currentUser } from '../../services/auth'
interface ManagedUser {
  id: number
  name: string
  email: string
  role: string
  is_active: boolean
  courier_profile: { capacity_kg?: number } | null
}
interface Directory {
  data: ManagedUser[]
  total: number
  last_page: number
}
const page = ref(1),
  search = ref(''),
  filter = ref(''),
  adding = ref(false),
  name = ref(''),
  email = ref(''),
  role = ref('checker'),
  password = ref(''),
  capacity = ref<number | string>(''),
  chatIds = ref<Record<number, string>>({})
const users = useApi<Directory>(
  () => '/admin/users' + query({ page: page.value, search: filter.value }),
)
const mutation = useMutation()
const roles = [
  { value: 'admin', label: 'Admin' },
  { value: 'checker', label: 'Checker' },
  { value: 'courier', label: 'Kurir' },
  { value: 'buyer', label: 'Vendor / penerima' },
]
async function create() {
  const payload = {
    name: name.value,
    email: email.value,
    password: password.value,
    role: role.value,
    ...(role.value === 'courier'
      ? { capacity_kg: Number(capacity.value) }
      : {}),
  }
  if (await mutation.run('/admin/users', payload)) {
    name.value = ''
    email.value = ''
    password.value = ''
    capacity.value = ''
    adding.value = false
    await users.reload()
  }
}
async function update(user: ManagedUser, newRole: string, active: boolean) {
  const values = {
    role: newRole,
    is_active: active,
    expected_role: user.role,
    expected_active: user.is_active,
  }
  const roleCapacity =
    newRole === 'courier' && !user.courier_profile?.capacity_kg
      ? prompt('Kapasitas kurir (kg) yang benar-benar tersedia:')
      : null
  if (
    newRole === 'courier' &&
    !user.courier_profile?.capacity_kg &&
    !roleCapacity
  )
    return
  if (
    !confirm(
      'Simpan perubahan akses ' +
        user.name +
        '? Sesi aktif akun ini akan dicabut jika aksesnya berubah.',
    )
  )
    return
  if (
    await mutation.run(
      '/admin/users/' + user.id,
      {
        ...values,
        ...(roleCapacity ? { capacity_kg: Number(roleCapacity) } : {}),
      },
      'PUT',
    )
  )
    await users.reload()
}
async function telegram(user: ManagedUser) {
  const chat = chatIds.value[user.id]?.trim()
  if (!chat) return
  if (
    await mutation.run(
      '/admin/users/' + user.id + '/telegram',
      { chat_id: chat },
      'PUT',
    )
  )
    chatIds.value[user.id] = ''
}
function chooseRole(event: Event, user: ManagedUser) {
  const select = event.target as HTMLSelectElement
  const selected = select.value
  select.value = user.role
  void update(user, selected, user.is_active)
}
function submitSearch() {
  filter.value = search.value.trim()
  page.value = 1
}
</script>
<template>
  <div>
    <AdminNavbar active-tab="users" />
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 flex flex-col gap-6">
      <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-semibold tracking-tight">
            Pengguna dan hak akses
          </h1>
          <p class="mt-3 text-on-surface-variant">
            Role ditentukan oleh akun, bukan dipilih saat login.
          </p>
        </div>
        <button type="button" class="live-button" @click="adding = !adding">
          {{ adding ? 'Tutup formulir' : 'Tambah pengguna' }}
        </button>
      </header>
      <ApiState
        :loading="users.loading.value"
        :error="users.error.value || mutation.error.value"
        :notice="mutation.notice.value"
        @retry="users.reload"
      />
      <form
        v-if="adding"
        class="live-panel grid sm:grid-cols-2 gap-4"
        @submit.prevent="create"
      >
        <label class="live-field"
          ><span>Nama</span
          ><input
            v-model="name"
            name="name"
            minlength="2"
            maxlength="255"
            required
            :disabled="mutation.busy.value"
        /></label>
        <label class="live-field"
          ><span>Email</span
          ><input
            v-model="email"
            name="email"
            type="email"
            maxlength="255"
            required
            :disabled="mutation.busy.value"
        /></label>
        <label class="live-field"
          ><span>Peran</span
          ><select v-model="role" name="role" :disabled="mutation.busy.value">
            <option
              v-for="option in roles"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select></label
        >
        <label class="live-field"
          ><span>Password awal (minimal 12 karakter)</span
          ><input
            v-model="password"
            name="password"
            type="password"
            autocomplete="new-password"
            minlength="12"
            maxlength="255"
            required
            :disabled="mutation.busy.value"
        /></label>
        <label v-if="role === 'courier'" class="live-field"
          ><span>Kapasitas pengantaran (kg)</span
          ><input
            v-model.number="capacity"
            name="capacity_kg"
            type="number"
            step="0.01"
            min="0.01"
            max="100000"
            required
        /></label>
        <p class="sm:col-span-2">
          Sampaikan password awal langsung kepada pemilik akun. Password tidak
          ditampilkan dalam respons atau disimpan sebagai teks biasa.
        </p>
        <button
          type="submit"
          class="live-button live-primary justify-self-start"
          :disabled="mutation.busy.value"
        >
          Simpan pengguna
        </button>
      </form>
      <form class="flex gap-3 items-end" @submit.prevent="submitSearch">
        <label class="live-field flex-1"
          ><span>Cari nama atau email</span
          ><input
            v-model="search"
            name="search"
            type="search"
            maxlength="120" /></label
        ><button type="submit" class="live-button">Cari</button>
      </form>
      <p
        v-if="users.data.value && !users.data.value.data.length"
        class="live-panel"
      >
        Tidak ada akun yang sesuai.
      </p>
      <article
        v-for="user in users.data.value?.data"
        :key="user.id"
        class="live-panel"
      >
        <div class="flex flex-wrap justify-between gap-4">
          <div>
            <h2 class="text-lg font-semibold">{{ user.name }}</h2>
            <p class="mt-2 break-all">{{ user.email }}</p>
            <p class="mt-2">
              {{ user.role }} · {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
            </p>
          </div>
          <div class="flex flex-wrap gap-3 items-start">
            <label class="live-field"
              ><span>Ubah peran</span
              ><select
                :value="user.role"
                :name="'role_' + user.id"
                :disabled="mutation.busy.value || user.id === currentUser?.id"
                @change="(event) => chooseRole(event, user)"
              >
                <option
                  v-for="option in [
                    ...roles,
                    { value: 'vendor', label: 'Vendor' },
                  ]"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </option>
              </select></label
            ><button
              type="button"
              class="live-button"
              :disabled="mutation.busy.value || user.id === currentUser?.id"
              @click="update(user, user.role, !user.is_active)"
            >
              {{ user.is_active ? 'Nonaktifkan akun' : 'Aktifkan akun' }}
            </button>
          </div>
        </div>
        <details class="mt-5">
          <summary class="cursor-pointer font-semibold py-2">
            Pasang Chat ID Telegram
          </summary>
          <form
            class="flex flex-wrap gap-3 items-end mt-3"
            @submit.prevent="telegram(user)"
          >
            <label class="live-field flex-1"
              ><span>Chat ID pemilik akun yang sudah mengirim /start</span
              ><input
                v-model="chatIds[user.id]"
                :name="'telegram_' + user.id"
                pattern="-?[0-9]{1,20}"
                required /></label
            ><button
              type="submit"
              class="live-button"
              :disabled="mutation.busy.value"
            >
              Simpan Chat ID
            </button>
          </form>
          <p class="mt-3 text-on-surface-variant">
            Menyimpan Chat ID belum berarti pesan terkirim. Bot token tetap
            disimpan privat di server.
          </p>
        </details>
      </article>
      <nav
        v-if="users.data.value"
        class="flex gap-3 items-center"
        aria-label="Halaman pengguna"
      >
        <button
          type="button"
          class="live-button"
          :disabled="page <= 1 || users.loading.value"
          @click="page--"
        >
          Sebelumnya
        </button>
        <p>
          {{ page }} / {{ users.data.value.last_page }} ·
          {{ users.data.value.total }} akun
        </p>
        <button
          type="button"
          class="live-button"
          :disabled="page >= users.data.value.last_page || users.loading.value"
          @click="page++"
        >
          Berikutnya
        </button>
      </nav>
    </main>
  </div>
</template>
