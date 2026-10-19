import axios, { AxiosError } from "axios";

export const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api",
  headers: { Accept: "application/json" },
  timeout: 30000,
});

api.interceptors.request.use((config) => {
  if (typeof window !== "undefined") {
    const token = localStorage.getItem("anteraja_token");
    if (token) config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error: AxiosError) => {
    if (
      error.response?.status === 401 &&
      typeof window !== "undefined" &&
      !window.location.pathname.startsWith("/login")
    ) {
      clearSession();
      window.dispatchEvent(new Event("anteraja:unauthorized"));
    }

    return Promise.reject(error);
  },
);

export type Role = "admin" | "checker" | "courier" | "customer";

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  role: Role;
  customer_id: number | null;
}

export interface Product {
  id: number;
  sku: string;
  name: string;
  category: string | null;
  min_temperature: string | number;
  max_temperature: string | number;
  max_excursion_seconds: number | null;
  temperature_protocol: string | null;
  protocol_reference: string | null;
  is_active: boolean;
}

export interface Customer {
  id: number;
  company_name: string;
  contact_name: string | null;
  email: string | null;
  whatsapp_number: string | null;
  whatsapp_opt_in: boolean;
  company_type: "manufacturer" | "distributor" | "pharmacy";
  address: string | null;
  is_active: boolean;
}

export interface CustomerAccount extends AuthUser {
  is_active: boolean;
  customer: Customer | null;
}

export interface ThermalCheck {
  id: number;
  temperature_celsius: string | number;
  packaging_condition: string;
  physical_condition: string;
  notes: string | null;
  photo_path: string | null;
  checked_at: string;
  ai_assessment_status?: string;
  ai_assessment?: {
    risk_level?: string;
    summary?: string;
    recommended_actions?: string[];
    requires_human_review?: boolean;
  } | null;
}

export interface ThermalIncident {
  id: number;
  shipment_id: number;
  temperature_celsius: string | number;
  allowed_min_temperature: string | number;
  allowed_max_temperature: string | number;
  exposure_duration_seconds: number | null;
  severity: string;
  incident_status: string;
  description: string | null;
  corrective_action: string | null;
  detected_at: string;
  recovered_at: string | null;
  ai_assessment_status?: string;
  ai_model?: string | null;
  ai_assessed_at?: string | null;
  ai_assessment?: ThermalCheck["ai_assessment"];
  shipment?: Shipment;
}

export interface TrackingLocation {
  id: number;
  latitude: string | number;
  longitude: string | number;
  is_simulated: boolean;
  recorded_at: string;
}

export interface Shipment {
  id: number;
  shipment_number: string;
  customer_id: number;
  origin_customer_user_id: number | null;
  destination_customer_user_id: number | null;
  product_id: number;
  quantity: number;
  origin_address: string;
  destination_address: string;
  checker_id: number | null;
  courier_id: number | null;
  status: string;
  notes: string | null;
  scheduled_at: string | null;
  started_at: string | null;
  delivered_at: string | null;
  cancelled_at: string | null;
  created_at: string;
  updated_at: string;
  customer?: Customer;
  origin_customer_user?: CustomerAccount | null;
  destination_customer_user?: CustomerAccount | null;
  product?: Product;
  checker?: AuthUser;
  courier?: AuthUser;
  thermal_checks?: ThermalCheck[];
  thermal_incidents?: ThermalIncident[];
  tracking_locations?: TrackingLocation[];
  events?: Array<{
    id: number;
    event_type: string;
    description: string | null;
    occurred_at: string;
    from_status: string | null;
    to_status: string | null;
  }>;
  delivery_proofs?: Array<{
    id: number;
    photo_path: string;
    recipient_name: string;
    notes: string | null;
    delivered_at: string;
  }>;
}

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface PageResult<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export function getStoredUser(): AuthUser | null {
  if (typeof window === "undefined") return null;

  const value = localStorage.getItem("anteraja_user");
  if (!value) return null;

  try {
    return JSON.parse(value) as AuthUser;
  } catch {
    clearSession();
    return null;
  }
}

export function setSession(token: string, user: AuthUser): void {
  localStorage.setItem("anteraja_token", token);
  localStorage.setItem("anteraja_user", JSON.stringify(user));
}

export function clearSession(): void {
  localStorage.removeItem("anteraja_token");
  localStorage.removeItem("anteraja_user");
}

export function roleHome(role: Role): string {
  return {
    admin: "/admin",
    checker: "/checker",
    courier: "/courier",
    customer: "/customer/tracking",
  }[role];
}

export function errorMessage(error: unknown): string {
  if (axios.isAxiosError(error)) {
    const data = error.response?.data as
      | { message?: string; errors?: Record<string, string[]> }
      | undefined;
    const validation = data?.errors
      ? Object.values(data.errors).flat()[0]
      : undefined;

    return validation ?? data?.message ?? error.message;
  }

  return error instanceof Error ? error.message : "Terjadi kesalahan.";
}

export function unwrap<T>(payload: unknown): T {
  if (
    payload &&
    typeof payload === "object" &&
    "data" in payload &&
    !Array.isArray((payload as { data?: unknown }).data)
  ) {
    return (payload as { data: T }).data;
  }

  return payload as T;
}

export function listFrom<T>(payload: unknown): T[] {
  if (!payload || typeof payload !== "object") return [];
  const data = (payload as { data?: unknown }).data;
  return Array.isArray(data) ? (data as T[]) : [];
}

export function pageFrom<T>(payload: unknown): PageResult<T> {
  if (!payload || typeof payload !== "object") {
    return { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0 };
  }

  const value = payload as Partial<PageResult<T>>;

  return {
    data: Array.isArray(value.data) ? value.data : [],
    current_page: value.current_page ?? 1,
    last_page: value.last_page ?? 1,
    per_page: value.per_page ?? 15,
    total: value.total ?? 0,
  };
}
