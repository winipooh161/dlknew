export function initializeEmojiPicker(textarea) {
    const container = textarea.parentElement;
    const emojiButton = document.createElement('button');
    const emojiPicker = document.createElement('div');
    emojiButton.textContent = "😉";
    emojiButton.type = "button";
    emojiButton.classList.add('emoji-button');
    emojiPicker.classList.add('emoji-picker');
    emojiPicker.style.position = 'absolute';
    emojiPicker.style.bottom = '50px';
    emojiPicker.style.left = '10px';
    const emojis = [
        "😀","😁","😂","🤣","😃","😄","😅","😆","😉","😊","😍","😘","😜","😎","😭","😡",
        "😇","😈","🙃","🤔","😥","😓","🤩","🥳","🤯","🤬","🤡","👻","💀","👽","🤖","🎃",
        "🐱","🐶","🐭","🐹","🐰","🦊","🐻","🐼","🦁","🐮","🐷","🐸","🐵","🐔","🐧","🐦",
        "🌹","🌻","🌺","🌷","🌼","🍎","🍓","🍒","🍇","🍉","🍋","🍊","🍌","🥝","🍍","🥭"
    ];
    let emojiHTML = '';
    emojis.forEach(emoji => { emojiHTML += `<span class="emoji-item">${emoji}</span>`; });
    emojiPicker.innerHTML = emojiHTML;
    emojiPicker.addEventListener('click', (e) => {
        if (e.target.classList.contains('emoji-item')) {
            const emoji = e.target.textContent;
            const cursorPos = textarea.selectionStart;
            const textBefore = textarea.value.substring(0, cursorPos);
            const textAfter = textarea.value.substring(cursorPos);
            textarea.value = textBefore + emoji + textAfter;
            const newPos = cursorPos + emoji.length;
            textarea.selectionStart = newPos;
            textarea.selectionEnd = newPos;
            textarea.focus();
        }
    });
    container.appendChild(emojiButton);
    container.appendChild(emojiPicker);
    emojiPicker.style.display = "none";
    emojiButton.addEventListener('click', (event) => {
        event.stopPropagation();
        emojiPicker.style.display = (emojiPicker.style.display === "none") ? "flex" : "none";
    });
    document.addEventListener('click', (event) => {
        if (!emojiPicker.contains(event.target) && !emojiButton.contains(event.target)) {
            emojiPicker.style.display = "none";
        }
    });
}
