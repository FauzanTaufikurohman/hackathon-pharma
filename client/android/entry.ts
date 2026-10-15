import { Capacitor } from "@capacitor/core";
import "./mobile.css";
import { setupNative } from "./native";

// install the GPS bridge before the shared Vue application starts.
if (Capacitor.isNativePlatform()) {
  document.documentElement.classList.add("pharma-android");
  try {
    await setupNative();
  } catch {
    // a native plugin failure must not prevent login or the Vue UI from opening.
    console.warn("Fitur perangkat belum siap. Aplikasi tetap dibuka.");
  }
}
await import("../src/main");
