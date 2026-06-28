import { fileURLToPath } from 'node:url'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vite'

export default defineConfig({
  server: {
    host: '10.10.100.189',
    port: 5179,
  },
  plugins: [
    vue(),
    laravel({
      input: ['resources/ts/main.ts'],
      refresh: true,
    }),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./resources/ts', import.meta.url)),
    },
  },
})
