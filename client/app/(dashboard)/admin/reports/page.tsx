"use client";

import { useCallback, useEffect, useState } from "react";
import { Download } from "lucide-react";
import { DashboardLayout } from "@/components/layouts/dashboard-layout";
import { api, errorMessage, type PageResult, type Shipment } from "@/lib/api";
import { Card, ErrorState, formatDate, inputClass, Loading, PageTitle, Pager, StatusBadge } from "@/lib/ui";

const statuses = ["pending", "approved", "rejected", "checker_assigned", "in_transit", "delivered", "cancelled"];

type ReportSummary = {
  total_shipments: number;
  shipments_by_status: Record<string, number>;
  total_thermal_incidents: number;
  open_thermal_incidents: number;
};

export default function ReportsPage() {
  const [shipments, setShipments] = useState<PageResult<Shipment>>({ data: [], current_page: 1, last_page: 1, per_page: 20, total: 0 });
  const [summary, setSummary] = useState<ReportSummary | null>(null);
  const [status, setStatus] = useState("");
  const [from, setFrom] = useState("");
  const [to, setTo] = useState("");
  const [page, setPage] = useState(1);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    setError("");
    const params = { status: status || undefined, from: from || undefined, to: to || undefined, page };
    try {
      const [summaryResponse, shipmentResponse] = await Promise.all([
        api.get("/reports/summary", { params }),
        api.get("/reports/shipments", { params }),
      ]);
      setSummary(summaryResponse.data.data);
      setShipments(shipmentResponse.data);
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setLoading(false);
    }
  }, [from, page, status, to]);
  useEffect(() => { void Promise.resolve().then(load); }, [load]);

  async function download(id: number, name: string) {
    try {
      const response = await api.get(`/reports/shipments/${id}/pdf`, { responseType: "blob" });
      const url = URL.createObjectURL(response.data as Blob);
      const anchor = document.createElement("a");
      anchor.href = url;
      anchor.download = `shipment-${name}.pdf`;
      anchor.click();
      URL.revokeObjectURL(url);
    } catch (requestError) {
      setError(errorMessage(requestError));
    }
  }

  function changeFilter(callback: (value: string) => void, value: string) {
    setPage(1);
    callback(value);
  }

  return (
    <DashboardLayout roles={["admin"]}>
      <div className="space-y-5">
        <PageTitle title="Laporan shipment" description="Ringkasan dan laporan PDF berdasarkan data API." />
        {error && <ErrorState message={error} />}
        <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
          {([
            ["Total shipment", summary?.total_shipments ?? 0],
            ["Dalam perjalanan", summary?.shipments_by_status.in_transit ?? 0],
            ["Insiden termal", summary?.total_thermal_incidents ?? 0],
            ["Insiden terbuka", summary?.open_thermal_incidents ?? 0],
          ] as Array<[string, number]>).map(([label, value]) => <Card key={label}><p className="text-sm text-slate-500">{label}</p><p className="mt-2 text-2xl font-bold">{value}</p></Card>)}
        </div>
        <Card>
          <div className="mb-5 grid gap-3 sm:grid-cols-3">
            <label className="text-sm">Status<select className={inputClass} value={status} onChange={(e) => changeFilter(setStatus, e.target.value)}><option value="">Semua status</option>{statuses.map((item) => <option key={item} value={item}>{item.replaceAll("_", " ")}</option>)}</select></label>
            <label className="text-sm">Dari tanggal<input className={inputClass} type="date" value={from} onChange={(e) => changeFilter(setFrom, e.target.value)} /></label>
            <label className="text-sm">Sampai tanggal<input className={inputClass} type="date" value={to} onChange={(e) => changeFilter(setTo, e.target.value)} /></label>
          </div>
          {loading ? <Loading /> : <div className="overflow-x-auto"><table className="w-full min-w-[750px] text-left text-sm"><thead><tr className="border-b text-xs uppercase text-slate-500"><th className="py-3">Nomor</th><th>Customer</th><th>Produk</th><th>Status</th><th>Dibuat</th><th>Laporan</th></tr></thead><tbody>{shipments.data.map((shipment) => <tr key={shipment.id} className="border-b last:border-0"><td className="py-3 font-semibold">{shipment.shipment_number}</td><td>{shipment.customer?.company_name ?? "-"}</td><td>{shipment.product?.name ?? "-"}</td><td><StatusBadge status={shipment.status} /></td><td>{formatDate(shipment.created_at)}</td><td><button className="inline-flex items-center gap-1 text-blue-700" onClick={() => void download(shipment.id, shipment.shipment_number)}><Download size={15} />PDF</button></td></tr>)}</tbody></table>{shipments.data.length === 0 && <p className="py-6 text-sm text-slate-500">Tidak ada shipment sesuai filter.</p>}</div>}
          {!loading && shipments.total > 0 && <div className="mt-4"><Pager page={shipments.current_page} lastPage={shipments.last_page} onChange={setPage} /></div>}
        </Card>
      </div>
    </DashboardLayout>
  );
}
