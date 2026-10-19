"use client";

import type { ReactNode } from "react";

export const inputClass =
  "mt-1 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-base text-slate-900 outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-100 sm:text-sm";

export const buttonClass =
  "inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60";

export function PageTitle({
  title,
  description,
  action,
}: {
  title: string;
  description?: string;
  action?: ReactNode;
}) {
  return (
    <div className="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end">
      <div className="min-w-0">
        <h1 className="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
          {title}
        </h1>
        {description && (
          <p className="mt-1.5 max-w-3xl text-sm leading-6 text-slate-500 sm:mt-2">{description}</p>
        )}
      </div>
      {action}
    </div>
  );
}

export function Notice({
  children,
  tone = "error",
}: {
  children: ReactNode;
  tone?: "error" | "success" | "info";
}) {
  const styles = {
    error: "border-red-200 bg-red-50 text-red-800",
    success: "border-emerald-200 bg-emerald-50 text-emerald-800",
    info: "border-blue-200 bg-blue-50 text-blue-800",
  };

  return (
    <p role="status" className={`rounded-xl border px-4 py-3 text-sm ${styles[tone]}`}>
      {children}
    </p>
  );
}

export function StatusBadge({ status }: { status: string }) {
  const color =
    status === "delivered" || status === "resolved"
      ? "bg-emerald-100 text-emerald-800"
      : status === "in_transit"
        ? "bg-blue-100 text-blue-800"
        : status === "rejected" || status === "cancelled" || status === "critical"
          ? "bg-red-100 text-red-800"
          : status === "open" || status === "pending" || status === "warning"
            ? "bg-amber-100 text-amber-800"
            : "bg-slate-100 text-slate-700";

  return (
    <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${color}`}>
      {status.replaceAll("_", " ")}
    </span>
  );
}

export function Card({
  children,
  className = "",
}: {
  children: ReactNode;
  className?: string;
}) {
  return (
    <section className={`min-w-0 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-[0_2px_12px_rgba(35,39,43,0.035)] sm:p-5 ${className}`}>
      {children}
    </section>
  );
}

export function Loading({ label = "Memuat data..." }: { label?: string }) {
  return <p className="py-10 text-center text-sm text-slate-500">{label}</p>;
}

export function ErrorState({ message }: { message: string }) {
  return <Notice>{message}</Notice>;
}

export function formatDate(value: string | null | undefined): string {
  if (!value) return "-";
  const date = new Date(value);
  return Number.isNaN(date.getTime())
    ? value
    : date.toLocaleString("id-ID", { dateStyle: "medium", timeStyle: "short" });
}

export function Pager({
  page,
  lastPage,
  onChange,
}: {
  page: number;
  lastPage: number;
  onChange: (page: number) => void;
}) {
  return (
    <div className="flex flex-col items-start justify-between gap-3 border-t border-slate-100 pt-4 text-sm sm:flex-row sm:items-center">
      <span className="text-slate-500">Halaman {page} dari {lastPage}</span>
      <div className="flex w-full gap-2 sm:w-auto">
        <button className="min-h-10 flex-1 rounded-xl border border-slate-200 px-3 py-1.5 font-medium hover:bg-slate-50 disabled:opacity-40 sm:flex-none" disabled={page <= 1} onClick={() => onChange(page - 1)}>Sebelumnya</button>
        <button className="min-h-10 flex-1 rounded-xl border border-slate-200 px-3 py-1.5 font-medium hover:bg-slate-50 disabled:opacity-40 sm:flex-none" disabled={page >= lastPage} onClick={() => onChange(page + 1)}>Berikutnya</button>
      </div>
    </div>
  );
}
