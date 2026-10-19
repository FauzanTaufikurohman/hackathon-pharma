"use client";

import Link from "next/link";
import { useCallback, useEffect, useState } from "react";
import { Search } from "lucide-react";
import { DashboardLayout } from "@/components/layouts/dashboard-layout";
import { api, errorMessage, type PageResult, type Shipment } from "@/lib/api";
import { Card, ErrorState, Loading, PageTitle, Pager, StatusBadge, formatDate, inputClass } from "@/lib/ui";

const statuses = ["pending", "approved", "rejected", "checker_assigned", "in_transit", "delivered", "cancelled"];

export default function ShipmentsPage() {
  const [result, setResult] = useState<PageResult<Shipment>>({ data: [], current_page: 1, last_page: 1, per_page: 10, total: 0 });
  const [search, setSearch] = useState("");
  const [status, setStatus] = useState("");
  const [page, setPage] = useState(1);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    setError("");
    try {
      const response = await api.get("/shipments", {
        params: { page, per_page: 10, search: search || undefined, status: status || undefined },
      });
      const payload = response.data;
      const shipments: Shipment[] = payload.data ?? [];
      const meta = payload.meta ?? {};
      setResult({
        data: shipments,
        current_page: meta.current_page ?? page,
        last_page: meta.last_page ?? 1,
        per_page: meta.per_page ?? 10,
        total: meta.total ?? shipments.length,
      });
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setLoading(false);
    }
  }, [page, search, status]);

  useEffect(() => { void Promise.resolve().then(load); }, [load]);

  return (
    <DashboardLayout roles={["admin"]}>
      <div className="space-y-5">
        <PageTitle title="Shipments" description={`${result.total} shipment tercatat`} action={<Link href="/admin/shipments/create" className="rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white">+ Shipment baru</Link>} />
        <Card>
          <div className="mb-5 grid gap-3 sm:grid-cols-[1fr_220px]">
            <label className="relative"><Search size={17} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" /><input className={`${inputClass} mt-0 pl-10`} placeholder="Cari nomor shipment" value={search} onChange={(event) => { setPage(1); setSearch(event.target.value); }} /></label>
            <select className={`${inputClass} mt-0`} value={status} onChange={(event) => { setPage(1); setStatus(event.target.value); }}><option value="">Semua status</option>{statuses.map((value) => <option key={value} value={value}>{value.replaceAll("_", " ")}</option>)}</select>
          </div>
          {error && <ErrorState message={error} />}
          {loading ? <Loading /> : result.data.length === 0 ? <p className="py-8 text-center text-sm text-slate-500">Tidak ada data shipment.</p> : (
            <div className="overflow-x-auto"><table className="w-full min-w-[950px] text-left text-sm"><thead><tr className="border-b text-xs uppercase text-slate-500"><th className="py-3">Shipment</th><th>Produk</th><th>Produsen</th><th>Distributor / apotek</th><th>Rute</th><th>Kurir</th><th>Status</th><th>Dibuat</th><th /></tr></thead><tbody>{result.data.map((shipment) => <tr key={shipment.id} className="border-b last:border-0"><td className="py-3 font-semibold">{shipment.shipment_number}</td><td>{shipment.product?.name ?? "-"}</td><td>{shipment.origin_customer_user?.customer?.company_name ?? shipment.customer?.company_name ?? "-"}</td><td>{shipment.destination_customer_user?.customer?.company_name ?? "-"}</td><td>{shipment.origin_address} → {shipment.destination_address}</td><td>{shipment.courier?.name ?? "Belum ditugaskan"}</td><td><StatusBadge status={shipment.status} /></td><td>{formatDate(shipment.created_at)}</td><td><Link href={`/admin/shipments/${shipment.id}`} className="font-medium text-blue-700 hover:underline">Detail</Link></td></tr>)}</tbody></table></div>
          )}
          {!loading && result.total > 0 && <div className="mt-4"><Pager page={result.current_page} lastPage={result.last_page} onChange={setPage} /></div>}
        </Card>
      </div>
    </DashboardLayout>
  );
}
