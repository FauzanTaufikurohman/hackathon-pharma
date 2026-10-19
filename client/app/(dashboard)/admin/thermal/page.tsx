"use client";

import { useCallback, useEffect, useState } from "react";
import { Bug, Download } from "lucide-react";
import { api, errorMessage, type Shipment, type ThermalIncident } from "@/lib/api";
import { DashboardLayout } from "@/components/layouts/dashboard-layout";
import { buttonClass, Card, ErrorState, formatDate, inputClass, Loading, Notice, PageTitle, StatusBadge } from "@/lib/ui";

export default function ThermalPage() {
  const [shipments, setShipments] = useState<Shipment[]>([]);
  const [incidents, setIncidents] = useState<ThermalIncident[]>([]);
  const [shipmentId, setShipmentId] = useState("");
  const [temperature, setTemperature] = useState("");
  const [duration, setDuration] = useState("3600");
  const [description, setDescription] = useState("");
  const [correctiveAction, setCorrectiveAction] = useState("");
  const [assessmentNote, setAssessmentNote] = useState("");
  const [selectedIncident, setSelectedIncident] = useState<number | null>(null);
  const [debugIncident, setDebugIncident] = useState<number | null>(null);
  const [debugInfo, setDebugInfo] = useState<Record<number, {
    debug_enabled: boolean;
    ai_assessment_status: string;
    ai_model: string | null;
    ai_assessed_at: string | null;
    diagnostics: Record<string, unknown> | null;
  }>>({});
  const [debugBusy, setDebugBusy] = useState<number | null>(null);
  const [error, setError] = useState("");
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);

  const load = useCallback(async () => {
    try {
      const [shipmentResponse, incidentResponse] = await Promise.all([
        api.get("/shipments", { params: { per_page: 100, status: "in_transit" } }),
        api.get("/thermal-incidents", { params: { per_page: 100 } }),
      ]);
      setShipments(shipmentResponse.data.data ?? []);
      setIncidents(incidentResponse.data.data ?? []);
      setError("");
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { void Promise.resolve().then(load); }, [load]);

  async function simulate(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!shipmentId) return setError("Pilih shipment terlebih dahulu.");
    setBusy(true);
    setError("");
    setMessage("");
    try {
      const response = await api.post(`/shipments/${shipmentId}/thermal-simulations`, {
        temperature_celsius: Number(temperature),
        exposure_duration_seconds: Number(duration),
        description: description || undefined,
      });
      setMessage(response.data.message);
      setTemperature("");
      setDescription("");
      await load();
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setBusy(false);
    }
  }

  async function resolve(incidentId: number) {
    setBusy(true);
    setError("");
    setMessage("");
    try {
      const response = await api.patch(`/thermal-incidents/${incidentId}/resolve`, {
        corrective_action: correctiveAction,
        assessment_note: assessmentNote,
      });
      setMessage(response.data.message);
      setCorrectiveAction("");
      setAssessmentNote("");
      setSelectedIncident(null);
      await load();
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setBusy(false);
    }
  }

  async function downloadIncident(id: number) {
    setError("");
    try {
      const response = await api.get(`/reports/thermal-incidents/${id}/pdf`, { responseType: "blob" });
      const url = URL.createObjectURL(response.data as Blob);
      const anchor = document.createElement("a");
      anchor.href = url;
      anchor.download = `thermal-incident-${id}.pdf`;
      anchor.click();
      URL.revokeObjectURL(url);
    } catch (requestError) {
      setError(errorMessage(requestError));
    }
  }

  async function loadDebug(id: number) {
    setDebugBusy(id);
    setError("");
    try {
      const response = await api.get(`/thermal-incidents/${id}/debug`);
      setDebugInfo((previous) => ({ ...previous, [id]: response.data.data }));
      setDebugIncident((previous) => previous === id ? null : id);
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setDebugBusy(null);
    }
  }

  const selected = shipments.find((shipment) => String(shipment.id) === shipmentId);

  return (
    <DashboardLayout roles={["admin"]}>
      <div className="space-y-5">
        <PageTitle title="Thermal simulation & incidents" description="Simulasi dan penilaian insiden hanya tersedia untuk shipment yang sedang dalam perjalanan." />
        {error && <ErrorState message={error} />}
        {message && <Notice tone="success">{message}</Notice>}
        <div className="grid gap-5 xl:grid-cols-[0.85fr_1.15fr]">
          <Card>
            <h2 className="mb-1 font-semibold">Simulasikan excursion</h2>
            <p className="mb-4 text-sm text-slate-500">Hanya shipment in-transit yang dapat dipilih. Durasi dimasukkan dalam detik; 86.400 detik = 24 jam.</p>
            <form onSubmit={simulate} className="space-y-4">
              <label className="block text-sm font-medium">Shipment<select className={inputClass} required value={shipmentId} onChange={(e) => setShipmentId(e.target.value)}><option value="">Pilih shipment</option>{shipments.map((shipment) => <option key={shipment.id} value={shipment.id}>{shipment.shipment_number} — {shipment.product?.name}</option>)}</select></label>
              {selected?.product && <p className="rounded-xl bg-slate-50 p-3 text-sm text-slate-700">Rentang produk: {selected.product.min_temperature}–{selected.product.max_temperature}°C</p>}
              <label className="block text-sm font-medium">Suhu simulasi (°C)<input className={inputClass} type="number" step="0.01" min="-100" max="100" required value={temperature} onChange={(e) => setTemperature(e.target.value)} /></label>
              <label className="block text-sm font-medium">Durasi paparan (detik)<input className={inputClass} type="number" min="1" max="2592000" required value={duration} onChange={(e) => setDuration(e.target.value)} /></label>
              <label className="block text-sm font-medium">Catatan simulasi (opsional)<textarea className={inputClass} maxLength={2000} rows={3} value={description} onChange={(e) => setDescription(e.target.value)} /></label>
              <button className={buttonClass} disabled={busy || loading}>{busy ? "Menyimpan..." : "Catat simulasi & minta analisis AI"}</button>
            </form>
          </Card>
          <Card>
            <h2 className="mb-4 font-semibold">Riwayat insiden suhu</h2>
            {loading ? <Loading /> : incidents.length === 0 ? <p className="text-sm text-slate-500">Belum ada insiden.</p> : <div className="space-y-3">{incidents.map((incident) => <article key={incident.id} className="rounded-xl border border-slate-200 p-4"><div className="flex flex-wrap items-center justify-between gap-2"><div className="font-medium">{incident.shipment?.shipment_number ?? `Shipment #${incident.shipment_id}`} · {incident.temperature_celsius}°C</div><div className="flex gap-2"><StatusBadge status={incident.severity} /><StatusBadge status={incident.incident_status} /></div></div><p className="mt-2 text-sm text-slate-600">{incident.description}</p><p className="mt-1 text-xs text-slate-500">{incident.exposure_duration_seconds ?? "Tidak diketahui"} detik · {formatDate(incident.detected_at)} · AI: {incident.ai_assessment_status ?? "pending"}</p>{incident.ai_assessment && <div className="mt-3 rounded-lg bg-blue-50 p-3 text-sm"><strong>AI · {incident.ai_assessment.risk_level}</strong><p className="mt-1">{incident.ai_assessment.summary}</p>{incident.ai_assessment.recommended_actions?.map((action) => <p key={action} className="mt-1">• {action}</p>)}</div>}<div className="mt-3 flex flex-wrap gap-x-4 gap-y-2"><button className="inline-flex items-center gap-1 text-sm text-blue-700" onClick={() => void downloadIncident(incident.id)}><Download size={15} />Unduh PDF insiden</button><button className="inline-flex items-center gap-1 text-sm text-blue-700" disabled={debugBusy === incident.id} onClick={() => void loadDebug(incident.id)}><Bug size={15} />{debugBusy === incident.id ? "Memuat debug..." : debugIncident === incident.id ? "Tutup debug AI" : "Debug AI"}</button></div>{debugIncident === incident.id && debugInfo[incident.id] && <section className="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm"><h3 className="font-semibold">Diagnostik automation AI</h3><p className="mt-1 text-slate-700">Status: {debugInfo[incident.id].ai_assessment_status} · Model: {debugInfo[incident.id].ai_model ?? "belum ada"} · Waktu: {debugInfo[incident.id].ai_assessed_at ? formatDate(debugInfo[incident.id].ai_assessed_at!) : "belum dianalisis"}</p>{!debugInfo[incident.id].debug_enabled ? <p className="mt-2 text-slate-600">Mode debug nonaktif. Set THERMAL_AI_DEBUG=true di backend, bersihkan config cache, lalu restart queue worker.</p> : debugInfo[incident.id].diagnostics ? <pre className="mt-2 max-h-64 overflow-auto rounded-md bg-white p-3 text-xs">{JSON.stringify(debugInfo[incident.id].diagnostics, null, 2)}</pre> : <p className="mt-2 text-slate-600">Belum ada diagnostik tersimpan. Pastikan worker queue AI berjalan; detail error tersedia di backend/storage/logs/laravel.log.</p>}<p className="mt-2 text-xs text-slate-500">Diagnostik tidak berisi API key atau isi prompt. Saran AI bukan persetujuan kelayakan produk.</p></section>}{incident.incident_status === "open" && <div className="mt-3">{selectedIncident === incident.id ? <div className="space-y-2"><textarea className={inputClass} placeholder="Tindakan korektif (min. 5 karakter)" value={correctiveAction} onChange={(e) => setCorrectiveAction(e.target.value)} /><textarea className={inputClass} placeholder="Catatan penilaian manusia (min. 5 karakter)" value={assessmentNote} onChange={(e) => setAssessmentNote(e.target.value)} /><button className={buttonClass} disabled={busy || correctiveAction.trim().length < 5 || assessmentNote.trim().length < 5} onClick={() => void resolve(incident.id)}>Catat penilaian dan resolusi</button></div> : <button className="text-sm font-semibold text-blue-700" onClick={() => setSelectedIncident(incident.id)}>Nilai / selesaikan insiden</button>}</div>}</article>)}</div>}
          </Card>
        </div>
      </div>
    </DashboardLayout>
  );
}
