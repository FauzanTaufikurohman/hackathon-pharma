import type { TrackingLocation } from "@/lib/api";
import { formatDate } from "@/lib/ui";

export function TrackingMap({
  location,
  title = "Lokasi kurir",
}: {
  location: TrackingLocation;
  title?: string;
}) {
  const latitude = Number(location.latitude);
  const longitude = Number(location.longitude);
  if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) return null;

  const offset = 0.012;
  const bounds = [
    longitude - offset,
    latitude - offset,
    longitude + offset,
    latitude + offset,
  ].join("%2C");
  const src = `https://www.openstreetmap.org/export/embed.html?bbox=${bounds}&layer=mapnik&marker=${latitude}%2C${longitude}`;

  return (
    <div className="overflow-hidden rounded-xl border border-slate-200">
      <iframe
        title={title}
        src={src}
        className="h-64 w-full border-0 sm:h-80"
        loading="lazy"
        referrerPolicy="no-referrer"
      />
      <p className="bg-slate-50 px-3 py-2 text-xs text-slate-500">
        {location.is_simulated ? "Lokasi simulasi" : "Lokasi GPS"} · diperbarui {formatDate(location.recorded_at)}
      </p>
    </div>
  );
}
