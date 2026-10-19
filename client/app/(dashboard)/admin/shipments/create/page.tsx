"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { ArrowLeft } from "lucide-react";
import { DashboardLayout } from "@/components/layouts/dashboard-layout";
import { api, errorMessage, listFrom, type CustomerAccount, type Product } from "@/lib/api";
import { buttonClass, Card, ErrorState, inputClass, PageTitle } from "@/lib/ui";

export default function CreateShipmentPage() {
  const router = useRouter();
  const [customers, setCustomers] = useState<CustomerAccount[]>([]);
  const [products, setProducts] = useState<Product[]>([]);
  const [originCustomerUserId, setOriginCustomerUserId] = useState("");
  const [destinationCustomerUserId, setDestinationCustomerUserId] = useState("");
  const [productId, setProductId] = useState("");
  const [quantity, setQuantity] = useState("1");
  const [origin, setOrigin] = useState("");
  const [destination, setDestination] = useState("");
  const [scheduledAt, setScheduledAt] = useState("");
  const [notes, setNotes] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    Promise.all([
      api.get("/users/customers", { params: { active: true } }),
      api.get("/products", { params: { per_page: 100, status: "active" } }),
    ]).then(([customerResponse, productResponse]) => {
      const customerItems = listFrom<CustomerAccount>(customerResponse.data);
      const productItems = listFrom<Product>(productResponse.data);
      setCustomers(customerItems);
      setProducts(productItems);
      const producer = customerItems.find((user) => user.customer?.company_type === "manufacturer");
      const recipient = customerItems.find((user) =>
        user.customer?.company_type === "distributor" || user.customer?.company_type === "pharmacy"
      );
      setOriginCustomerUserId(producer ? String(producer.id) : "");
      setDestinationCustomerUserId(recipient ? String(recipient.id) : "");
      if (producer?.customer?.address) setOrigin(producer.customer.address);
      if (recipient?.customer?.address) setDestination(recipient.customer.address);
      setProductId(productItems[0] ? String(productItems[0].id) : "");
    }).catch((requestError: unknown) => setError(errorMessage(requestError)));
  }, []);

  async function submit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError("");
    setLoading(true);
    try {
      const response = await api.post("/shipments", {
        origin_customer_user_id: Number(originCustomerUserId),
        destination_customer_user_id: Number(destinationCustomerUserId),
        product_id: Number(productId),
        quantity: Number(quantity),
        origin_address: origin,
        destination_address: destination,
        scheduled_at: scheduledAt || null,
        notes: notes || null,
      });
      router.push(`/admin/shipments/${response.data.data.id}`);
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setLoading(false);
    }
  }

  const producers = customers.filter((user) => user.customer?.company_type === "manufacturer");
  const recipients = customers.filter((user) =>
    user.customer?.company_type === "distributor" || user.customer?.company_type === "pharmacy"
  );

  return (
    <DashboardLayout roles={["admin"]}>
      <div className="mx-auto max-w-3xl space-y-5">
        <Link href="/admin/shipments" className="inline-flex items-center gap-2 text-sm text-slate-500"><ArrowLeft size={16} />Kembali ke shipment</Link>
        <PageTitle title="Buat shipment" description="Tentukan alur pengiriman dari akun produsen ke akun distributor atau apotek." />
        {error && <ErrorState message={error} />}
        <Card>
          {producers.length === 0 || recipients.length === 0 || products.length === 0 ? (
            <div className="space-y-3 text-sm text-slate-600">
              <p>Shipment memerlukan akun customer produsen, akun customer distributor/apotek, dan produk aktif.</p>
              {(producers.length === 0 || recipients.length === 0) && <Link className="block text-blue-700 underline" href="/admin/customers">Lihat akun customer</Link>}
              {products.length === 0 && <Link className="block text-blue-700 underline" href="/admin/products">Tambahkan produk</Link>}
            </div>
          ) : (
            <form onSubmit={submit} className="grid gap-5 sm:grid-cols-2">
              <label className="text-sm font-medium text-slate-700">Produsen / pengirim<select className={inputClass} value={originCustomerUserId} onChange={(event) => {
                const id = event.target.value;
                setOriginCustomerUserId(id);
                const producer = producers.find((user) => String(user.id) === id);
                if (producer?.customer?.address) setOrigin(producer.customer.address);
              }} required><option value="">Pilih produsen</option>{producers.map((user) => <option key={user.id} value={user.id}>{user.customer?.company_name} — {user.name} · WA {user.customer?.whatsapp_number ?? "belum diisi"}</option>)}</select></label>
              <label className="text-sm font-medium text-slate-700">Distributor / apotek penerima<select className={inputClass} value={destinationCustomerUserId} onChange={(event) => {
                const id = event.target.value;
                setDestinationCustomerUserId(id);
                const recipient = recipients.find((user) => String(user.id) === id);
                if (recipient?.customer?.address) setDestination(recipient.customer.address);
              }} required><option value="">Pilih penerima</option>{recipients.map((user) => <option key={user.id} value={user.id}>{user.customer?.company_name} — {user.name} · WA {user.customer?.whatsapp_number ?? "belum diisi"}</option>)}</select></label>
              <label className="text-sm font-medium text-slate-700">Produk<select className={inputClass} value={productId} onChange={(event) => setProductId(event.target.value)} required>{products.map((product) => <option key={product.id} value={product.id}>{product.sku} — {product.name} ({product.min_temperature}–{product.max_temperature}°C)</option>)}</select></label>
              <label className="text-sm font-medium text-slate-700">Jumlah<input className={inputClass} type="number" min={1} required value={quantity} onChange={(event) => setQuantity(event.target.value)} /></label>
              <label className="text-sm font-medium text-slate-700">Jadwal (opsional)<input className={inputClass} type="datetime-local" value={scheduledAt} onChange={(event) => setScheduledAt(event.target.value)} /></label>
              <label className="text-sm font-medium text-slate-700 sm:col-span-2">Alamat asal<input className={inputClass} required maxLength={1000} value={origin} onChange={(event) => setOrigin(event.target.value)} /></label>
              <label className="text-sm font-medium text-slate-700 sm:col-span-2">Alamat tujuan<input className={inputClass} required maxLength={1000} value={destination} onChange={(event) => setDestination(event.target.value)} /></label>
              <label className="text-sm font-medium text-slate-700 sm:col-span-2">Catatan<textarea className={inputClass} rows={3} maxLength={5000} value={notes} onChange={(event) => setNotes(event.target.value)} /></label>
              <div className="sm:col-span-2"><button className={buttonClass} disabled={loading}>{loading ? "Menyimpan..." : "Simpan shipment"}</button></div>
            </form>
          )}
        </Card>
      </div>
    </DashboardLayout>
  );
}
