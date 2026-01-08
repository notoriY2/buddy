document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.start-chat').forEach(chatLink => {
    chatLink.addEventListener('click', async (event) => {
      event.preventDefault();
      const userId = chatLink.getAttribute('data-user-id');
      const chatPopup = await fetchChatPopup(userId);
      document.getElementById('chat-popup-placeholder').innerHTML = chatPopup;
      document.querySelector('.chat-popup').style.display = 'flex';

      document.querySelector('.chat-popup .close').addEventListener('click', () => {
        document.querySelector('.chat-popup').style.display = 'none';
      });

      // Load chat.js script
      const chatScript = document.createElement('script');
      chatScript.src = 'javascript/chat.js';
      document.body.appendChild(chatScript);
    });
  });
});

async function fetchChatPopup(userId) {
  const response = await fetch(`chat-popup.html?user_id=${userId}`);
  return await response.text();
}