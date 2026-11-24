import tailwindcss from '@tailwindcss/vite'
import react from '@vitejs/plugin-react'
import { defineConfig } from 'vite'

// https://vite.dev/config/
export default defineConfig(({ command }) => ({
  // Прод: файлы собираются в /dist/ (nginx раздаёт их оттуда).
  // Dev: приложение остаётся в корне — http://localhost:5173/
  base: command === 'build' ? '/dist/' : '/',
  plugins: [react(), tailwindcss()],
  server: {
    port: 5173,
  },
  build: {
    // Сборка в отдельный каталог: рядом лежат index.php и health.php,
    // которые мы не должны затирать.
    outDir: '../app/public/dist',
    emptyOutDir: true,
  },
}))
