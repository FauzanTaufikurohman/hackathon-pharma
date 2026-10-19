export function BrandLogo({
  inverse = false,
  compact = false,
}: {
  inverse?: boolean;
  compact?: boolean;
}) {
  return (
    <span className="inline-flex min-w-0 items-center gap-3">
      <svg
        aria-hidden="true"
        viewBox="0 0 44 44"
        className="h-10 w-10 shrink-0"
        fill="none"
      >
        <rect width="44" height="44" rx="13" fill="#E6007E" />
        <path d="M11 28.5h7.2l4-13h-7.1L11 28.5Z" fill="white" />
        <path d="M22 28.5h7.2l4-13h-7.1L22 28.5Z" fill="#FFB33B" />
        <path d="M12 32h19" stroke="white" strokeWidth="2.4" strokeLinecap="round" />
      </svg>
      <span className="min-w-0 leading-tight">
        <span className={`block truncate text-base font-extrabold tracking-tight ${inverse ? "text-white" : "text-[#292d32]"}`}>
          anteraja<span className="text-[#E6007E]">.</span>
        </span>
        {!compact && (
          <span className={`mt-0.5 block truncate text-[10px] font-semibold uppercase tracking-[0.16em] ${inverse ? "text-slate-400" : "text-slate-500"}`}>
            pharma cold chain
          </span>
        )}
      </span>
    </span>
  );
}
