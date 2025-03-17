import{subscribeToNotifications as k}from"./notification-BNuqCmH2.js";function _(e,s,t){document.querySelectorAll(".delete-message").forEach(a=>{a.addEventListener("click",async function(h){h.preventDefault();const i=this.getAttribute("data-message-id");if(i&&confirm("Вы уверены, что хотите удалить это сообщение?"))try{const o=await fetch(`/chats/${s}/${t}/messages/${i}`,{method:"DELETE",headers:{"X-CSRF-TOKEN":e,"Content-Type":"application/json",Accept:"application/json"}});if(!o.ok)throw new Error(`HTTP error! Status: ${o.status}`);const m=await o.json();if(m.success){const n=document.querySelector(`.message[data-id="${i}"]`);n&&n.remove()}else throw new Error(m.error||"Неизвестная ошибка")}catch(o){console.error("Ошибка при удалении сообщения:",o),alert("Не удалось удалить сообщение: "+o.message)}})}),document.querySelectorAll(".pin-message").forEach(a=>{a.addEventListener("click",async function(h){h.preventDefault();const i=this.getAttribute("data-message-id");if(!i)return;const o=this,m=o.parentNode,n=document.createElement("button");n.classList.add("unpin-message"),n.setAttribute("data-message-id",i),n.innerHTML='<img src="/img/icon/unpin.svg" alt="Открепить">',m.replaceChild(n,o),M(n,e,s,t);try{const c=await fetch(`/chats/${s}/${t}/messages/${i}/pin`,{method:"POST",headers:{"X-CSRF-TOKEN":e,"Content-Type":"application/json",Accept:"application/json"}});if(!c.ok)throw new Error(`HTTP error! Status: ${c.status}`);const r=await c.json();if(!r.message)throw new Error(r.error||"Неизвестная ошибка");typeof showToast=="function"?showToast("Сообщение закреплено"):console.log("Сообщение закреплено")}catch(c){console.error("Ошибка при закреплении сообщения:",c),alert("Не удалось закрепить сообщение: "+c.message);const r=document.querySelector(`.unpin-message[data-message-id="${i}"]`);if(r){const u=document.createElement("button");u.classList.add("pin-message"),u.setAttribute("data-message-id",i),u.innerHTML='<img src="/img/icon/pin.svg" alt="Закрепить">',r.parentNode.replaceChild(u,r)}}})}),document.querySelectorAll(".unpin-message").forEach(a=>{a.addEventListener("click",async function(h){h.preventDefault();const i=this.getAttribute("data-message-id");if(!i)return;const o=this,m=o.parentNode,n=document.createElement("button");n.classList.add("pin-message"),n.setAttribute("data-message-id",i),n.innerHTML='<img src="/img/icon/pin.svg" alt="Закрепить">',m.replaceChild(n,o);try{const c=await fetch(`/chats/${s}/${t}/messages/${i}/unpin`,{method:"POST",headers:{"X-CSRF-TOKEN":e,"Content-Type":"application/json",Accept:"application/json"}});if(!c.ok)throw new Error(`HTTP error! Status: ${c.status}`);const r=await c.json();if(!r.success)throw new Error(r.error||"Неизвестная ошибка")}catch(c){console.error("Ошибка при откреплении сообщения:",c),alert("Не удалось открепить сообщение: "+c.message);const r=document.querySelector(`.pin-message[data-message-id="${i}"]`);if(r){const u=document.createElement("button");u.classList.add("unpin-message"),u.setAttribute("data-message-id",i),u.innerHTML='<img src="/img/icon/unpin.svg" alt="Открепить">',r.parentNode.replaceChild(u,r),M(u,e,s,t)}}})})}function M(e,s,t,a){e.addEventListener("click",async function(h){h.preventDefault();const i=this.getAttribute("data-message-id");if(i){this.innerHTML='<img src="/img/icon/pin.svg" alt="Закрепить">',this.classList.add("processing-unpin");try{const o=await fetch(`/chats/${t}/${a}/messages/${i}/unpin`,{method:"POST",headers:{"X-CSRF-TOKEN":s,"Content-Type":"application/json",Accept:"application/json"}});if(!o.ok)throw new Error(`HTTP error! Status: ${o.status}`);const m=await o.json();if(m.success){const n=document.querySelector(`.message[data-message-id="${i}"]`);n&&(n.classList.remove("pinned"),e.classList.remove("processing-unpin"))}else throw new Error(m.error||"Неизвестная ошибка")}catch(o){console.error("Ошибка при откреплении сообщения:",o),alert("Не удалось открепить сообщение: "+o.message),this.innerHTML='<img src="/img/icon/unpin.svg" alt="Открепить">',this.classList.remove("processing-unpin")}}})}document.addEventListener("DOMContentLoaded",()=>{k(),setInterval(A,1e3)});function N(e){const s=e instanceof Date?e:new Date(e);if(isNaN(s))return"";const t=s.getHours().toString().padStart(2,"0"),a=s.getMinutes().toString().padStart(2,"0");return`${t}:${a}`}function b(e){if(!e)return"";const s=document.createElement("div");return s.textContent=e,s.innerHTML}function T(e=!0){const s=document.getElementById("chat-messages");s&&s.scrollTo({top:s.scrollHeight,behavior:e?"smooth":"auto"})}function j(e=!1){document.querySelectorAll("#chat-messages .message").forEach(t=>{e?t.style.display=t.classList.contains("pinned")?"":"none":t.style.display=""})}function q(e){if(e===0||!e)return"";const s=1024,t=["Байт","КБ","МБ","ГБ"],a=Math.floor(Math.log(e)/Math.log(s));return parseFloat((e/Math.pow(s,a)).toFixed(2))+" "+t[a]}function $(e,s,t,a,h,i){if(!e||e.length===0)return;const o=document.querySelector("#chat-messages ul");if(!o)return;let m=document.createDocumentFragment();e.forEach(n=>{if(t.has(n.id))return;t.add(n.id);const c=n.sender_id==s,r=document.createElement("li");r.className=`message ${c?"own":""}`,n.is_pinned&&r.classList.add("pinned"),r.setAttribute("data-id",n.id);const u=document.createElement("div");u.className="message-header";const y=document.createElement("span");y.className="sender-name",y.textContent=n.sender_name||"Пользователь";const L=document.createElement("span");L.className="message-time",L.textContent=N(n.created_at),u.appendChild(y),u.appendChild(L);const C=document.createElement("div");if(C.className="message-content",n.message&&n.message.trim()){const p=document.createElement("div");p.className="message-text",p.innerHTML=b(n.message).replace(/\n/g,"<br>"),C.appendChild(p)}if(n.attachments&&n.attachments.length>0){const p=document.createElement("div");p.className="attachments";const d=[],S=[];if(n.attachments.forEach(f=>{f&&(f.mime&&f.mime.startsWith("image/")?d.push(f):S.push(f))}),d.length>0){const f=document.createElement("div");f.className=`attachment-images images-${d.length>3?"grid":"row"}`,d.forEach(l=>{const w=document.createElement("div");w.className="image-container";const g=document.createElement("img");g.src=l.url,g.alt=l.original_file_name||"Изображение",g.className="attachment-image",g.loading="lazy",g.addEventListener("click",()=>{const E=document.createElement("div");E.className="image-modal",E.innerHTML=`
                            <div class="image-modal-content">
                                <img src="${l.url}" alt="${l.original_file_name||"Изображение"}">
                                <button class="close-modal">&times;</button>
                            </div>
                        `,document.body.appendChild(E),E.addEventListener("click",v=>{(v.target===E||v.target.classList.contains("close-modal"))&&E.remove()})}),w.appendChild(g),f.appendChild(w)}),p.appendChild(f)}if(S.length>0){const f=document.createElement("div");f.className="attachment-files",S.forEach(l=>{const w=document.createElement("a");w.href=l.url,w.className="attachment-file",w.target="_blank";let g="📄";l.mime&&(l.mime.includes("pdf")?g="📕":l.mime.includes("word")?g="📘":l.mime.includes("excel")||l.mime.includes("spreadsheet")?g="📊":l.mime.includes("zip")||l.mime.includes("rar")?g="🗂️":l.mime.includes("audio")?g="🎵":l.mime.includes("video")&&(g="🎬"));const E=l.original_file_name||"Файл";if(w.innerHTML=`<span class="file-icon">${g}</span> ${E}`,l.size){const v=document.createElement("span");v.className="file-size",v.textContent=q(l.size),w.appendChild(v)}f.appendChild(w)}),p.appendChild(f)}C.appendChild(p)}if(!n.is_system){const p=document.createElement("div");if(p.className="message-actions",c){const d=document.createElement("button");d.className="delete-message",d.setAttribute("data-message-id",n.id),d.innerHTML=`<img src="${window.deleteImgUrl||"/img/delete.svg"}" alt="Удалить">`,p.appendChild(d)}if(n.is_pinned){const d=document.createElement("button");d.className="unpin-message",d.setAttribute("data-message-id",n.id),d.innerHTML=`<img src="${window.unpinImgUrl||"/img/unpin.svg"}" alt="Открепить">`,p.appendChild(d)}else{const d=document.createElement("button");d.className="pin-message",d.setAttribute("data-message-id",n.id),d.innerHTML=`<img src="${window.pinImgUrl||"/img/pin.svg"}" alt="Закрепить">`,p.appendChild(d)}C.appendChild(p)}r.appendChild(u),r.appendChild(C),m.appendChild(r)}),o.appendChild(m),T(),_(a,h,i)}async function A(){var e,s,t;if(!(!window.currentChatId||!window.currentChatType))try{const a=(e=document.querySelector('meta[name="csrf-token"]'))==null?void 0:e.getAttribute("content"),h=new Date().getTime(),i=await fetch(`/chats/${window.currentChatType}/${window.currentChatId}/new-messages?t=${h}`,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":a,"Cache-Control":"no-cache, no-store, must-revalidate",Pragma:"no-cache",Expires:"0"},body:JSON.stringify({last_message_id:window.lastLoadedMessageId||0})});if(i.ok){const o=await i.json();if(o.messages&&o.messages.length>0){window.loadedMessageIds||(window.loadedMessageIds=new Set);let m=window.lastLoadedMessageId||0;if(o.messages.forEach(c=>{c.id>m&&(m=c.id)}),window.lastLoadedMessageId=m,!document.querySelector("#chat-messages ul")){console.error("Контейнер для сообщений не найден!");return}$(o.messages,(t=(s=window.Laravel)==null?void 0:s.user)==null?void 0:t.id,window.loadedMessageIds,a,window.currentChatType,window.currentChatId),T(),H(window.currentChatId,window.currentChatType)}}else console.error("Ошибка получения новых сообщений:",i.status,i.statusText)}catch(a){console.error("Ошибка при получении новых сообщений:",a)}}function H(e,s){var t;!e||!s||fetch(`/chats/${s}/${e}/mark-read`,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":(t=document.querySelector('meta[name="csrf-token"]'))==null?void 0:t.getAttribute("content"),Accept:"application/json"}}).catch(a=>console.error("Ошибка при пометке сообщений как прочитанных:",a))}function I(e,s){if(!window.Echo){console.warn("Laravel Echo не инициализирован");return}try{window.Echo.private(`chat.${s}.${e}`).listen(".message.sent",t=>{if(console.log("Новое сообщение WebSocket:",t),t.message&&!document.querySelector(`.message[data-id="${t.message.id}"]`)&&document.querySelector("#chat-messages ul")){const h=new Set,i=document.querySelector('meta[name="csrf-token"]').getAttribute("content");$([t.message],window.Laravel.user.id,h,i,s,e)}}).listen(".message.deleted",t=>{if(t.message_id){const a=document.querySelector(`.message[data-id="${t.message_id}"]`);a&&a.remove()}}),window.Echo.private(`typing.${s}.${e}`).listenForWhisper("typing",t=>{const a=document.getElementById("typing-indicator");a&&(a.textContent=`${t.name} печатает...`,a.style.display="block",setTimeout(()=>{a.style.display="none"},3e3))})}catch(t){console.error("Ошибка при настройке WebSocket для чата:",t)}}const B=Object.freeze(Object.defineProperty({__proto__:null,escapeHtml:b,fetchNewMessages:A,filterMessages:j,formatTime:N,initChatWebSockets:I,markMessagesAsRead:H,renderMessages:$,scrollToBottom:T},Symbol.toStringTag,{value:"Module"}));export{j as a,B as c,A as f,$ as r};
