import { initializeApp, getApps, getApp } from 'firebase/app';

const firebaseConfig = {
  apiKey: import.meta.env.VITE_FIREBASE_API_KEY || 'AIzaSyB6N1n8dW95YGMMuTsZMRnJY1En7lK2s2M',
  authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN || 'dlk-diz.firebaseapp.com',
  projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID || 'dlk-diz.firebasestorage.app',
  storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET || 'dlk-diz.firebasestorage.app',
  messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID || '209164982906',
  appId: import.meta.env.VITE_FIREBASE_APP_ID || '1:209164982906:web:0836fbb02e7effd80679c3',
};

const app = !getApps().length ? initializeApp(firebaseConfig) : getApp();

export default app;
