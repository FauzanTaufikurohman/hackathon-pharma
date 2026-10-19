"use client";

import { ArrowLeft, LogOut, Menu } from "lucide-react";
import { useRouter } from "next/navigation";
import { useState } from "react";
import { api, clearSession, errorMessage, type AuthUser } from "@/lib/api";

export function AppHeader({
  user,
  onOpenMenu,
}: {
  user: AuthUser;
  onOpenMenu: () => void;
}) {
  const router = useRouter();
  const [error, setError] = useState("");

  async function logout() {
    setError("");
    try {
      await api.post("/logout");
    } catch (requestError) {
      setError(errorMessage(requestError));
      return;
    }

    clearSession();
    router.replace("/login");
  }

  const mobileBackEnabled = ["checker", "courier", "customer"].includes(user.role);

  return (
    <header className="sticky top-0 z-20 flex min-h-16 items-center justify-between gap-3 border-b border-slate-200 bg-white/95 px-3 py-3 backdrop-blur sm:px-5 md:min-h-20 md:px-8">
      <div className="flex min-w-0 items-center gap-3">
        <button type="button" aria-label="Buka menu navigasi" onClick={onOpenMenu} className="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 md:hidden"><Menu size={19} /></button>
        {mobileBackEnabled && <button type="button" aria-label="Kembali" onClick={() => router.back()} className="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-700 md:hidden"><ArrowLeft size={18} /></button>}
        <div>
          <p className="hidden text-xs text-slate-500 sm:block">Anteraja Pharma / Operations</p>
          <h1 className="text-lg font-semibold capitalize text-slate-900">{user.role} workspace</h1>
        {error && <p role="alert" className="mt-1 text-xs text-red-700">{error}</p>}
        </div>
      </div>
      <div className="flex items-center gap-3">
        <div className="hidden text-right sm:block">
          <p className="text-sm font-medium text-slate-800">{user.name}</p>
          <p className="text-xs text-slate-500">{user.email}</p>
        </div>
        <button type="button" onClick={logout} aria-label="Keluar" className="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
          <LogOut size={16} /><span className="hidden sm:inline">Keluar</span>
        </button>
      </div>
    </header>
  );
}
