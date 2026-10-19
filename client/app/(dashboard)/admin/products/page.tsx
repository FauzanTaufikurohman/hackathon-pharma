"use client";

import { useCallback, useEffect, useState } from "react";
import { DashboardLayout } from "@/components/layouts/dashboard-layout";
import { api, errorMessage, listFrom, type Product } from "@/lib/api";
import { buttonClass, Card, ErrorState, inputClass, Loading, Notice, PageTitle } from "@/lib/ui";

const emptyForm = {
  sku: "", name: "", category: "", min_temperature: "2", max_temperature: "8",
  max_excursion_seconds: "", temperature_protocol: "", protocol_reference: "",
};

function generateSku(name: string) {
  const slug = name.normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toUpperCase()
    .replace(/[^A-Z0-9]+/g, "-")
    .replace(/^-|-$/g, "");
  return slug ? `PH-${slug}`.slice(0, 100) : "";
}

export default function ProductsPage() {
  const [products, setProducts] = useState<Product[]>([]);
  const [form, setForm] = useState(emptyForm);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [error, setError] = useState("");
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);

  const load = useCallback(async () => {
    try {
      const response = await api.get("/products", { params: { per_page: 100 } });
      setProducts(listFrom<Product>(response.data));
      setError("");
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setLoading(false);
    }
  }, []);
  useEffect(() => { void Promise.resolve().then(load); }, [load]);

  function edit(product: Product) {
    setEditingId(product.id);
    setForm({
      sku: product.sku,
      name: product.name,
      category: product.category ?? "",
      min_temperature: String(product.min_temperature),
      max_temperature: String(product.max_temperature),
      max_excursion_seconds: product.max_excursion_seconds ? String(product.max_excursion_seconds) : "",
      temperature_protocol: product.temperature_protocol ?? "",
      protocol_reference: product.protocol_reference ?? "",
    });
  }

  async function submit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setBusy(true);
    setError("");
    setMessage("");
    const payload = {
      ...form,
      min_temperature: Number(form.min_temperature),
      max_temperature: Number(form.max_temperature),
      max_excursion_seconds: form.max_excursion_seconds ? Number(form.max_excursion_seconds) : null,
    };
    try {
      const response = editingId
        ? await api.put(`/products/${editingId}`, payload)
        : await api.post("/products", payload);
      setMessage(response.data.message);
      setEditingId(null);
      setForm(emptyForm);
      await load();
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setBusy(false);
    }
  }

  async function toggle(product: Product) {
    setBusy(true);
    try {
      await api.patch(`/products/${product.id}/status`, { is_active: !product.is_active });
      await load();
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setBusy(false);
    }
  }

  return (
    <DashboardLayout roles={["admin"]}>
      <div className="space-y-5">
        <PageTitle title="Produk pharma" description="Kelola katalog dan batas suhu sesuai protokol yang tervalidasi." />
        {error && <ErrorState message={error} />}{message && <Notice tone="success">{message}</Notice>}
        <Card>
          <h2 className="mb-4 font-semibold">{editingId ? "Edit produk" : "Tambah produk"}</h2>
          <form onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
            <label className="text-sm">Nama produk<input required className={inputClass} value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value, sku: generateSku(e.target.value) })} /></label>
            <label className="text-sm">SKU otomatis<input readOnly required className={`${inputClass} bg-slate-50`} value={form.sku} /><span className="mt-1 block text-xs text-slate-500">Dibuat dari nama produk dengan format PH-NAMA-PRODUK.</span></label>
            <label className="text-sm">Kategori<input className={inputClass} value={form.category} onChange={(e) => setForm({ ...form, category: e.target.value })} /></label>
            <label className="text-sm">Referensi protokol<input className={inputClass} value={form.protocol_reference} onChange={(e) => setForm({ ...form, protocol_reference: e.target.value })} /></label>
            <label className="text-sm">Suhu minimum (°C)<input type="number" step="0.01" required className={inputClass} value={form.min_temperature} onChange={(e) => setForm({ ...form, min_temperature: e.target.value })} /></label>
            <label className="text-sm">Suhu maksimum (°C)<input type="number" step="0.01" required className={inputClass} value={form.max_temperature} onChange={(e) => setForm({ ...form, max_temperature: e.target.value })} /></label>
            <label className="text-sm">Paparan maks. (detik, opsional)<input type="number" min="1" className={inputClass} value={form.max_excursion_seconds} onChange={(e) => setForm({ ...form, max_excursion_seconds: e.target.value })} /></label>
            <label className="text-sm">Protokol suhu<textarea className={inputClass} value={form.temperature_protocol} onChange={(e) => setForm({ ...form, temperature_protocol: e.target.value })} /></label>
            <div className="flex gap-2 sm:col-span-2"><button className={buttonClass} disabled={busy}>{busy ? "Menyimpan..." : editingId ? "Simpan perubahan" : "Tambah produk"}</button>{editingId && <button type="button" className="rounded-xl border px-4 py-2.5 text-sm" onClick={() => { setEditingId(null); setForm(emptyForm); }}>Batal</button>}</div>
          </form>
        </Card>
        <Card><h2 className="mb-4 font-semibold">Daftar produk</h2>{loading ? <Loading /> : <div className="overflow-x-auto"><table className="w-full min-w-[700px] text-left text-sm"><thead><tr className="border-b text-xs uppercase text-slate-500"><th className="py-3">Produk</th><th>Kategori</th><th>Rentang suhu</th><th>Durasi maks.</th><th>Status</th><th /></tr></thead><tbody>{products.map((product) => <tr key={product.id} className="border-b last:border-0"><td className="py-3"><strong>{product.name}</strong><p className="text-xs text-slate-500">{product.sku}</p></td><td>{product.category ?? "-"}</td><td>{product.min_temperature}–{product.max_temperature}°C</td><td>{product.max_excursion_seconds ?? "Tidak ditentukan"}</td><td>{product.is_active ? "Aktif" : "Nonaktif"}</td><td className="space-x-3 whitespace-nowrap"><button className="text-blue-700" onClick={() => edit(product)}>Edit</button><button disabled={busy} className="text-blue-700" onClick={() => void toggle(product)}>{product.is_active ? "Nonaktifkan" : "Aktifkan"}</button></td></tr>)}</tbody></table>{products.length === 0 && <p className="py-6 text-sm text-slate-500">Belum ada produk.</p>}</div>}</Card>
      </div>
    </DashboardLayout>
  );
}
