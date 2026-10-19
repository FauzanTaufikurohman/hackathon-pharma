"use client";

import { useCallback, useEffect, useState } from "react";
import { DashboardLayout } from "@/components/layouts/dashboard-layout";
import { api, errorMessage, listFrom, type CustomerAccount } from "@/lib/api";
import { Card, ErrorState, Loading, PageTitle } from "@/lib/ui";

export default function CustomersPage() {
  const [customers, setCustomers] = useState<CustomerAccount[]>([]);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    try {
      const response = await api.get("/users/customers");
      setCustomers(listFrom<CustomerAccount>(response.data));
      setError("");
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { void Promise.resolve().then(load); }, [load]);

  return (
    <DashboardLayout roles={["admin"]}>
      <div className="space-y-5">
        <PageTitle title="Customer" description="Daftar akun pengguna dengan role customer beserta profil perusahaan dan kontak WhatsApp." />
        {error && <ErrorState message={error} />}
        <Card>
          {loading ? <Loading /> : customers.length === 0 ? (
            <p className="py-6 text-sm text-slate-500">Belum ada akun dengan role customer.</p>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full min-w-[850px] text-left text-sm">
                <thead><tr className="border-b text-xs uppercase text-slate-500"><th className="py-3">Akun customer</th><th>Perusahaan</th><th>Tipe</th><th>Nomor WhatsApp</th><th>Persetujuan notifikasi</th><th>Status akun</th></tr></thead>
                <tbody>
                  {customers.map((user) => (
                    <tr key={user.id} className="border-b last:border-0">
                      <td className="py-3"><strong>{user.name}</strong><p className="text-xs text-slate-500">{user.email}</p></td>
                      <td>{user.customer?.company_name ?? "Profil perusahaan belum ditautkan"}<p className="text-xs text-slate-500">{user.customer?.contact_name ?? ""}</p></td>
                      <td>{companyTypeLabel(user.customer?.company_type)}</td>
                      <td>{user.customer?.whatsapp_number ?? "Belum diisi"}</td>
                      <td>{user.customer?.whatsapp_opt_in ? "Opt-in" : "Belum opt-in"}</td>
                      <td>{user.is_active ? "Aktif" : "Nonaktif"}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </Card>
      </div>
    </DashboardLayout>
  );
}

function companyTypeLabel(type?: string) {
  if (type === "manufacturer") return "Produsen";
  if (type === "distributor") return "Distributor";
  if (type === "pharmacy") return "Apotek";
  return "-";
}
