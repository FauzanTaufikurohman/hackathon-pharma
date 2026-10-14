<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import CheckerNavbar from '../../components/checker/CheckerNavbar.vue'
import CheckerExamContextBanner from '../../components/checker/CheckerExamContextBanner.vue'
import CheckerExamChecklistModule from '../../components/checker/CheckerExamChecklistModule.vue'
import CheckerExamProofAiModule from '../../components/checker/CheckerExamProofAiModule.vue'
import CheckerExamSopFooter from '../../components/checker/CheckerExamSopFooter.vue'
import CheckerExamPhotoModal from '../../components/checker/CheckerExamPhotoModal.vue'
import CheckerExamSubmitModal from '../../components/checker/CheckerExamSubmitModal.vue'
import CheckerFooter from '../../components/checker/CheckerFooter.vue'

const router = useRouter()

// Active Feature Tab: 2 = Form Initial Exam
const activeTab = ref(2)

// Form state
const packagingPassed = ref(true)
const sealPassed = ref(true)
const labelPassed = ref(true)
const temperature = ref<number | string>(4.2)
const inspectionNote = ref(
  'Kemasan boks vacuum panel utuh dan bersih, nomor segel SL-8891 cocok dengan manifest, suhu termometer digital internal terkalibrasi stabil pada 4.2°C. Siap dirilis untuk penugasan kurir.'
)

// Photo & OCR state
const photoUrl = ref(
  'https://lh3.googleusercontent.com/aida-public/AB6AXuB5WXkJBEZndlRE6Vn9T2vwslOCmIzkK2Y3A9RyttgfzuJMPgDS19zmjMUpn1qFNPL7j-kjdzOWvI1AO38PuyXyIypXn1D8GQxuysS2cGqj449Ad5_5-p-ZWXx_WOUtRZq7spSbSj-5iWzNCq3CWE0-JfV40JAI2EiaN03b61M2VTsGU4P4Q6m6rBx4-cn4F4xQtzjX9cAwds6xeLjB1bfnOsWOMeYII_e9IiUKPcyAotY8eGXf6Ttnsg'
)
const ocrTemp = ref('4.2')
const ocrSeal = ref('SL-8891')

// Modals state
const isPhotoModalOpen = ref(false)
const isSubmitModalOpen = ref(false)
const submissionResultPassed = ref(true)

// Toast state
interface ToastState {
  show: boolean
  message: string
  isSuccess: boolean
}

const toast = ref<ToastState>({
  show: false,
  message: '',
  isSuccess: true,
})

let toastTimer: ReturnType<typeof setTimeout> | null = null

const triggerToast = (message: string, isSuccess = true) => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { show: true, message, isSuccess }
  toastTimer = setTimeout(() => {
    toast.value.show = false
  }, 3200)
}

// Navigation handler
const handleTabChange = (tab: number) => {
  activeTab.value = tab
  if (tab === 1) {
    router.push('/checker')
  } else if (tab === 2) {
    triggerToast('Menampilkan Form Initial Exam (Warehouse Inspection)')
  } else if (tab === 3) {
    router.push('/checker/riwayat')
  }
}

// Retake photo simulation
const handleRetakePhoto = () => {
  triggerToast('Kamera Aktif: Mengambil foto bukti coldbox baru...')
  setTimeout(() => {
    triggerToast('Foto bukti fisik berhasil diperbarui & sinkron ke AI OCR')
  }, 900)
}

// Zoom photo
const handleZoomPhoto = () => {
  isPhotoModalOpen.value = true
}

// Submit exam
const handleSubmitExam = (isPassed: boolean) => {
  submissionResultPassed.value = isPassed
  isSubmitModalOpen.value = true
  triggerToast(
    isPassed
      ? 'Hasil Initial Exam berhasil dikirim ke Admin Dispatcher (pending_approval)'
      : 'Initial Exam dikirim dengan status GAGAL / HELD',
    isPassed
  )
}

