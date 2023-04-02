import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  // @see https://vitejs.dev/config/build-options.html
  build: {
    sourcemap: true,
    // @see https://rollupjs.org/configuration-options
    // rollupOptions: {
    //   output: {
    //     dir: "assets",
    //     file: "hainguyen",
    //   }
    // },
  }
})
