"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import { ArrowLeft } from "lucide-react";
import { api, errorMessage, type Shipment } from "@/lib/api";
import { DashboardLayout } from "@/components/layouts/dashboard-layout";
import { buttonClass, Card, ErrorState, formatDate, inputClass, Loading, Notice, PageTitle, StatusBadge } from "@/lib/ui";

export default function CheckerPage() {
  const [shipments, setShipments] = useState<Shipment[]>([]);
  const [selectedId, setSelectedId] = useState("");
  const [temperature, setTemperature] = useState("");
  const [packaging, setPackaging] = useState("good");
  const [physical, setPhysical] = useState("good");
  const [notes, setNotes] = useState("");
  const [photo, setPhoto] = useState<File | null>(null);
  const [error, setError] = useState("");
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);

  const load = useCallback(async () => {
    try {
      const response = await api.get("/checker/shipments", { params: { per_page: 100 } });
      setShipments(response.data.data ?? []);
      setError("");
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { void Promise.resolve().then(load); }, [load]);

  const inspectable = useMemo(() => shipments.filter((item) =>
    item.status === "checker_assigned" && !item.thermal_checks?.length
  ), [shipments]);
  const selected = inspectable.find((item) => String(item.id) === selectedId);

  async function submit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!selected) return setError("Pilih shipment yang ditugaskan kepada Anda.");
    setBusy(true);
    setError("");
    setMessage("");
    try {
      const body = new FormData();
      body.append("temperature_celsius", temperature);
      body.append("packaging_condition", packaging);
      body.append("physical_condition", physical);
      if (notes) body.append("notes", notes);
      if (photo) body.append("photo", photo);
      const response = await api.post(`/shipments/${selected.id}/thermal-checks`, body);
      setMessage(`${response.data.message} Analisis AI: ${response.data.data.ai_assessment_status}.`);
      setTemperature("");
      setNotes("");
      setPhoto(null);
      setSelectedId("");
      await load();
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setBusy(false);
    }
  }

  return (
    <DashboardLayout roles={["checker"]}>
      <div className="space-y-5">
        <PageTitle title="Pemeriksaan shipment" description="Shipment yang ditugaskan kepada akun checker Anda." />
        {error && <ErrorState message={error} />}
        {message && <Notice tone="success">{message}</Notice>}
        <div className="grid gap-5 xl:grid-cols-[0.9fr_1.1fr]">
          <Card className={!selectedId ? "block" : "hidden md:block"}>
            <h2 className="mb-4 font-semibold">Tugas pemeriksaan</h2>
            {loading ? <Loading /> : shipments.length === 0 ? <p className="text-sm text-slate-500">Belum ada shipment yang ditugaskan kepada Anda.</p> : <div className="space-y-3">{shipments.map((shipment) => {
              const canInspect = shipment.status === "checker_assigned" && !shipment.thermal_checks?.length;
              return <button key={shipment.id} disabled={!canInspect} onClick={() => setSelectedId(String(shipment.id))} className={`w-full rounded-xl border p-4 text-left ${!canInspect ? "cursor-not-allowed bg-slate-50 opacity-70" : String(shipment.id) === selectedId ? "border-blue-500 bg-blue-50" : "border-slate-200"}`}><div className="flex items-center justify-between gap-2"><strong>{shipment.shipment_number}</strong><StatusBadge status={shipment.status} /></div><p className="mt-1 text-sm text-slate-600">{shipment.product?.name} · {shipment.customer?.company_name}</p><p className="mt-1 text-xs text-slate-500">{shipment.origin_address} → {shipment.destination_address}</p><p className="mt-1 text-xs text-slate-500">Rentang {shipment.product?.min_temperature}–{shipment.product?.max_temperature}°C {canInspect ? "" : "· Tidak tersedia untuk pemeriksaan baru"}</p></button>;
            })}</div>}
          </Card>
          <Card className={selectedId ? "block" : "hidden md:block"}>
            {selectedId && <button type="button" className="mb-4 inline-flex min-h-10 items-center gap-2 text-sm font-medium text-blue-700 md:hidden" onClick={() => setSelectedId("")}><ArrowLeft size={16} />Kembali ke daftar</button>}
            <h2 className="font-semibold">Form pemeriksaan</h2>
            <p className="mt-1 text-sm text-slate-500">Input berasal dari pemeriksaan manual; foto boleh dilampirkan.</p>
            {!selected ? <p className="mt-5 text-sm text-slate-500">Pilih shipment berstatus checker assigned yang belum diperiksa.</p> : <form onSubmit={submit} className="mt-4 space-y-4">
              <div className="rounded-xl bg-slate-50 p-3 text-sm"><strong>{selected.shipment_number}</strong><p className="mt-1">{selected.product?.name} · target {selected.product?.min_temperature}–{selected.product?.max_temperature}°C</p></div>
              <label className="block text-sm font-medium">Suhu produk (°C)<input className={inputClass} type="number" step="0.01" min="-100" max="100" required value={temperature} onChange={(e) => setTemperature(e.target.value)} /></label>
              <label className="block text-sm font-medium">Kondisi kemasan<select className={inputClass} value={packaging} onChange={(e) => setPackaging(e.target.value)}><option value="good">Baik</option><option value="damaged">Rusak</option><option value="wet">Basah</option><option value="opened">Terbuka</option></select></label>
              <label className="block text-sm font-medium">Kondisi fisik produk<select className={inputClass} value={physical} onChange={(e) => setPhysical(e.target.value)}><option value="good">Baik</option><option value="damaged">Rusak</option><option value="suspected">Perlu pemeriksaan</option></select></label>
              <label className="block text-sm font-medium">Catatan<textarea className={inputClass} maxLength={2000} rows={3} value={notes} onChange={(e) => setNotes(e.target.value)} /></label>
              <label className="block text-sm font-medium">Foto bukti (opsional, maks. 5 MB)<input className={inputClass} type="file" accept="image/jpeg,image/png,image/webp" onChange={(e) => setPhoto(e.target.files?.[0] ?? null)} /></label>
              {photo && <p className="text-xs text-slate-500">{photo.name} · {formatDate(new Date(photo.lastModified).toISOString())}</p>}
              <button className={buttonClass} disabled={busy}>{busy ? "Mengirim..." : "Simpan pemeriksaan"}</button>
            </form>}
          </Card>
        </div>
      </div>
    </DashboardLayout>
  );
}
