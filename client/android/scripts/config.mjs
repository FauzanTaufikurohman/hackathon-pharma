import { readFileSync } from "node:fs";
import { fileURLToPath } from "node:url";

export const androidRoot = fileURLToPath(new URL("../", import.meta.url));
export const clientRoot = fileURLToPath(new URL("../../", import.meta.url));

export function validateApiUrl(value) {
  let url;
  try {
    url = new URL(String(value || "").trim());
  } catch {
    throw new Error(
      "Isi ANDROID_API_BASE_URL di android/.env.local dengan URL HTTPS Laravel + /api/v1.",
    );
  }
  if (
    url.protocol !== "https:" ||
    url.username ||
    url.password ||
    url.search ||
    url.hash ||
    ["localhost", "127.0.0.1", "[::1]"].includes(url.hostname) ||
    /(^|\.)example\.(com|org|net)$/.test(url.hostname) ||
    !url.pathname.replace(/\/+$/, "").endsWith("/api/v1")
  ) {
    throw new Error(
      "Gunakan URL HTTPS publik nyata berakhiran /api/v1, tanpa password/query. localhost bukan server laptop dari HP.",
    );
  }
  return url.href.replace(/\/+$/, "");
}

export function getApiUrl() {
  let value = process.env.ANDROID_API_BASE_URL;
  if (!value) {
    try {
      const env = readFileSync(
        new URL("../.env.local", import.meta.url),
        "utf8",
      );
      const line = env
        .split(/\r?\n/)
        .find((line) => /^\s*ANDROID_API_BASE_URL\s*=/.test(line));
      value = line
        ?.slice(line.indexOf("=") + 1)
        .trim()
        .replace(/^(['"])(.*)\1$/, "$2");
    } catch {
      /* missing file is reported by the validator */
    }
  }
  return validateApiUrl(value);
}

// only redirect the mobile build; the original web API module stays untouched.
export function isSharedApi(source, importer) {
  if (
    !importer ||
    importer.split("?")[0].replaceAll("\\", "/").endsWith("/android/api.ts")
  )
    return false;
  const normalized = source.replaceAll("\\", "/");
  return (
    normalized === "@/services/api" ||
    normalized === "@/services/api.ts" ||
    /(?:^|\/)services\/api(?:\.ts)?$/.test(normalized) ||
    (normalized === "./api" &&
      importer.replaceAll("\\", "/").includes("/src/services/"))
  );
}
