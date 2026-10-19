
"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import {
  Activity,
  ArrowRight,
  Eye,
  EyeOff,
  LockKeyhole,
  Mail,
  ShieldCheck,
  Thermometer,
} from "lucide-react";
import { api, errorMessage, roleHome, setSession, type AuthUser } from "@/lib/api";
import { BrandLogo } from "@/components/brand-logo";

export default function LoginPage() {
  const router = useRouter();

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [registering, setRegistering] = useState(false);
  const [name, setName] = useState("");
  const [companyName, setCompanyName] = useState("");
  const [companyType, setCompanyType] = useState("pharmacy");
  const [whatsapp, setWhatsapp] = useState("");
  const [whatsappOptIn, setWhatsappOptIn] = useState(false);
  const [error, setError] = useState("");

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError("");
    setLoading(true);
    try {
      const response = registering
        ? await api.post("/register", {
            name,
            email,
            password,
            password_confirmation: password,
            company_name: companyName,
            company_type: companyType,
            whatsapp_number: whatsapp || null,
            whatsapp_opt_in: whatsappOptIn,
          })
        : await api.post("/login", { email, password });
      const { token, user } = response.data.data as {
        token: string;
        user: AuthUser;
      };
      setSession(token, user);
      router.replace(roleHome(user.role));
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setLoading(false);
    }
  }

  return (
    <main className="grid min-h-screen bg-white lg:grid-cols-2">
      <section className="relative hidden flex-col justify-between overflow-hidden bg-[#24282d] p-12 text-white lg:flex xl:p-16">
        <div className="absolute -right-28 -top-28 h-96 w-96 rounded-full bg-pink-500/20 blur-3xl" />
        <div className="absolute -bottom-32 -left-24 h-96 w-96 rounded-full bg-amber-400/15 blur-3xl" />

        <Link href="/" className="relative inline-flex items-center">
          <BrandLogo inverse />
        </Link>

        <div className="relative max-w-lg">
          <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-pink-400/20 bg-pink-400/10 px-3 py-1.5 text-xs text-pink-200">
            <Activity size={14} />
            Cold Chain Monitoring
          </div>

          <h1 className="text-4xl font-semibold leading-tight xl:text-5xl">
            Protect every shipment.
            <span className="mt-2 block text-pink-300">
              Preserve product integrity.
            </span>
          </h1>

          <p className="mt-6 max-w-md leading-7 text-slate-400">
            Monitor pharmaceutical shipments, record thermal incidents,
            and maintain traceable delivery reports in one platform.
          </p>

          <div className="mt-10 grid grid-cols-2 gap-4">
            <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
              <Thermometer className="mb-3 text-pink-300" size={22} />
              <p className="font-medium">Thermal monitoring</p>
              <p className="mt-1 text-sm text-slate-400">
                Temperature and incident records
              </p>
            </div>

            <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
              <ShieldCheck className="mb-3 text-amber-300" size={22} />
              <p className="font-medium">Traceable delivery</p>
              <p className="mt-1 text-sm text-slate-400">
                Shipment status and reports
              </p>
            </div>
          </div>
        </div>

        <p className="relative text-xs text-slate-500">
          Anteraja Pharma · Thermal AI Automation
        </p>
      </section>

      <section className="flex items-center justify-center px-6 py-12 sm:px-12">
        <div className="w-full max-w-md">
          <div className="mb-10 flex items-center lg:hidden">
            <BrandLogo />
          </div>

          <p className="text-sm font-semibold text-blue-700">
            Welcome back
          </p>
          <h2 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">
            Sign in to your account
          </h2>
          <p className="mt-3 text-sm leading-6 text-slate-500">
            Enter your account credentials to access the platform.
          </p>

          <form onSubmit={handleSubmit} className="mt-8 space-y-5">
            {registering && (
              <>
                <div>
                  <label htmlFor="name" className="mb-2 block text-sm font-medium text-slate-700">Nama kontak</label>
                  <input id="name" required value={name} onChange={(event) => setName(event.target.value)} className="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm" autoComplete="name" />
                </div>
                <div>
                  <label htmlFor="company" className="mb-2 block text-sm font-medium text-slate-700">Nama perusahaan</label>
                  <input id="company" required value={companyName} onChange={(event) => setCompanyName(event.target.value)} className="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm" />
                </div>
                <div>
                  <label htmlFor="company-type" className="mb-2 block text-sm font-medium text-slate-700">Jenis perusahaan</label>
                  <select id="company-type" value={companyType} onChange={(event) => setCompanyType(event.target.value)} className="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                    <option value="manufacturer">Produsen</option>
                    <option value="distributor">Distributor</option>
                    <option value="pharmacy">Apotek / toko obat</option>
                  </select>
                </div>
                <div>
                  <label htmlFor="whatsapp" className="mb-2 block text-sm font-medium text-slate-700">Nomor WhatsApp (format internasional)</label>
                  <input id="whatsapp" type="tel" value={whatsapp} onChange={(event) => setWhatsapp(event.target.value)} placeholder="6281234567890" className="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm" />
                  <label className="mt-2 flex items-start gap-2 text-xs text-slate-500">
                    <input type="checkbox" checked={whatsappOptIn} onChange={(event) => setWhatsappOptIn(event.target.checked)} />
                    Saya menyetujui pembaruan shipment melalui WhatsApp.
                  </label>
                </div>
              </>
            )}
            <div>
              <label
                htmlFor="email"
                className="mb-2 block text-sm font-medium text-slate-700"
              >
                Email address
              </label>

              <div className="relative">
                <Mail
                  size={18}
                  className="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"
                />
                <input
                  id="email"
                  type="email"
                  autoComplete="email"
                  required
                  value={email}
                  onChange={(event) => setEmail(event.target.value)}
                  placeholder="you@company.com"
                  className="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10"
                />
              </div>
            </div>

            <div>
              <div className="mb-2 flex items-center justify-between">
                <label
                  htmlFor="password"
                  className="text-sm font-medium text-slate-700"
                >
                  Password
                </label>
              </div>

              <div className="relative">
                <LockKeyhole
                  size={18}
                  className="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"
                />
                <input
                  id="password"
                  type={showPassword ? "text" : "password"}
                  autoComplete={registering ? "new-password" : "current-password"}
                  required
                  value={password}
                  onChange={(event) => setPassword(event.target.value)}
                  placeholder="Enter your password"
                  minLength={registering ? 8 : undefined}
                  className="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-12 text-sm text-slate-900 outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  aria-label={showPassword ? "Hide password" : "Show password"}
                  className="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"
                >
                  {showPassword ? (
                    <EyeOff size={18} />
                  ) : (
                    <Eye size={18} />
                  )}
                </button>
              </div>
            </div>

            <button
              type="submit"
              disabled={loading}
              className="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-3.5 text-sm font-semibold text-white transition hover:bg-blue-800 disabled:opacity-60"
            >
              {loading ? "Memproses..." : registering ? "Buat akun customer" : "Masuk"}
              {!loading && <ArrowRight size={17} />}
            </button>
          </form>

          {error && <p role="alert" className="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">{error}</p>}
          <button type="button" onClick={() => { setRegistering((value) => !value); setError(""); }} className="mt-5 w-full text-center text-sm font-medium text-blue-700 hover:underline">
            {registering ? "Sudah punya akun? Masuk" : "Belum punya akun customer? Daftar"}
          </button>

          <p className="mt-8 text-center text-xs text-slate-400">
            Secure access for pharmaceutical logistics operations.
          </p>
        </div>
      </section>
    </main>
  );
}
