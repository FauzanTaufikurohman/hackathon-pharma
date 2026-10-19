
"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import {
  ArrowLeft,
  MapPin,
  Search,
  Package,
  ClipboardCheck,
  Truck,
  MapPinned,
  CheckCircle2,
  XCircle,
} from "lucide-react";

import { DashboardLayout } from "@/components/layouts/dashboard-layout";
import { TrackingMap } from "@/components/tracking-map";
import { api, errorMessage, type PageResult, type Shipment } from "@/lib/api";
import {
  Card,
  ErrorState,
  formatDate,
  inputClass,
  Loading,
  PageTitle,
  Pager,
  StatusBadge,
} from "@/lib/ui";

const shipmentSteps = [
  {
    key: "pending",
    label: "Shipment dibuat",
    description: "Data pengiriman telah dibuat",
    icon: Package,
    aliases: ["pending", "created", "shipment_created"],
  },
  {
    key: "processing",
    label: "Diproses",
    description: "Shipment sedang dipersiapkan",
    icon: ClipboardCheck,
    aliases: [
      "processing",
      "confirmed",
      "processed",
      "ready_for_pickup",
    ],
  },
  {
    key: "picked_up",
    label: "Diambil kurir",
    description: "Shipment telah diambil oleh kurir",
    icon: Truck,
    aliases: ["picked_up", "pickup", "pickedup"],
  },
  {
    key: "in_transit",
    label: "Dalam perjalanan",
    description: "Shipment sedang menuju tujuan",
    icon: MapPinned,
    aliases: [
      "in_transit",
      "intransit",
      "on_the_way",
      "shipped",
    ],
  },
  {
    key: "delivered",
    label: "Terkirim",
    description: "Shipment telah sampai di tujuan",
    icon: CheckCircle2,
    aliases: ["delivered", "completed"],
  },
];

function normalizeShipmentStatus(status?: string) {
  return (status ?? "")
    .toLowerCase()
    .trim()
    .replaceAll(" ", "_")
    .replaceAll("-", "_");
}

