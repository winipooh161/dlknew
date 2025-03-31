// vite.config.js

import path from 'path';
import vue from '@vitejs/plugin-vue';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [
    vue(),
    laravel({
      input: [
        'resources/js/bootstrap.js',
        'resources/css/style.css',
        'resources/css/font.css',
        'resources/css/element.css',
        'resources/css/animation.css',
        'resources/css/mobile.css',
        'resources/js/modal.js',
        'resources/js/success.js',
        'resources/js/login.js',
        'resources/js/mask.js',
        'public/js/jquery-3.6.0.min.js',
        'public/js/wow.js',
        'public/css/animate.css'
      ],
      refresh: true,
    })
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js')
    }
  },
  server: {
    hmr: {
      overlay: false
    },
    cors: {
      origin: 'https://lk',
      credentials: true
    }
  },
  define: {
    'process.env': {}
  }
});
