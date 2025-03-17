import './bootstrap';


document.addEventListener('DOMContentLoaded', () => {
    subscribeToNotifications();
    setInterval(fetchNewMessages, 1000);
});
