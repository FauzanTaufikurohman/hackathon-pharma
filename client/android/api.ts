import { Capacitor } from "@capacitor/core";
import { Filesystem, Directory } from "@capacitor/filesystem";
import { Share } from "@capacitor/share";
import { download as webDownload, request } from "../src/services/api";
import { safeFilename, expiredReportNames } from "./policies.mjs";
export * from "../src/services/api";
let sharing = false;

export async function cleanOldReports() {
  try {
    const { files } = await Filesystem.readdir({
      path: "pharma-reports",
      directory: Directory.Cache,
    });
    await Promise.allSettled(
      expiredReportNames(files).map((name) =>
        Filesystem.deleteFile({
          path: "pharma-reports/" + name,
          directory: Directory.Cache,
        }),
      ),
    );
  } catch {
    /* first launch has no report directory yet */
  }
}

// keep shared authentication/error handling; only Android's file delivery changes.
export async function download(path: string, filename: string) {
  if (!Capacitor.isNativePlatform()) return webDownload(path, filename);
  if (sharing)
    throw new Error("Selesaikan menu berbagi yang masih terbuka dahulu.");
  sharing = true;
  try {
    const blob = await request<Blob>(path, {
      headers: { Accept: "application/octet-stream" },
    });
    if (!blob.size || blob.size > 20 * 1024 * 1024)
      throw new Error("Ukuran laporan kosong atau lebih dari 20 MB.");
    const data = await new Promise<string>((resolve, reject) => {
      const reader = new FileReader();
      reader.onerror = () =>
        reject(new Error("Laporan belum dapat dibaca. Coba kembali."));
      reader.onload = () => resolve(String(reader.result).split(",")[1] || "");
      reader.readAsDataURL(blob);
    });
    const file = await Filesystem.writeFile({
      path: "pharma-reports/" + Date.now() + "-" + safeFilename(filename),
      data,
      directory: Directory.Cache,
      recursive: true,
    });
    // Android's share sheet lets the user open, save or forward the actual report.
    try {
      await Share.share({
        title: "Laporan Anteraja Pharma",
        files: [file.uri],
        dialogTitle: "Buka atau simpan laporan",
      });
    } catch (error) {
      if (!(error instanceof Error) || error.message !== "Share canceled")
        throw error;
      // dismissing the share sheet is a user choice, not a failed server request.
    }
  } finally {
    sharing = false;
  }
}
