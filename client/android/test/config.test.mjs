import { test } from "node:test";
import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import { validateApiUrl, isSharedApi } from "../scripts/config.mjs";
import {
  safeFilename,
  externalWebUrl,
  locationErrorCode,
  expiredReportNames,
} from "../policies.mjs";

test("cache cleanup only removes expired app reports, retaining complete filenames", () => {
  const now = 1800000000000;
  const long = "1799900000000-" + "x".repeat(96) + ".pdf";
  assert.deepEqual(
    expiredReportNames(
      [
        { name: long, type: "file", mtime: now - 90000000 },
        { name: "1799999999999-new.pdf", type: "file", mtime: now },
        { name: "../private", type: "file", mtime: 0 },
        { name: "1799900000000-dir", type: "directory", mtime: 0 },
        { name: "other.pdf", type: "file", mtime: 0 },
      ],
      now,
    ),
    [long],
  );
});

test("public HTTPS API endpoint is mandatory", () => {
  assert.equal(
    validateApiUrl("https://api.pharma.test/api/v1/"),
    "https://api.pharma.test/api/v1",
  );
  for (const url of [
    "",
    "/api/v1",
    "http://server.test/api/v1",
    "https://localhost/api/v1",
    "https://127.0.0.1/api/v1",
    "https://u:p@server.test/api/v1",
    "https://server.test/api/v1?key=secret",
    "https://server.test/wrong",
    "https://api-contoh.example.com/api/v1",
  ]) {
    assert.throws(() => validateApiUrl(url), undefined, url);
  }
});
test("only mobile imports use the native download adapter, without a circular import", () => {
  assert.equal(
    isSharedApi("../../services/api", "/client/src/views/Test.vue"),
    true,
  );
  assert.equal(
    isSharedApi("@/services/api", "/client/src/views/Test.vue"),
    true,
  );
  assert.equal(
    isSharedApi("../src/services/api", "/client/android/api.ts"),
    false,
  );
  assert.equal(isSharedApi("./auth", "/client/src/services/api.ts"), false);
});
test("report filenames cannot escape the cache directory", () => {
  for (const name of [
    "../../private.pdf",
    "laporan paket.pdf",
    "",
    "..",
    "x".repeat(400),
  ]) {
    const safe = safeFilename(name);
    assert.match(safe, /^[a-zA-Z0-9_-][a-zA-Z0-9._-]*$/);
    assert.ok(safe.length <= 100);
  }
});
test("native browser only accepts external HTTPS links", () => {
  assert.equal(externalWebUrl("/paket-saya", "https://localhost"), null);
  assert.equal(
    externalWebUrl("javascript:alert(1)", "https://localhost"),
    null,
  );
  assert.equal(
    externalWebUrl("https://u:p@test.org", "https://localhost"),
    null,
  );
  assert.equal(
    externalWebUrl("https://telegram.org/", "https://localhost"),
    "https://telegram.org/",
  );
});
test("GPS denial and timeout preserve browser-compatible error codes", () => {
  assert.equal(locationErrorCode("OS-PLUG-GLOC-0003"), 1);
  assert.equal(locationErrorCode("OS-PLUG-GLOC-0010"), 3);
  assert.equal(locationErrorCode("unknown"), 2);
});
test("bundled app uses HTTPS local assets, not a remote server URL or cleartext", () => {
  const config = JSON.parse(
    readFileSync(new URL("../capacitor.config.json", import.meta.url)),
  );
  assert.equal(config.android.path, "native");
  assert.equal(config.webDir, "www");
  assert.equal(config.server.androidScheme, "https");
  assert.equal(config.server.cleartext, false);
  assert.equal(config.android.allowMixedContent, false);
  assert.equal(config.server.url, undefined);
});
test("native manifest keeps GPS optional and disables backups/cleartext", () => {
  const manifest = readFileSync(
    new URL("../native/app/src/main/AndroidManifest.xml", import.meta.url),
    "utf8",
  );
  assert.match(manifest, /android:allowBackup="false"/);
  assert.match(manifest, /android:usesCleartextTraffic="false"/);
  assert.match(manifest, /ACCESS_FINE_LOCATION/);
  assert.match(
    manifest,
    /android.hardware.location.gps" android:required="false"/,
  );
  assert.doesNotMatch(
    manifest,
    /MANAGE_EXTERNAL_STORAGE|READ_EXTERNAL_STORAGE/,
  );
  const paths = readFileSync(
    new URL("../native/app/src/main/res/xml/file_paths.xml", import.meta.url),
    "utf8",
  );
  assert.match(paths, /path="pharma-reports\/"/);
  assert.doesNotMatch(paths, /<external-path|path="\."/);
});
