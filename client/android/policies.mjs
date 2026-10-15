export function safeFilename(value) {
  const name = String(value || "laporan.pdf")
    .replace(/[^a-zA-Z0-9._-]/g, "_")
    .replace(/^\.+/, "")
    .slice(-100);
  return name || "laporan.pdf";
}
export function externalWebUrl(value, origin) {
  try {
    const url = new URL(value, origin);
    return url.protocol === "https:" &&
      url.origin !== origin &&
      !url.username &&
      !url.password
      ? url.href
      : null;
  } catch {
    return null;
  }
}
export function locationErrorCode(code) {
  return code === "OS-PLUG-GLOC-0003"
    ? 1
    : code === "OS-PLUG-GLOC-0010"
      ? 3
      : 2;
}

export function expiredReportNames(files, now = Date.now()) {
  return files
    .filter(
      (file) =>
        file.type === "file" &&
        Number.isFinite(file.mtime) &&
        file.mtime < now - 86400000 &&
        /^\d{13}-[a-zA-Z0-9._-]{1,100}$/.test(file.name),
    )
    .map((file) => file.name);
}
