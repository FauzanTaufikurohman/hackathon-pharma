"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import {
  Activity,
  Building2,
  ClipboardCheck,
  FileText,
  LayoutDashboard,
  Package,
  Thermometer,
  Truck,
} from "lucide-react";
import type { Role } from "@/lib/api";
import { BrandLogo } from "@/components/brand-logo";

const navigation: Record<Role, { label: string; href: string; icon: typeof Package }[]> = {
  admin: [
    { label: "Dashboard", href: "/admin", icon: LayoutDashboard },
    { label: "Shipments", href: "/admin/shipments", icon: Package },
    { label: "Thermal incidents", href: "/admin/thermal", icon: Thermometer },
    { label: "Customers", href: "/admin/customers", icon: Building2 },
    { label: "Products", href: "/admin/products", icon: Package },
    { label: "Reports", href: "/admin/reports", icon: FileText },
  ],
  checker: [{ label: "Inspections", href: "/checker", icon: ClipboardCheck }],
  courier: [{ label: "My deliveries", href: "/courier", icon: Truck }],
  customer: [{ label: "Shipment tracking", href: "/customer/tracking", icon: Package }],
};

export function AppSidebar({
  role,
  onNavigate,
}: {
  role: Role;
  onNavigate?: () => void;
}) {
  const pathname = usePathname();

  return (
    <aside className="fixed inset-y-0 left-0 z-30 flex h-dvh w-64 shrink-0 flex-col overflow-hidden bg-[#24282d] text-slate-300">
      <Link href="/" className="flex h-20 shrink-0 items-center border-b border-white/10 px-5">
        <BrandLogo inverse />
      </Link>

      <nav className="min-h-0 flex-1 space-y-1 overflow-hidden px-3 py-6">
        {navigation[role].map((item) => {
          const active = pathname === item.href || (item.href !== "/admin" && pathname.startsWith(`${item.href}/`));
          const Icon = item.icon;

          return (
            <Link key={item.href} href={item.href} onClick={onNavigate} className={`flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition ${active ? "bg-blue-600 font-medium text-white" : "text-slate-400 hover:bg-white/5 hover:text-white"}`}>
              <Icon size={18} />
              <span>{item.label}</span>
            </Link>
          );
        })}
      </nav>

      <div className="border-t border-white/10 p-4">
        <div className="flex items-center gap-3 rounded-lg bg-white/5 p-3">
          <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400"><Activity size={18} /></div>
          <div>
            <p className="text-xs font-medium text-white">Simulation Mode</p>
            <p className="mt-1 text-[10px] text-slate-400">No physical IoT connected</p>
          </div>
        </div>
      </div>
    </aside>
  );
}
