import { initializeApp } from 'firebase/app';

const firebaseConfig = {
    apiKey: "AIzaSyB6N1n8dW95YGMMuTsZMRnJY1En7lK2s2M",
    authDomain: "dlk-diz.firebaseapp.com",
    projectId: "dlk-diz",
    storageBucket: "dlk-diz.firebasestorage.app",
    messagingSenderId: "209164982906",
    appId: "1:209164982906:web:0836fbb02e7effd80679c3"
};

const app = initializeApp(firebaseConfig);

export default app;