// Reject / Mark as held
const handleRejectExam = () => {
  packagingPassed.value = false
  sealPassed.value = false
  submissionResultPassed.value = false
  isSubmitModalOpen.value = true
  triggerToast('Kargo ditandai Gagal / Ditolak (status -> held)', false)
}
</script>

<template>
  <div class="min-h-screen bg-surface font-body-md text-on-surface antialiased flex flex-col justify-between selection:bg-primary-fixed selection:text-on-primary-fixed">
    <!-- Header Navigation with 3 Feature Tabs -->
    <CheckerNavbar
      :active-tab="activeTab"
      @tab-change="handleTabChange"
    />

    <!-- Main Container -->
    <main class="w-full pt-24 pb-12 flex-1">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6">
        <div class="flex flex-col w-full">
          <!-- Active Context Header Banner -->
          <CheckerExamContextBanner
            awb="PHA-260104"
            batch-id="#MCV-2026-X12"
            commodity-title="Vaksin Meningococcal Konjugat ACYW"
            package-count-str="2 Koli Insulated Box"
            origin-facility="PT BioFarma (Bandung)"
            destination-facility="RS Medika Sehat Jakarta"
            temp-range-str="2.0°C – 8.0°C"
            inspector-name="Hendra Wijaya"
            inspector-id="CHK-012"
          />

          <!-- Split Workspace: 7 cols (Checklist) + 5 cols (Proof & AI OCR) -->
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- LEFT COLUMN: Mandatory Physical Checklist -->
            <section class="lg:col-span-7 flex flex-col gap-6">
              <CheckerExamChecklistModule
                v-model:packaging-passed="packagingPassed"
                v-model:seal-passed="sealPassed"
                v-model:label-passed="labelPassed"
                v-model:temperature="temperature"
                v-model:inspection-note="inspectionNote"
              />
            </section>

            <!-- RIGHT COLUMN: Photo Proof Upload & AI Vision OCR Verification -->
            <section class="lg:col-span-5 flex flex-col gap-6">
              <CheckerExamProofAiModule
                :temperature="temperature"
                :packaging-passed="packagingPassed"
                :seal-passed="sealPassed"
                :label-passed="labelPassed"
                :photo-url="photoUrl"
                :ocr-temp="ocrTemp"
                :ocr-seal="ocrSeal"
                @retake-photo="handleRetakePhoto"
                @zoom-photo="handleZoomPhoto"
                @submit-exam="handleSubmitExam"
                @reject-exam="handleRejectExam"
              />
            </section>
          </div>

          <!-- SOP Reminder Strip at Bottom -->
          <CheckerExamSopFooter />
        </div>
      </div>
    </main>

    <!-- Footer -->
    <CheckerFooter />

    <!-- Photo Zoom Modal -->
    <CheckerExamPhotoModal
      :is-open="isPhotoModalOpen"
      :photo-url="photoUrl"
      @close="isPhotoModalOpen = false"
    />

    <!-- Submit / Gate Result Modal -->
    <CheckerExamSubmitModal
      :is-open="isSubmitModalOpen"
      :is-passed="submissionResultPassed"
      awb="PHA-260104"
      :temperature="temperature"
      @close="isSubmitModalOpen = false"
    />

    <!-- Toast Notification -->
    <div
      class="fixed bottom-6 right-6 bg-inverse-surface text-inverse-on-surface px-4 py-3 rounded-xl shadow-xl flex items-center gap-3 transform transition-all duration-300 z-50 pointer-events-none"
      :class="toast.show ? 'translate-y-0 opacity-100' : 'translate-y-24 opacity-0'"
      id="toastNotification"
    >
      <span class="material-symbols-outlined text-secondary-fixed text-xl" id="toastIcon">
        {{ toast.isSuccess ? 'check_circle' : 'info' }}
      </span>
      <span class="font-label-md text-label-md font-medium" id="toastMessage">
        {{ toast.message }}
      </span>
    </div>
  </div>
</template>
