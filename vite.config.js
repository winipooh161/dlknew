// vite.config.js

import path from 'path';

export default async () => {
  const { defineConfig } = await import('vite');
  const vue = await import('@vitejs/plugin-vue');
  const laravel = await import('laravel-vite-plugin');

  return defineConfig({
    plugins: [
      vue.default(),
      laravel.default({
        input: [
          'resources/js/bootstrap.js',
          'resources/js/notification.js',
          'resources/js/chat-main.js',
          'resources/js/message-actions.js',
          'resources/js/emoji-picker.js',
          'resources/js/chat-utils.js',
          'resources/js/chat.js',
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
          'public/css/animate.css',
        ],
        refresh: true,
      }),
    ],
    resolve: {
      alias: {
        '@': path.resolve(__dirname, './resources/js'),
        'firebase/app': path.resolve(__dirname, './node_modules/firebase/app'),
        'firebase/messaging': path.resolve(__dirname, './node_modules/firebase/messaging')
      }
    },
    server: {
      hmr: {
        overlay: false
      },
      cors: {
        origin: 'https://dlk',
        credentials: true
      }
    }
  });
};
