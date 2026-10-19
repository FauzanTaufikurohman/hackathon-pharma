"use client";

import Link from "next/link";
import { Suspense, useCallback, useEffect, useState } from "react";
import { useParams } from "next/navigation";
import { Download, MapPin } from "lucide-react";
import { DashboardLayout } from "@/components/layouts/dashboard-layout";
import { TrackingMap } from "@/components/tracking-map";
import { api, errorMessage, listFrom, type AuthUser, type CustomerAccount, type Product, type Shipment } from "@/lib/api";
import { buttonClass, Card, ErrorState, formatDate, inputClass, Loading, Notice, PageTitle, StatusBadge } from "@/lib/ui";

export default function ShipmentDetailPage() {
  return (
    <Suspense fallback={<main className="grid min-h-screen place-items-center text-sm text-slate-500">Memuat detail shipment...</main>}>
      <ShipmentDetailContent />
    </Suspense>
  );
}

function ShipmentDetailContent() {
  const { id } = useParams<{ id: string }>();
  const [shipment, setShipment] = useState<Shipment | null>(null);
  const [checkers, setCheckers] = useState<AuthUser[]>([]);
  const [couriers, setCouriers] = useState<AuthUser[]>([]);
  const [checkerId, setCheckerId] = useState("");
  const [courierId, setCourierId] = useState("");
  const [reason, setReason] = useState("");
  const [customers, setCustomers] = useState<CustomerAccount[]>([]);
  const [products, setProducts] = useState<Product[]>([]);
  const [editForm, setEditForm] = useState({ origin_customer_user_id: "", destination_customer_user_id: "", product_id: "", quantity: "", origin_address: "", destination_address: "", notes: "" });
  const [error, setError] = useState("");
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);

  const load = useCallback(async () => {
    try {
      const response = await api.get(`/shipments/${id}`);
      const item = response.data.data as Shipment;
      setShipment(item);
      setEditForm({
        origin_customer_user_id: String(item.origin_customer_user_id ?? ""),
        destination_customer_user_id: String(item.destination_customer_user_id ?? ""),
        product_id: String(item.product_id),
        quantity: String(item.quantity),
        origin_address: item.origin_address,
        destination_address: item.destination_address,
        notes: item.notes ?? "",
      });
      setError("");
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setLoading(false);
    }
  }, [id]);

  useEffect(() => {
    void Promise.resolve().then(load);
    const interval = window.setInterval(() => void load(), 30000);
    return () => window.clearInterval(interval);
  }, [load]);

  useEffect(() => {
    Promise.all([
      api.get("/users/assignable", { params: { role: "checker" } }),
      api.get("/users/assignable", { params: { role: "courier" } }),
      api.get("/users/customers", { params: { active: true } }),
      api.get("/products", { params: { per_page: 100 } }),
    ]).then(([checkerResponse, courierResponse, customerResponse, productResponse]) => {
      const checkerList = listFrom<AuthUser>(checkerResponse.data);
      const courierList = listFrom<AuthUser>(courierResponse.data);
      setCheckers(checkerList);
      setCouriers(courierList);
      setCheckerId(checkerList[0] ? String(checkerList[0].id) : "");
      setCourierId(courierList[0] ? String(courierList[0].id) : "");
      setCustomers(listFrom<CustomerAccount>(customerResponse.data));
      setProducts(listFrom<Product>(productResponse.data));
    }).catch((requestError: unknown) => setError(errorMessage(requestError)));
  }, []);

  async function mutate(path: string, body: Record<string, unknown> = {}) {
    setBusy(true);
    setError("");
    setMessage("");
    try {
      const response = await api.post(path, body);
      setMessage(response.data.message ?? "Perubahan berhasil disimpan.");
      await load();
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setBusy(false);
    }
  }

  async function editShipment(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setBusy(true);
    setError("");
    try {
      const response = await api.put(`/shipments/${id}`, {
        origin_customer_user_id: Number(editForm.origin_customer_user_id),
        destination_customer_user_id: Number(editForm.destination_customer_user_id),
        product_id: Number(editForm.product_id),
        quantity: Number(editForm.quantity),
        origin_address: editForm.origin_address,
        destination_address: editForm.destination_address,
        notes: editForm.notes || null,
      });
      setMessage(response.data.message);
      await load();
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setBusy(false);
    }
  }

  async function downloadPdf() {
    setError("");
    try {
      const response = await api.get(`/reports/shipments/${id}/pdf`, { responseType: "blob" });
      const url = URL.createObjectURL(response.data as Blob);
      const anchor = document.createElement("a");
      anchor.href = url;
      anchor.download = `shipment-${shipment?.shipment_number ?? id}.pdf`;
      anchor.click();
      URL.revokeObjectURL(url);
    } catch (requestError) {
      setError(errorMessage(requestError));
    }
  }

  const shipmentId = shipment?.id;
  const canAssignCourier = shipment?.status === "checker_assigned";

  return (
    <DashboardLayout roles={["admin"]}>
      <div className="space-y-5">
        <Link href="/admin/shipments" className="text-sm font-medium text-blue-700">← Kembali ke daftar shipment</Link>
        {error && <ErrorState message={error} />}
        {message && <Notice tone="success">{message}</Notice>}
        {loading ? <Loading /> : !shipment ? null : (
          <>
            <PageTitle title={shipment.shipment_number} description={`Dibuat ${formatDate(shipment.created_at)}`} action={<StatusBadge status={shipment.status} />} />
            <div className="grid gap-5 xl:grid-cols-[1.3fr_0.7fr]">
              <div className="space-y-5">
                <Card>
                  <h2 className="mb-4 font-semibold">Detail shipment</h2>
                  <dl className="grid gap-4 text-sm sm:grid-cols-2">
                    <Info label="Produsen / pengirim" value={`${shipment.origin_customer_user?.customer?.company_name ?? shipment.customer?.company_name ?? "-"} · ${shipment.origin_customer_user?.customer?.whatsapp_number ?? "WA belum diisi"}`} />
                    <Info label="Distributor / apotek" value={`${shipment.destination_customer_user?.customer?.company_name ?? "-"} · ${shipment.destination_customer_user?.customer?.whatsapp_number ?? "WA belum diisi"}`} />
                    <Info label="Produk" value={`${shipment.product?.sku ?? ""} ${shipment.product?.name ?? ""}`} />
                    <Info label="Jumlah" value={String(shipment.quantity)} />
                    <Info label="Rentang suhu" value={`${shipment.product?.min_temperature ?? "?"}–${shipment.product?.max_temperature ?? "?"}°C`} />
                    <Info label="Alamat asal" value={shipment.origin_address} />
                    <Info label="Alamat tujuan" value={shipment.destination_address} />
                    <Info label="Checker" value={shipment.checker?.name ?? "Belum ditugaskan"} />
                    <Info label="Kurir" value={shipment.courier?.name ?? "Belum ditugaskan"} />
                    <Info label="Mulai" value={formatDate(shipment.started_at)} />
                    <Info label="Selesai" value={formatDate(shipment.delivered_at ?? shipment.cancelled_at)} />
                  </dl>
                </Card>
                {shipment.status === "in_transit" && (
                  <Card>
                    <h2 className="mb-3 font-semibold">Peta tracking kurir</h2>
                    {shipment.tracking_locations?.[0]
                      ? <TrackingMap location={shipment.tracking_locations[0]} />
                      : <p className="text-sm text-slate-500">Menunggu lokasi GPS kurir.</p>}
                  </Card>
                )}

                {shipment.status === "pending" && (
                  <Card>
                    <h2 className="mb-4 font-semibold">Edit shipment (status pending)</h2>
                    <form onSubmit={editShipment} className="grid gap-3 sm:grid-cols-2">
                      <select required className={inputClass} value={editForm.origin_customer_user_id} onChange={(e) => setEditForm({ ...editForm, origin_customer_user_id: e.target.value })}><option value="">Pilih produsen</option>{customers.filter((user) => user.customer?.company_type === "manufacturer").map((user) => <option key={user.id} value={user.id}>{user.customer?.company_name} — {user.name}</option>)}</select>
                      <select required className={inputClass} value={editForm.destination_customer_user_id} onChange={(e) => setEditForm({ ...editForm, destination_customer_user_id: e.target.value })}><option value="">Pilih distributor/apotek</option>{customers.filter((user) => user.customer?.company_type === "distributor" || user.customer?.company_type === "pharmacy").map((user) => <option key={user.id} value={user.id}>{user.customer?.company_name} — {user.name}</option>)}</select>
                      <select required className={inputClass} value={editForm.product_id} onChange={(e) => setEditForm({ ...editForm, product_id: e.target.value })}>{products.map((p) => <option key={p.id} value={p.id}>{p.sku} — {p.name}</option>)}</select>
                      <input required type="number" min="1" className={inputClass} value={editForm.quantity} onChange={(e) => setEditForm({ ...editForm, quantity: e.target.value })} />
                      <input required className={inputClass} placeholder="Alamat asal" value={editForm.origin_address} onChange={(e) => setEditForm({ ...editForm, origin_address: e.target.value })} />
                      <input required className={inputClass} placeholder="Alamat tujuan" value={editForm.destination_address} onChange={(e) => setEditForm({ ...editForm, destination_address: e.target.value })} />
                      <textarea className={inputClass} placeholder="Catatan" value={editForm.notes} onChange={(e) => setEditForm({ ...editForm, notes: e.target.value })} />
                      <button className={buttonClass} disabled={busy}>Simpan perubahan</button>
                    </form>
                  </Card>
                )}

                <Card>
                  <h2 className="mb-4 font-semibold">Pemeriksaan suhu & analisis AI</h2>
                  {shipment.thermal_checks?.length ? shipment.thermal_checks.map((check) => (
                    <div key={check.id} className="border-b py-3 last:border-0">
                      <p className="text-sm font-medium">{check.temperature_celsius}°C · kemasan {check.packaging_condition} · fisik {check.physical_condition}</p>
                      <p className="mt-1 text-xs text-slate-500">{formatDate(check.checked_at)} · AI: {check.ai_assessment_status ?? "pending"}</p>
                      {check.ai_assessment && <p className="mt-2 text-sm">{check.ai_assessment.risk_level}: {check.ai_assessment.summary}</p>}
                    </div>
                  )) : <p className="text-sm text-slate-500">Belum ada pemeriksaan.</p>}
                  <h3 className="mb-2 mt-5 text-sm font-semibold">Insiden termal</h3>
                  {shipment.thermal_incidents?.length ? shipment.thermal_incidents.map((incident) => <div key={incident.id} className="border-t py-3"><div className="flex justify-between"><span className="text-sm">{incident.temperature_celsius}°C · {incident.exposure_duration_seconds ?? "durasi tidak diketahui"} detik</span><StatusBadge status={incident.incident_status} /></div><p className="mt-1 text-xs text-slate-500">{incident.description} · AI: {incident.ai_assessment_status ?? "pending"}</p>{incident.ai_assessment && <p className="mt-1 text-sm">{incident.ai_assessment.summary}</p>}</div>) : <p className="text-sm text-slate-500">Tidak ada insiden.</p>}
                </Card>

                <Card>
                  <h2 className="mb-4 font-semibold">Riwayat shipment</h2>
                  {shipment.events?.length ? shipment.events.map((event) => <div key={event.id} className="flex gap-3 border-l-2 border-blue-200 py-2 pl-4"><div><p className="text-sm font-medium">{event.event_type.replaceAll("_", " ")}</p><p className="text-xs text-slate-500">{event.description} · {formatDate(event.occurred_at)}</p></div></div>) : <p className="text-sm text-slate-500">Belum ada riwayat.</p>}
                  {shipment.tracking_locations?.[0] && <p className="mt-4 text-sm"><MapPin className="mr-1 inline" size={16} />Koordinat terakhir {shipment.tracking_locations[0].latitude}, {shipment.tracking_locations[0].longitude} {shipment.tracking_locations[0].is_simulated ? "(simulasi)" : ""} · <a className="text-blue-700 underline" href={`https://www.openstreetmap.org/?mlat=${shipment.tracking_locations[0].latitude}&mlon=${shipment.tracking_locations[0].longitude}#map=14/${shipment.tracking_locations[0].latitude}/${shipment.tracking_locations[0].longitude}`} target="_blank" rel="noreferrer">Lihat peta</a></p>}
                </Card>
              </div>

              <div className="space-y-5">
                <Card>
                  <h2 className="mb-4 font-semibold">Tindakan admin</h2>
                  <div className="space-y-3">
                    {shipment.status === "pending" && <><button className={buttonClass} disabled={busy} onClick={() => void mutate(`/shipments/${shipmentId}/approve`)}>Setujui shipment</button><textarea className={inputClass} placeholder="Alasan penolakan" value={reason} onChange={(e) => setReason(e.target.value)} /><button className="rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-700" disabled={busy || reason.trim().length < 5} onClick={() => void mutate(`/shipments/${shipmentId}/reject`, { reason })}>Tolak shipment</button></>}
                    {shipment.status === "approved" && <><label className="block text-sm">Tugaskan checker<select className={inputClass} value={checkerId} onChange={(e) => setCheckerId(e.target.value)}><option value="">Pilih checker</option>{checkers.map((u) => <option key={u.id} value={u.id}>{u.name} · {u.email}</option>)}</select></label><button className={buttonClass} disabled={busy || !checkerId} onClick={() => void mutate(`/shipments/${shipmentId}/assign-checker`, { checker_id: Number(checkerId) })}>Tugaskan checker</button></>}
                    {canAssignCourier && <><label className="block text-sm">Tugaskan kurir (akun yang sedang free)<select className={inputClass} value={courierId} onChange={(e) => setCourierId(e.target.value)}><option value="">Pilih kurir</option>{couriers.map((u) => <option key={u.id} value={u.id}>{u.name} · {u.email}</option>)}</select></label><button className={buttonClass} disabled={busy || !courierId} onClick={() => void mutate(`/shipments/${shipmentId}/assign-courier`, { courier_id: Number(courierId) })}>Tugaskan kurir</button></>}
                    {!["delivered", "cancelled", "rejected"].includes(shipment.status) && <><textarea className={inputClass} placeholder="Alasan pembatalan" value={reason} onChange={(e) => setReason(e.target.value)} /><button className="rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-700" disabled={busy || reason.trim().length < 5} onClick={() => void mutate(`/shipments/${shipmentId}/cancel`, { reason })}>Batalkan shipment</button></>}
                    {["delivered", "cancelled"].includes(shipment.status) && <button className="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold" onClick={() => void downloadPdf()}><Download size={16} />Unduh laporan PDF</button>}
                  </div>
                </Card>
                {shipment.delivery_proofs?.length ? <Card><h2 className="mb-3 font-semibold">Bukti serah terima</h2>{shipment.delivery_proofs.map((proof) => <div key={proof.id} className="text-sm"><p>{proof.recipient_name} · {formatDate(proof.delivered_at)}</p><p className="mt-1 text-slate-500">{proof.notes}</p></div>)}</Card> : null}
              </div>
            </div>
          </>
        )}
      </div>
    </DashboardLayout>
  );
}

function Info({ label, value }: { label: string; value?: string }) {
  return <div><dt className="text-xs uppercase text-slate-500">{label}</dt><dd className="mt-1 font-medium text-slate-900">{value || "-"}</dd></div>;
}
