import { initializeApp } from "https://www.gstatic.com/firebasejs/9.22.1/firebase-app.js";
import { getFirestore } from "https://www.gstatic.com/firebasejs/9.22.1/firebase-firestore.js";
import { getMessaging } from "https://www.gstatic.com/firebasejs/9.22.1/firebase-messaging.js";

// Конфигурация Firebase можно передать через Blade-шаблон
const firebaseConfig = {
  apiKey: "AIzaSyB6N1n8dW95YGMMuTsZMRnJY1En7lK2s2M",
  authDomain: "dlk-diz.firebaseapp.com",
  projectId: "dlk-diz",
  storageBucket: "dlk-diz.firebasestorage.app",
  messagingSenderId: "209164982906",
  appId: "1:209164982906:web:0836fbb02e7effd80679c3"
};

const app = initializeApp(firebaseConfig);
const firestore = getFirestore(app);

let messaging;
try {
  messaging = getMessaging(app);
} catch (error) {
  console.warn("Firebase messaging is not available", error);
}

window.firebaseApp = app;
window.firestore = firestore;
window.messaging = messaging;

export default app;
