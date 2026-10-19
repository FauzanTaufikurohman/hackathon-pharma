"use client";

import { useCallback, useEffect, useRef, useState } from "react";
import { ArrowLeft, MapPin } from "lucide-react";
import { DashboardLayout } from "@/components/layouts/dashboard-layout";
import { TrackingMap } from "@/components/tracking-map";
import { api, errorMessage, type Shipment } from "@/lib/api";
import { buttonClass, Card, ErrorState, inputClass, Loading, Notice, PageTitle, StatusBadge } from "@/lib/ui";

export default function CourierPage() {
  const [shipments, setShipments] = useState<Shipment[]>([]);
  const [selectedId, setSelectedId] = useState<number | null>(null);
  const [tracking, setTracking] = useState(false);
  const [photo, setPhoto] = useState<File | null>(null);
  const [error, setError] = useState("");
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);
  const lastLocationSentAt = useRef(0);

  const load = useCallback(async () => {
    try {
      const response = await api.get("/courier/shipments");
      setShipments(response.data.data ?? []);
      setError("");
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { void Promise.resolve().then(load); }, [load]);
  const selected = shipments.find((item) => item.id === selectedId);

  function toggleTracking() {
    if (tracking) {
      setTracking(false);
      return;
    }
    if (!navigator.geolocation) {
      setError("Browser ini tidak mendukung akses lokasi GPS.");
      return;
    }
    setError("");
    setTracking(true);
  }

  useEffect(() => {
    if (!tracking || selected?.status !== "in_transit" || selected.id !== selectedId) return;
    const watchId = navigator.geolocation.watchPosition(
      (position) => {
        if (Date.now() - lastLocationSentAt.current < 15000) return;
        lastLocationSentAt.current = Date.now();
        api.post(`/shipments/${selected.id}/locations`, {
          latitude: position.coords.latitude,
          longitude: position.coords.longitude,
        }).then((response) => {
          const location = response.data.data;
          setShipments((current) => current.map((shipment) =>
            shipment.id === selected.id
              ? { ...shipment, tracking_locations: [location, ...(shipment.tracking_locations ?? [])] }
              : shipment
          ));
          setError("");
        }).catch((requestError: unknown) => {
          setError(errorMessage(requestError));
        });
      },
      (locationError) => {
        setError(`Gagal mengambil lokasi GPS: ${locationError.message}`);
        setTracking(false);
      },
      { enableHighAccuracy: true, maximumAge: 10000, timeout: 20000 },
    );

    return () => navigator.geolocation.clearWatch(watchId);
  }, [selected?.id, selected?.status, selectedId, tracking]);

  async function send(path: string, body?: FormData | Record<string, number>) {
    if (!selected) return;
    setBusy(true);
    setError("");
    setMessage("");
    try {
      const response = await api.post(`/shipments/${selected.id}/${path}`, body);
      const reportStatus = path === "delivery-proof"
        ? response.data.whatsapp_report_queued
          ? " Laporan PDF diantrekan untuk nomor WhatsApp customer yang sudah opt-in."
          : " Laporan PDF tidak diantrekan karena belum ada customer dengan nomor WhatsApp dan persetujuan aktif."
        : "";
      setMessage(`${response.data.message ?? "Perubahan tersimpan."}${reportStatus}`);
      await load();
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setBusy(false);
    }
  }

  async function complete(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!photo) return setError("Foto bukti serah terima wajib diunggah.");
    const body = new FormData();
    body.append("photo", photo);
    await send("delivery-proof", body);
    setPhoto(null);
  }

  return (
    <DashboardLayout roles={["courier"]}>
      <div className="space-y-5">
        <PageTitle title="Pengantaran saya" description="Kelola shipment yang ditugaskan ke akun kurir Anda." />
        {error && <ErrorState message={error} />}
        {message && <Notice tone="success">{message}</Notice>}
        <div className="grid gap-5 xl:grid-cols-[0.9fr_1.1fr]">
          <Card className={selectedId === null ? "block" : "hidden md:block"}>
            <h2 className="mb-4 font-semibold">Daftar penugasan</h2>
            {loading ? <Loading /> : shipments.length === 0 ? <p className="text-sm text-slate-500">Belum ada shipment yang ditugaskan.</p> : <div className="space-y-3">{shipments.map((shipment) => <button key={shipment.id} onClick={() => { setTracking(false); setSelectedId(shipment.id); }} className={`w-full rounded-xl border p-4 text-left ${selectedId === shipment.id ? "border-blue-500 bg-blue-50" : "border-slate-200"}`}><div className="flex items-center justify-between gap-2"><strong>{shipment.shipment_number}</strong><StatusBadge status={shipment.status} /></div><p className="mt-1 text-sm">{shipment.product?.name} · {shipment.quantity} unit</p><p className="mt-1 text-xs text-slate-500">{shipment.origin_address} → {shipment.destination_address}</p><p className="mt-1 text-xs text-slate-500">{shipment.origin_customer_user?.customer?.company_name ?? shipment.customer?.company_name} → {shipment.destination_customer_user?.customer?.company_name ?? "Penerima"}</p></button>)}</div>}
          </Card>
          <Card className={selectedId !== null ? "block" : "hidden md:block"}>
            {!selected ? <p className="text-sm text-slate-500">Pilih penugasan untuk memulai pengantaran atau mencatat lokasi.</p> : <>
              <button type="button" className="mb-4 inline-flex min-h-10 items-center gap-2 text-sm font-medium text-blue-700 md:hidden" onClick={() => { setTracking(false); setSelectedId(null); }}><ArrowLeft size={16} />Kembali ke daftar</button>
              <div className="flex flex-wrap items-center justify-between gap-2"><h2 className="font-semibold">{selected.shipment_number}</h2><StatusBadge status={selected.status} /></div>
              <p className="mt-2 text-sm text-slate-600">{selected.origin_customer_user?.customer?.company_name ?? selected.customer?.company_name} → {selected.destination_customer_user?.customer?.company_name ?? "Penerima"}</p>
              <p className="mt-1 text-xs text-slate-500">{selected.origin_address} → {selected.destination_address}</p>
              {selected.status === "checker_assigned" && <button className={`${buttonClass} mt-4`} disabled={busy} onClick={() => void send("start")}>Mulai pengantaran</button>}
              {selected.status === "in_transit" && <div className="mt-5 space-y-5">
                <section className="border-t pt-4">
                  <h3 className="font-medium">Lokasi GPS simulasi</h3>
                  <p className="mt-1 text-xs text-slate-500">Saat tracking aktif, browser mengambil titik GPS dan mengirimkannya berkala tanpa menampilkan koordinat.</p>
                  <button type="button" className="mt-3 rounded-xl border px-4 py-2 text-sm font-semibold" disabled={busy} onClick={toggleTracking}><MapPin size={15} className="mr-1 inline" />{tracking ? "Hentikan tracking GPS" : "Aktifkan tracking GPS"}</button>
                  {tracking && <p className="mt-2 text-xs font-medium text-emerald-700">Tracking aktif selama halaman kurir terbuka.</p>}
                  {selected.tracking_locations?.[0] && <div className="mt-3"><TrackingMap location={selected.tracking_locations[0]} /></div>}
                </section>
                <form onSubmit={complete} className="space-y-3 border-t pt-4">
                  <h3 className="font-medium">Konfirmasi serah terima</h3>
                  <p className="text-sm text-slate-600">Penerima: <strong>{selected.destination_customer_user?.customer?.contact_name ?? selected.destination_customer_user?.name ?? selected.destination_customer_user?.customer?.company_name ?? selected.customer?.contact_name ?? selected.customer?.company_name ?? "Customer tujuan shipment"}</strong></p>
                  <label className="block text-sm">Foto bukti (wajib, maks. 5 MB)<input required type="file" accept="image/jpeg,image/png,image/webp" className={inputClass} onChange={(e) => setPhoto(e.target.files?.[0] ?? null)} /></label>
                  <button className={buttonClass} disabled={busy}>{busy ? "Mengirim..." : "Selesaikan pengiriman"}</button>
                </form>
              </div>}
              {selected.status === "delivered" && <p className="mt-4 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800">Shipment telah selesai. Laporan PDF akan dikirim sesuai konfigurasi WhatsApp customer.</p>}
              {selected.status === "cancelled" && <p className="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-800">Shipment dibatalkan oleh operasional.</p>}
            </>}
          </Card>
        </div>
      </div>
    </DashboardLayout>
  );
}
