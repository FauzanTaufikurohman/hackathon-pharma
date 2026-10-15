import { fileURLToPath } from "node:url";
import { resolve } from "node:path";
import { createRequire } from "node:module";
import vue from "../node_modules/@vitejs/plugin-vue/dist/index.mjs";
import tailwindConfig from "../tailwind.config.js";
import {
  androidRoot,
  clientRoot,
  getApiUrl,
  isSharedApi,
} from "./scripts/config.mjs";

const require = createRequire(new URL("../package.json", import.meta.url));

export default {
  root: clientRoot,
  // do not load the web's .env.local (which can point to laptop-only localhost).
  envDir: androidRoot,
  plugins: [
    {
      name: "pharma-mobile-entry",
      enforce: "pre",
      transformIndexHtml: {
        order: "pre",
        handler: (html) =>
          html
            .replace('src="/src/main.ts"', 'src="/android/entry.ts"')
            .replace(
              "initial-scale=1.0",
              "initial-scale=1.0, viewport-fit=cover",
            ),
      },
      resolveId(source, importer) {
        if (isSharedApi(source, importer))
          return resolve(androidRoot, "api.ts");
      },
    },
    vue(),
  ],
  resolve: { alias: { "@": resolve(clientRoot, "src") } },
  css: {
    postcss: {
      plugins: [
        require("tailwindcss")({
          ...tailwindConfig,
          content: [
            resolve(clientRoot, "index.html"),
            resolve(clientRoot, "src/**/*.{vue,js,ts,jsx,tsx}"),
          ],
        }),
        require("autoprefixer")(),
      ],
    },
  },
  define: { "import.meta.env.VITE_API_BASE_URL": JSON.stringify(getApiUrl()) },
  build: {
    outDir: resolve(androidRoot, "www"),
    emptyOutDir: true,
    sourcemap: false,
  },
  preview: { port: 5188, strictPort: true },
};
