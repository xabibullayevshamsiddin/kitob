import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  base: './',
  plugins: [react(), tailwindcss()],
  // Keep the parent Laravel app's Tailwind v3 PostCSS pipeline out of this isolated Tailwind v4 build.
  css: { postcss: { plugins: [] } },
  build: {
    outDir: '../public/offline',
    emptyOutDir: true,
    manifest: true,
    assetsDir: 'assets',
    cssCodeSplit: false,
    rollupOptions: {
      input: 'index.html',
      output: {
        manualChunks: undefined,
      },
    },
  },
});
