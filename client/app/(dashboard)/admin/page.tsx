"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { Activity, AlertTriangle, Package, Truck } from "lucide-react";
import { DashboardLayout } from "@/components/layouts/dashboard-layout";
import { api, errorMessage, listFrom, type Shipment } from "@/lib/api";
import { Card, ErrorState, Loading, PageTitle, StatusBadge, formatDate } from "@/lib/ui";

type Summary = {
  total_shipments: number;
  shipments_by_status: Record<string, number>;
  total_thermal_incidents: number;
  open_thermal_incidents: number;
  incidents_by_severity: Record<string, number>;
};

export default function AdminDashboardPage() {
  const [summary, setSummary] = useState<Summary | null>(null);
  const [shipments, setShipments] = useState<Shipment[]>([]);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let active = true;
    Promise.all([api.get("/reports/summary"), api.get("/reports/shipments", { params: { per_page: 5 } })])
      .then(([summaryResponse, shipmentsResponse]) => {
        if (!active) return;
        setSummary(summaryResponse.data.data);
        setShipments(listFrom<Shipment>(shipmentsResponse.data));
      })
      .catch((requestError: unknown) => active && setError(errorMessage(requestError)))
      .finally(() => active && setLoading(false));
    return () => { active = false; };
  }, []);

  return (
    <DashboardLayout roles={["admin"]}>
      <div className="space-y-6">
        <PageTitle title="Operations dashboard" description="Ringkasan shipment dan insiden termal." action={<Link href="/admin/shipments/create" className="rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white">+ Shipment baru</Link>} />
        {error && <ErrorState message={error} />}
        {loading ? <Loading /> : summary && (
          <>
            <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
              {([
                { label: "Total shipment", value: summary.total_shipments, Icon: Package },
                { label: "Dalam perjalanan", value: summary.shipments_by_status.in_transit ?? 0, Icon: Truck },
                { label: "Insiden termal terbuka", value: summary.open_thermal_incidents, Icon: AlertTriangle },
                { label: "Total insiden termal", value: summary.total_thermal_incidents, Icon: Activity },
              ]).map(({ label, value, Icon }) => (
                <Card key={label}><div className="flex items-center justify-between text-sm text-slate-500"><span>{label}</span><Icon size={19} className="text-blue-700" /></div><p className="mt-4 text-3xl font-bold text-slate-900">{value}</p></Card>
              ))}
            </section>
            <Card>
              <div className="mb-4 flex items-center justify-between"><div><h2 className="font-semibold text-slate-900">Shipment terbaru</h2><p className="mt-1 text-sm text-slate-500">Data langsung dari API operasional</p></div><Link href="/admin/shipments" className="text-sm font-medium text-blue-700">Lihat semua</Link></div>
              {shipments.length === 0 ? <p className="py-5 text-sm text-slate-500">Belum ada shipment.</p> : <div className="overflow-x-auto"><table className="w-full min-w-[650px] text-left text-sm"><thead><tr className="border-b text-xs uppercase text-slate-500"><th className="py-3">Nomor</th><th>Produk</th><th>Customer</th><th>Status</th><th>Dibuat</th></tr></thead><tbody>{shipments.map((shipment) => <tr key={shipment.id} className="border-b last:border-0"><td className="py-3"><Link href={`/admin/shipments/${shipment.id}`} className="font-semibold text-blue-700">{shipment.shipment_number}</Link></td><td>{shipment.product?.name ?? "-"}</td><td>{shipment.customer?.company_name ?? "-"}</td><td><StatusBadge status={shipment.status} /></td><td>{formatDate(shipment.created_at)}</td></tr>)}</tbody></table></div>}
            </Card>
          </>
        )}
      </div>
    </DashboardLayout>
  );
}
