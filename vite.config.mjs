import { defineConfig } from 'vite'

export default defineConfig({
  build: {
    outDir: 'js',
    emptyOutDir: false,
    rollupOptions: {
      input: {
        'admin-settings': 'src/admin-settings.js',
      },
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: '[name]-[hash].js',
        assetFileNames: '[name].[ext]',
      },
      external: ['OC', 'OCP', 'OCA'],
    },
    minify: false,
    sourcemap: true,
  },
})