import { App } from "@capacitor/app";
import { Browser } from "@capacitor/browser";
import { Dialog } from "@capacitor/dialog";
import { Geolocation } from "@capacitor/geolocation";
import { externalWebUrl, locationErrorCode } from "./policies.mjs";
import { cleanOldReports } from "./api";

export async function setupNative() {
  void cleanOldReports();
  // never request GPS during login; the courier's existing location button asks for it.
  const gps: Pick<Geolocation, "getCurrentPosition"> = {
    getCurrentPosition(success, failure, options) {
      void (async () => {
        try {
          let permission = await Geolocation.checkPermissions();
          if (
            permission.location !== "granted" &&
            permission.coarseLocation !== "granted"
          ) {
            permission = await Geolocation.requestPermissions({
              permissions: ["location"],
            });
          }
          if (
            permission.location !== "granted" &&
            permission.coarseLocation !== "granted"
          ) {
            throw { code: "OS-PLUG-GLOC-0003" };
          }
          const position = await Geolocation.getCurrentPosition({
            enableHighAccuracy:
              permission.location === "granted" &&
              options?.enableHighAccuracy === true,
            timeout: options?.timeout || 15000,
            maximumAge: options?.maximumAge || 0,
          });
          const coordinates = {
            ...position.coords,
            altitudeAccuracy: position.coords.altitudeAccuracy ?? null,
          };
          success({
            timestamp: position.timestamp,
            coords: { ...coordinates, toJSON: () => coordinates },
            toJSON: () => position,
          });
        } catch (error) {
          const code = locationErrorCode(
            (error as { code?: string })?.code || "",
          );
          failure?.({
            code,
            message:
              "Lokasi belum tersedia. Periksa GPS dan izin lokasi di pengaturan HP.",
            PERMISSION_DENIED: 1,
            POSITION_UNAVAILABLE: 2,
            TIMEOUT: 3,
          });
        }
      })();
    },
  };
  // override this one operation, not unrelated navigator APIs.
  if (navigator.geolocation) {
    Object.defineProperty(navigator.geolocation, "getCurrentPosition", {
      value: gps.getCurrentPosition,
      configurable: true,
    });
  } else {
    Object.defineProperty(navigator, "geolocation", {
      value: gps,
      configurable: true,
    });
  }

  let backBusy = false;
  await App.addListener("backButton", ({ canGoBack }) => {
    if (backBusy) return;
    const menu = document.querySelector<HTMLButtonElement>(
      'button[aria-expanded="true"][aria-controls^="portal-nav-"]',
    );
    if (menu) {
      menu.click();
      return;
    }
    if (canGoBack) {
      window.history.back();
      return;
    }
    backBusy = true;
    void Dialog.confirm({
      title: "Tutup Pharma?",
      message: "Keluar dari aplikasi sekarang?",
      okButtonTitle: "Keluar",
      cancelButtonTitle: "Tetap di sini",
    })
      .then(({ value }) => {
        if (value) void App.exitApp();
      })
      .catch(() => {
        /* a dismissed/unavailable dialog must not leave Back locked */
      })
      .finally(() => {
        backBusy = false;
      });
  });

  document.addEventListener("click", (event) => {
    if (event.defaultPrevented || !(event.target instanceof Element)) return;
    const anchor = event.target.closest("a[href]");
    if (!anchor || anchor.hasAttribute("download")) return;
    const url = externalWebUrl(
      anchor.getAttribute("href") || "",
      window.location.origin,
    );
    if (!url) return;
    event.preventDefault();
    void Browser.open({ url }).catch(() =>
      Dialog.alert({
        title: "Tautan belum terbuka",
        message: "Coba kembali setelah browser HP tersedia.",
      }),
    );
  });

  const status = document.createElement("div");
  status.className = "pharma-connection";
  status.setAttribute("role", "status");
  status.setAttribute("aria-live", "polite");
  status.textContent =
    "Koneksi terputus. Periksa jaringan sebelum menyimpan perubahan.";
  const update = () => {
    status.hidden = navigator.onLine;
  };
  window.addEventListener("online", update);
  window.addEventListener("offline", update);
  update();
  document.body.append(status);
}