function ShipmentProgress({ status }: { status: string }) {
  const normalizedStatus = normalizeShipmentStatus(status);

  const currentIndex = shipmentSteps.findIndex((step) =>
    step.aliases.includes(normalizedStatus),
  );

  const isCancelled = [
    "cancelled",
    "canceled",
    "dibatalkan",
  ].includes(normalizedStatus);

  const currentStep =
    currentIndex >= 0 ? shipmentSteps[currentIndex] : null;

  return (
    <Card>
      <div className="mb-5 flex flex-wrap items-start justify-between gap-3">
        <div>
          <h3 className="text-base font-semibold text-slate-900">
            Riwayat pengiriman
          </h3>
          <p className="mt-1 text-sm text-slate-500">
            Pantau perkembangan shipment dari awal hingga tujuan.
          </p>
        </div>

        {currentIndex >= 0 && !isCancelled && (
          <span className="rounded-full bg-pink-100 px-3 py-1 text-xs font-semibold text-pink-700">
            Tahap {currentIndex + 1} dari {shipmentSteps.length}
          </span>
        )}
      </div>

      {isCancelled ? (
        <div className="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-4">
          <XCircle className="shrink-0 text-red-600" size={24} />
          <div>
            <p className="font-semibold text-red-700">
              Pengiriman dibatalkan
            </p>
            <p className="mt-1 text-sm text-red-600">
              Shipment ini tidak melanjutkan proses pengiriman.
            </p>
          </div>
        </div>
      ) : (
        <>
          <div className="overflow-x-auto pb-3">
            <div className="flex min-w-[620px] items-start">
              {shipmentSteps.map((step, index) => {
                const Icon = step.icon;
                const isCompleted =
                  currentIndex >= 0 && index < currentIndex;
                const isCurrent = index === currentIndex;
                const isActive = index <= currentIndex;

                return (
                  <div
                    key={step.key}
                    className="relative flex flex-1 flex-col items-center text-center"
                  >
                    {/* Garis penghubung antarikon */}
                    {index < shipmentSteps.length - 1 && (
                      <div
                        className={`absolute left-1/2 top-6 h-1 w-full ${
                          currentIndex > index
                            ? "bg-pink-500"
                            : "bg-slate-200"
                        }`}
                      />
                    )}

                    {/* Ikon tahapan */}
                    <div
                      className={`relative z-10 flex h-12 w-12 items-center justify-center rounded-full border-2 transition-colors ${
                        isActive
                          ? "border-pink-500 bg-pink-500 text-white"
                          : "border-slate-300 bg-white text-slate-700"
                      }`}
                    >
                      <Icon size={21} strokeWidth={2} />
                    </div>

                    {/* Nama dan deskripsi tahapan */}
                    <div className="mt-3 w-full px-2">
                      <p
                        className={`text-xs font-semibold sm:text-sm ${
                          isActive
                            ? "text-pink-600"
                            : "text-slate-900"
                        }`}
                      >
                        {step.label}
                      </p>

                      <p className="mt-1 text-[11px] leading-relaxed text-slate-500 sm:text-xs">
                        {step.description}
                      </p>

                      {isCurrent && (
                        <span className="mt-2 inline-block rounded-full bg-pink-100 px-2 py-1 text-[10px] font-semibold text-pink-700">
                          Status saat ini
                        </span>
                      )}

                      {isCompleted && (
                        <span className="mt-2 inline-block text-[10px] font-medium text-pink-600">
                          Selesai
                        </span>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          </div>

          {currentStep ? (
            <div className="mt-3 rounded-xl border border-pink-200 bg-pink-50 p-4">
              <div className="flex items-start gap-3">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-pink-500 text-white">
                  <currentStep.icon size={20} />
                </div>

                <div>
                  <p className="text-xs font-medium text-pink-700">
                    Status pengiriman saat ini
                  </p>
                  <p className="mt-1 font-semibold text-slate-900">
                    {currentStep.label}
                  </p>
                  <p className="mt-1 text-sm text-slate-600">
                    {currentStep.description}
                  </p>
                </div>
              </div>
            </div>
          ) : (
            <p className="mt-3 rounded-lg bg-slate-50 p-3 text-sm text-slate-600">
              Status backend:{" "}
              <span className="font-semibold">
                {status.replaceAll("_", " ")}
              </span>
              . Tahapan progres belum dipetakan untuk status ini.
            </p>
          )}
        </>
      )}
    </Card>
  );
}

export default function CustomerTrackingPage() {
  const [page, setPage] = useState(1);
  const [result, setResult] = useState<PageResult<Shipment>>({
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  });
  const [selectedId, setSelectedId] = useState<number | null>(null);
  const [search, setSearch] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    try {
      const response = await api.get("/customer/shipments", {
        params: { page },
      });

      const next = response.data as PageResult<Shipment>;

      setResult(next);
      setSelectedId(
        (current) => current ?? next.data[0]?.id ?? null,
      );
      setError("");
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setLoading(false);
    }
  }, [page]);

  useEffect(() => {
    void Promise.resolve().then(load);

    const interval = window.setInterval(() => void load(), 30000);

    return () => window.clearInterval(interval);
  }, [load]);

  const filtered = useMemo(
    () =>
      result.data.filter(
        (item) =>
          item.shipment_number
            .toLowerCase()
            .includes(search.toLowerCase()) ||
          (item.product?.name ?? "")
            .toLowerCase()
            .includes(search.toLowerCase()),
      ),
    [result.data, search],
  );

  const selected = result.data.find(
    (shipment) => shipment.id === selectedId,
  );

  const latestLocation = selected?.tracking_locations?.[0];
  const lastCheck = selected?.thermal_checks?.[0];

  return (
    <DashboardLayout roles={["customer"]}>
      <div className="space-y-5">
        <PageTitle
          title="Pelacakan shipment"
          description="Status dan lokasi diperbarui otomatis setiap 30 detik."
        />

        {error && <ErrorState message={error} />}

        <div className="grid gap-5 xl:grid-cols-[0.8fr_1.2fr]">
          {/* Daftar shipment */}
          <Card
            className={
              selectedId === null ? "block" : "hidden md:block"
            }
          >
            <label className="relative mb-4 block">
              <Search
                size={16}
                className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
              />

              <input
                className={`${inputClass} mt-0 pl-9`}
                placeholder="Cari nomor atau nama produk"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </label>

            {loading ? (
              <Loading />
            ) : filtered.length === 0 ? (
              <p className="py-6 text-sm text-slate-500">
                {result.total
                  ? "Tidak ada shipment yang cocok."
                  : "Belum ada shipment untuk akun customer ini."}
              </p>
            ) : (
              <div className="space-y-3">
                {filtered.map((shipment) => (
                  <button
                    key={shipment.id}
                    type="button"
                    onClick={() => setSelectedId(shipment.id)}
                    className={`w-full rounded-xl border p-4 text-left transition-colors ${
                      selected?.id === shipment.id
                        ? "border-pink-500 bg-pink-50"
                        : "border-slate-200 hover:bg-slate-50"
                    }`}
                  >
                    <div className="flex items-center justify-between gap-2">
                      <strong>{shipment.shipment_number}</strong>
                      <StatusBadge status={shipment.status} />
                    </div>

                    <p className="mt-1 text-sm text-slate-600">
                      {shipment.product?.name} · {shipment.quantity} unit
                    </p>

                    <p className="mt-1 text-xs text-slate-500">
                      {shipment.origin_address} →{" "}
                      {shipment.destination_address}
                    </p>
                  </button>
                ))}
              </div>
            )}

            {result.total > result.per_page && (
              <div className="mt-4">
                <Pager
                  page={result.current_page}
                  lastPage={result.last_page}
                  onChange={(nextPage) => {
                    setSelectedId(null);
                    setPage(nextPage);
                  }}
                />
              </div>
            )}
          </Card>

          {/* Detail shipment */}
          <div
            className={`space-y-5 ${
              selectedId !== null ? "block" : "hidden md:block"
            }`}
          >
            {selected ? (
              <>
                <Card>
                  <button
                    type="button"
                    className="mb-4 inline-flex min-h-10 items-center gap-2 text-sm font-medium text-pink-700 md:hidden"
                    onClick={() => setSelectedId(null)}
                  >
                    <ArrowLeft size={16} />
                    Kembali ke daftar
                  </button>

                  <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                      <p className="text-xs uppercase text-slate-500">
                        Detail pengiriman
                      </p>
                      <h2 className="mt-1 text-xl font-bold">
                        {selected.shipment_number}
                      </h2>
                    </div>

                    <StatusBadge status={selected.status} />
                  </div>

                  <dl className="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                    <Info
                      label="Produk"
                      value={selected.product?.name}
                    />

                    <Info
                      label="Jumlah"
                      value={String(selected.quantity)}
                    />

                    <Info
                      label="Produsen / pengirim"
                      value={
                        selected.origin_customer_user?.customer
                          ?.company_name ??
                        selected.customer?.company_name
                      }
                    />

                    <Info
                      label="Distributor / apotek"
                      value={
                        selected.destination_customer_user?.customer
                          ?.company_name
                      }
                    />

                    <Info
                      label="Alamat asal"
                      value={selected.origin_address}
                    />

                    <Info
                      label="Alamat tujuan"
                      value={selected.destination_address}
                    />

                    <Info
                      label="Mulai"
                      value={formatDate(selected.started_at)}
                    />

                    <Info
                      label="Update terakhir"
                      value={formatDate(
                        latestLocation?.recorded_at ??
                          selected.updated_at,
                      )}
                    />
                  </dl>

                  {selected.status === "in_transit" &&
                  latestLocation ? (
                    <div className="mt-5">
                      <p className="mb-2 text-sm font-medium">
                        <MapPin
                          size={16}
                          className="mr-1 inline"
                        />
                        Peta lokasi kurir
                      </p>

                      <TrackingMap location={latestLocation} />
                    </div>
                  ) : (
                    <p className="mt-4 text-sm text-slate-500">
                      {selected.status === "in_transit"
                        ? "Menunggu lokasi GPS kurir."
                        : "Peta kurir tersedia saat shipment dalam perjalanan."}
                    </p>
                  )}
                </Card>

                {/* Progres pengiriman seperti Tokopedia */}
                <ShipmentProgress status={selected.status} />

                {/* Detail aktivitas dari backend */}
                <Card>
                  <h3 className="mb-4 font-semibold">
                    Detail aktivitas pengiriman
                  </h3>

                  {selected.events?.length ? (
                    <div className="space-y-4">
                      {selected.events.map((event, index) => (
                        <div
                          key={event.id}
                          className="flex gap-3"
                        >
                          <div className="flex flex-col items-center">
                            <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-pink-100 text-pink-600">
                              <CheckCircle2 size={17} />
                            </div>

                            {index <
                              selected.events!.length - 1 && (
                              <div className="mt-1 w-px flex-1 bg-slate-200" />
                            )}
                          </div>

                          <div className="min-w-0 flex-1 pb-3">
                            <p className="text-sm font-semibold capitalize text-slate-900">
                              {event.event_type.replaceAll("_", " ")}
                            </p>

                            <p className="mt-1 text-sm text-slate-600">
                              {event.description}
                            </p>

                            <p className="mt-1 text-xs text-slate-400">
                              {formatDate(event.occurred_at)}
                            </p>
                          </div>
                        </div>
                      ))}
                    </div>
                  ) : (
                    <p className="text-sm text-slate-500">
                      Belum ada detail aktivitas pengiriman.
                    </p>
                  )}
                </Card>

                {/* Pemantauan suhu dan insiden */}
                <Card>
                  <h3 className="font-semibold">
                    Pemantauan suhu & insiden
                  </h3>

                  {lastCheck && (
                    <p className="mt-3 text-sm">
                      Pemeriksaan terakhir:{" "}
                      <strong>
                        {lastCheck.temperature_celsius}°C
                      </strong>{" "}
                      · {formatDate(lastCheck.checked_at)}
                    </p>
                  )}

                  {selected.thermal_incidents?.length ? (
                    selected.thermal_incidents.map((incident) => (
                      <div
                        key={incident.id}
                        className="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm"
                      >
                        <div className="flex justify-between gap-2">
                          <strong>
                            {incident.temperature_celsius}°C ·{" "}
                            {incident.exposure_duration_seconds ??
                              "Durasi tidak diketahui"}{" "}
                            detik
                          </strong>

                          <StatusBadge
                            status={incident.incident_status}
                          />
                        </div>

                        <p className="mt-1">
                          {incident.description}
                        </p>

                        <p className="mt-1 text-xs text-slate-600">
                          Penilaian AI:{" "}
                          {incident.ai_assessment?.summary ??
                            incident.ai_assessment_status ??
                            "menunggu"}
                        </p>
                      </div>
                    ))
                  ) : (
                    <p className="mt-3 text-sm text-slate-500">
                      Tidak ada insiden termal tercatat.
                    </p>
                  )}
                </Card>
              </>
            ) : (
              <Card>
                <p className="text-sm text-slate-500">
                  Pilih shipment untuk melihat detail tracking.
                </p>
              </Card>
            )}
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}

function Info({
  label,
  value,
}: {
  label: string;
  value?: string;
}) {
  return (
    <div>
      <dt className="text-xs uppercase text-slate-500">
        {label}
      </dt>
      <dd className="mt-1 font-medium text-slate-900">
        {value || "-"}
      </dd>
    </div>
  );
}
