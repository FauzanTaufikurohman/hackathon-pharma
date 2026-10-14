/**
 * Model TypeScript sesuai dengan Skema Database (schema.md) & PRD/FRD
 * Sistem Pharma Logistics Workflow Architecture
 */

export type TemperatureProfile = 'ambient' | 'controlled'

export type ShipmentStatus =
  | 'created'
  | 'inspection'
  | 'pending_approval'
  | 'approved'
  | 'rejected'
  | 'pending_assignment'
  | 'assigned'
  | 'in_transit'
  | 'deviation_hold'
  | 'delivered'
  | 'completed'

export interface Shipment {
  id: number
  tracking_number: string // 'PHA-260104'
  buyer_id: number
  inspector_id?: number
  created_by?: number
  sender_name: string // 'PT BioFarma (Persero) Bandung'
  sender_chat_id?: string
  recipient_name: string // 'RS Medika Sehat (Jakarta)'
  recipient_phone: string
  address: string
  origin_city: string
  destination_city: string
  item_summary: string // 'Vaksin Meningococcal (2 Koli / 400 Dosis)'
  package_count: number
  handling_instructions: string
  temperature_profile: TemperatureProfile
  temperature_min: number // 2.0
  temperature_max: number // 8.0
  current_temperature?: number // 4.2
  current_speed_kmh?: number
  scheduled_at: string // '2026-10-14 15:30:00'
  status: ShipmentStatus
  last_location: string
  version: number
  recipient_ready: boolean | null
  completed_at?: string | null
  category?: 'transit' | 'qc' | 'delivered'
  route_display?: string
  status_label?: string
  temp_label?: string
  estimate_label?: string
  action_type?: 'live' | 'warehouse' | 'gps' | 'pod'
  action_label?: string
  batch_number?: string
  weight_kg?: number
  package_type?: string
  tolerance_threshold?: string
  inspection?: Inspection
  created_at: string
  updated_at: string
}

export interface Inspection {
  id: number
  shipment_id: number
  packaging: boolean
  seal: boolean
  label: boolean
  note: string
  temperature: number
  passed: boolean
  sensor_device?: string
  hub_name?: string
  checker_name?: string
  inspected_at?: string
}

export interface FaskesFacility {
  facility_id: string // 'RSMS-FARM-09'
  name: string // 'RS Medika Sehat Jakarta'
  department: string // 'Instalasi Farmasi Pusat'
  verified_status: string // 'VERIFIED BUYER'
  pharmacist_in_charge: string // 'Apt. Sarah Nadia, S.Farm'
  pharmacist_license: string // 'STRA-19920815-2024-001'
  cdob_certification: string // 'CDOB-BPOM-JKT-2024.3'
}

export interface OperationalMetrics {
  active_shipments_count: number
  critical_count: number
  average_temperature_str: string // '4.2°C – 4.5°C'
  compliance_rate: number // 100
  incident_count: number // 0
  available_reports_count: number
  digital_hash: string
  next_arrival_time: string
  next_arrival_item: string
}

export type AuditEventType = 'inspect' | 'approve' | 'depart' | 'deliver'

export interface AuditEvent {
  id: number
  shipment_id: number
  type: AuditEventType
  title: string
  timestamp: string
  actor_name: string
  recipient_name?: string
  recipient_dept?: string
  temperature?: number
  seal_status?: string
  fleet_info?: string
  route_info?: string
  decision?: 'approved' | 'held' | 'rejected'
  justification?: string
  location_note?: string
  metadata_str?: string
}

export interface BapReport {
  id: number
  tracking_number: string
  status_label: string
  temperature_min: number
  temperature_max: number
  safe_profile_label: string
  revision: number
  revision_label: string
  recipient_officer: string
  recipient_nipa: string
  audit_transaction_id: string
  digital_signature_verified: boolean
  cooler_box_id: string
  cooler_box_desc: string
}

export interface CourierProfile {
  id: number
  courier_code: string
  name: string
  role_label: string
  shift_name: string
  hub_base: string
  duty_active: boolean
  cooler_box_code: string
  current_cooler_temp: number
  ble_sync: boolean
  battery_pct: number
  avatar_url: string
}

export interface CourierMetrics {
  new_tasks_count: number
  on_route_count: number
  issues_hold_count: number
  completed_today_count: number
}

export type CourierTaskStatus = 'assigned' | 'in_transit' | 'hold' | 'delivered'

export interface CourierTaskItem {
  id: number
  awb: string
  status: CourierTaskStatus
  status_label: string
  category_label: string
  is_urgent?: boolean
  urgent_badge?: string
  temperature_profile_label: string
  item_title: string
  batch_number: string
  probe_temp_str: string
  seal_str: string
  battery_pct?: number
  origin_name: string
  origin_time: string
  destination_name: string
  destination_eta: string
  destination_address: string
  cooler_box_desc: string
  tolerance_label: string
  route_progress_note?: string
  recipient_person?: string
  pod_number?: string
  cta_label: string
  cta_type: 'accept_reject' | 'monitor' | 'view_pod'
  hint_text: string
}



