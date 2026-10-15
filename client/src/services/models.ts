export interface Response<T> {
  data: T
}
export interface Inspection {
  id: number
  packaging: boolean
  seal: boolean
  label: boolean
  passed: boolean
  temperature: number
  note: string
  checker_name?: string
  inspected_at?: string
  photo_url?: string | null
}
export interface Approval {
  id: number
  status: string
  reason: string
  inspection_id?: number
}
export interface LiveShipment {
  can_simulate?: boolean
  id: number
  tracking_number: string
  version: number
  status: string
  status_label: string
  sender_name: string
  recipient_name: string
  recipient_phone: string
  address: string
  origin_city: string
  destination_city: string
  item_summary: string
  package_count: number
  weight_kg: number
  package_type: string | null
  batch_number: string | null
  handling_instructions: string
  temperature_profile: string
  temperature_min: number
  temperature_max: number
  current_temperature: number | null
  telemetry_fresh: boolean
  telemetry_simulated: boolean | null
  telemetry_recorded_at: string | null
  last_location: string
  scheduled_at: string | null
  recipient_ready: boolean | null
  is_demo: boolean
  category: string
  route_display: string
  temp_label: string
  estimate_label: string
  inspection: Inspection | null
  inspection_detail?: Inspection | null
  approval?: Approval | null
  report_ids?: number[]
  courier?: { id: number; name: string } | null
  can_submit?: boolean
}
export interface LiveTask {
  id: number
  awb: string
  status: 'assigned' | 'in_transit' | 'hold' | 'delivered'
  workflow_status: string
  status_label: string
  version: number
  item_title: string
  origin_name: string
  destination_name: string
  destination_address: string
  temp_min: number
  temp_max: number
  temp_actual: number | null
  probe_temp_str: string
  telemetry_stale: boolean
  telemetry_recorded_at: string | null
  is_demo: boolean
  weight_str: string
  destination_detail: string
  origin_detail: string
  handling_instructions: string
  scheduled_at_str: string | null
}
export interface Sample {
  id: number
  temperature: number | null
  latitude: number
  longitude: number
  is_simulated: boolean
  recorded_at: string
  speed_kmh: number | null
}
export interface Issue {
  id: number
  shipment_id: number
  type: string
  status: string
  description: string
  resolution_reason: string | null
  created_at: string
  latest_decision?: { action: string; reason: string } | null
  photo_path?: string | null
  decision?: { action: string; reason: string } | null
}
export interface Report {
  id: number
  title: string
  revision: number
  sha256: string
  snapshot_sha256?: string
  is_demo: boolean
  published_at: string
  download_url?: string
}
export interface EventRecord {
  id: number
  type: string
  title: string
  reason?: string
  from_status?: string
  to_status?: string
  created_at: string
}
export interface Lookups {
  buyers: { id: number; name: string }[]
  checkers: { id: number; name: string }[]
  couriers: {
    id: number
    name: string
    capacity_kg: number | null
    reserved_kg: number
  }[]
}
export const awbPath = (awb: string) => encodeURIComponent(awb)
export function dateText(value?: string | null): string {
  if (!value) return 'Belum tersedia'
  const date = new Date(value)
  return Number.isNaN(date.getTime())
    ? value
    : new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
        timeZone: 'Asia/Jakarta',
      }).format(date) + ' WIB'
}
export const terminal = (status: string) =>
  ['completed', 'delivered', 'returned', 'cancelled'].includes(status)
