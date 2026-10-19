"use client";

import type { ReactNode } from "react";
import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { X } from "lucide-react";
import { api, clearSession, errorMessage, getStoredUser, roleHome, type AuthUser, type Role } from "@/lib/api";
import { AppSidebar } from "./app-sidebar";
import { AppHeader } from "./app-header";

export function DashboardLayout({
  children,
  roles,
}: {
  children: ReactNode;
  roles?: Role[];
}) {
  const router = useRouter();
  const [user, setUser] = useState<AuthUser | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [mobileNavOpen, setMobileNavOpen] = useState(false);
  const rolesKey = roles?.join(",");

  useEffect(() => {
    const handleUnauthorized = () => router.replace("/login");
    window.addEventListener("anteraja:unauthorized", handleUnauthorized);
    return () => window.removeEventListener("anteraja:unauthorized", handleUnauthorized);
  }, [router]);

  useEffect(() => {
    if (!mobileNavOpen) return;
    const closeOnEscape = (event: KeyboardEvent) => {
      if (event.key === "Escape") setMobileNavOpen(false);
    };
    window.addEventListener("keydown", closeOnEscape);
    return () => window.removeEventListener("keydown", closeOnEscape);
  }, [mobileNavOpen]);

  useEffect(() => {
    let active = true;

    async function verifySession() {
      const stored = getStoredUser();
      if (!stored || !localStorage.getItem("anteraja_token")) {
        router.replace("/login");
        return;
      }

      try {
        const response = await api.get<{ data: AuthUser }>("/me");
        const verified = response.data.data;
        if (!active) return;
        localStorage.setItem("anteraja_user", JSON.stringify(verified));

        if (rolesKey && !rolesKey.split(",").includes(verified.role)) {
          router.replace(roleHome(verified.role));
          return;
        }

        setUser(verified);
        setLoading(false);
      } catch (requestError) {
        if (!active) return;
        if ((requestError as { response?: { status?: number } }).response?.status === 401) {
          clearSession();
          router.replace("/login");
          return;
        }
        setError(errorMessage(requestError));
        setLoading(false);
      }
    }

    void verifySession();
    return () => {
      active = false;
    };
  }, [rolesKey, router]);

  if (loading) {
    return <main className="grid min-h-screen place-items-center text-sm text-slate-500">Memeriksa sesi akun...</main>;
  }

  if (error) {
    return (
      <main className="grid min-h-screen place-items-center p-6">
        <div className="max-w-lg rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-800">
          Gagal memverifikasi sesi ke API: {error}. Periksa backend dan koneksi API, lalu muat ulang.
        </div>
      </main>
    );
  }

  if (!user) return null;

  return (
    <div className="app-scroll-area min-h-dvh bg-[#f7f8fa]">
      <div className="hidden md:block"><AppSidebar role={user.role} /></div>
      <div className="min-h-dvh min-w-0 md:ml-64">
        <AppHeader user={user} onOpenMenu={() => setMobileNavOpen(true)} />
        <main className="mx-auto w-full max-w-[1600px] p-3 pb-8 sm:p-5 md:p-8">{children}</main>
      </div>
      {mobileNavOpen && (
        <div className="fixed inset-0 z-50 md:hidden">
          <button
            type="button"
            aria-label="Tutup menu"
            className="absolute inset-0 h-full w-full bg-slate-950/45"
            onClick={() => setMobileNavOpen(false)}
          />
          <div className="relative h-full w-[min(18rem,85vw)]" role="dialog" aria-modal="true" aria-label="Menu navigasi">
            <button type="button" aria-label="Tutup menu" onClick={() => setMobileNavOpen(false)} className="absolute right-3 top-4 z-40 inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-white"><X size={18} /></button>
            <AppSidebar role={user.role} onNavigate={() => setMobileNavOpen(false)} />
          </div>
        </div>
      )}
    </div>
  );
}
