$(document).ready(function() {
    // ...existing code...

    // Обработчик события keyup для индикации набора текста
    $('#chat-message').on('keyup', function() {
        let chatType = currentChatType;
        let chatId = currentChatId;
        if (chatType && chatId) {
            window.startTyping(chatType, chatId);
        }
    });
    // ...existing code...
});
