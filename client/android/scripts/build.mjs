import { build } from "../../node_modules/vite/dist/node/index.js";
import { fileURLToPath } from "node:url";
try {
  await build({
    configFile: fileURLToPath(new URL("../vite.config.mjs", import.meta.url)),
  });
  console.log("Web Android selesai. Tidak ada SDK/Gradle/APK yang dijalankan.");
} catch (error) {
  console.error(error instanceof Error ? error.message : "Build web gagal.");
  process.exitCode = 1;
}
