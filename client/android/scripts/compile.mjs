// this command is for the teammate's SDK-equipped computer, never run by install/sync.
import { spawnSync } from "node:child_process";
import { fileURLToPath } from "node:url";
if (process.env.ANDROID_ALLOW_COMPILE !== "yes") {
  console.error(
    "Compile sengaja dikunci. Di laptop dengan SDK, jalankan npm run sync lalu set ANDROID_ALLOW_COMPILE=yes. Lihat how_to.md.",
  );
  process.exit(1);
}
const windows = process.platform === "win32";
const result = spawnSync(
  windows ? "cmd.exe" : "./gradlew",
  windows ? ["/d", "/c", "gradlew.bat assembleDebug"] : ["assembleDebug"],
  {
    cwd: fileURLToPath(new URL("../native/", import.meta.url)),
    stdio: "inherit",
  },
);
if (result.error) console.error(result.error.message);
process.exitCode = result.status ?? 1;
