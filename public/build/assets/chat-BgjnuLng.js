import{r as f,f as q,c as A,a as x}from"./notification-BPHaAjEO.js";import"./firebase-init-B_RFWMUP.js";import"https://www.gstatic.com/firebasejs/9.22.1/firebase-app.js";import"https://www.gstatic.com/firebasejs/9.22.1/firebase-firestore.js";import"https://www.gstatic.com/firebasejs/9.22.1/firebase-messaging.js";import"./index.esm2017-fTSNeBOI.js";function P(l){const c=l.parentElement,r=document.createElement("button"),i=document.createElement("div");r.textContent="😉",r.type="button",r.classList.add("emoji-button"),i.classList.add("emoji-picker"),i.style.position="absolute",i.style.bottom="50px",i.style.left="10px";const h=["😀","😁","😂","🤣","😃","😄","😅","😆","😉","😊","😍","😘","😜","😎","😭","😡","😇","😈","🙃","🤔","😥","😓","🤩","🥳","🤯","🤬","🤡","👻","💀","👽","🤖","🎃","🐱","🐶","🐭","🐹","🐰","🦊","🐻","🐼","🦁","🐮","🐷","🐸","🐵","🐔","🐧","🐦","🌹","🌻","🌺","🌷","🌼","🍎","🍓","🍒","🍇","🍉","🍋","🍊","🍌","🥝","🍍","🥭"];let u="";h.forEach(d=>{u+=`<span class="emoji-item">${d}</span>`}),i.innerHTML=u,i.addEventListener("click",d=>{if(d.target.classList.contains("emoji-item")){const p=d.target.textContent,y=l.selectionStart,v=l.value.substring(0,y),E=l.value.substring(y);l.value=v+p+E;const m=y+p.length;l.selectionStart=m,l.selectionEnd=m,l.focus()}}),c.appendChild(r),c.appendChild(i),i.style.display="none",r.addEventListener("click",d=>{d.stopPropagation(),i.style.display=i.style.display==="none"?"flex":"none"}),document.addEventListener("click",d=>{!i.contains(d.target)&&!r.contains(d.target)&&(i.style.display="none")})}document.addEventListener("DOMContentLoaded",()=>{var k;let l=document.querySelector(".chat-container")||document.getElementById("chat-container"),c,r;!l||!l.dataset.chatId||!l.dataset.chatType?(console.warn("Chat container не найден или отсутствуют параметры чата, используются значения по умолчанию."),c="0",r="group"):(c=l.dataset.chatId,r=l.dataset.chatType);const i=window.Laravel.user.id,h=document.querySelector('meta[name="csrf-token"]').getAttribute("content");let u=new Set,d=!1;async function p(s,e={}){try{const n=await fetch(s,e);if(!n.ok){const t=await n.text();throw s.includes("build/manifest.json")&&console.error("Vite manifest не найден. Проверьте, что вы выполнили сборку (npm run dev или npm run build)."),new Error(`HTTP ${n.status}: ${t}`)}return await n.json()}catch(n){throw console.error("Fetch error:",n),n}}function y(s){if(!window.firestore)return;const e=window.firestore.collection?window.firestore.collection(`chats/${s}/messages`):null;if(!e){console.warn("Firestore коллекция не доступна");return}e.orderBy("created_at").onSnapshot(t=>{let o=[];t.docChanges().forEach(a=>{if(a.type==="added"){const L=a.doc.data();u.has(L.id)||o.push(L)}}),o.length>0&&(f(o,window.Laravel.user.id,u,h,r,c),m(c,r))},t=>{console.error("Ошибка подписки на Firestore обновления:",t)})}function v(s,e){c=s,r=e;const t=document.getElementById("chat-messages").querySelector("ul");t.innerHTML="",u.clear();const o=document.querySelector(`[data-chat-id="${s}"][data-chat-type="${e}"] h5`);document.getElementById("chat-header").textContent=o?o.textContent:"Выберите чат для общения",p(`/chats/${e}/${s}/messages`).then(a=>{a.messages&&a.messages.length>0&&(window.lastLoadedMessageId=a.messages[a.messages.length-1].id),f(a.messages,i,u,h,r,c),m(s,e),O(s,e),y(s)}).catch(a=>console.error("Ошибка загрузки сообщений:",a))}async function E(){if(!c||!g.value.trim()&&!document.querySelector(".file-input").files[0])return;S.disabled=!0;const s=g.value.trim(),e=document.querySelector(".file-input"),n=e.files;let t=new FormData;t.append("message",s);for(let o=0;o<n.length;o++)t.append("attachments[]",n[o]);try{const o=await fetch(`/chats/${r}/${c}/messages`,{method:"POST",headers:{"X-CSRF-TOKEN":h},body:t});let a;if((o.headers.get("content-type")||"").indexOf("application/json")!==-1)a=await o.json();else{const j=await o.text();if(j.trim().substr(0,4)==="<!--"){alert("Произошла ошибка авторизации. Перезагрузите страницу."),window.location.reload();return}throw new Error(`Unexpected response: ${j}`)}a.message?(f([a.message],a.message.sender_id,u,h,r,c),window.lastLoadedMessageId=a.message.id,g.value="",e.value=""):alert(a.error||"Ошибка при отправке сообщения")}catch(o){console.error("Ошибка при отправке сообщения:",o),alert("Ошибка при отправке сообщения: "+o.message)}finally{S.disabled=!1}}function m(s,e){fetch(`/chats/${e}/${s}/mark-read`,{method:"POST",headers:{"X-CSRF-TOKEN":h}}).catch(n=>console.error("Ошибка при пометке сообщений как прочитанных:",n))}function O(s,e){window.Echo&&(window.Echo.channel(`chat.${e}.${s}`).listen("MessageSent",n=>{console.log("Новое сообщение через веб-сокет:",n),u.has(n.message.id)||(f([n.message],i,u,h,r,c),m(s,e))}),window.Echo.connector.socket.on("error",n=>{console.error("WebSocket Error:",n)}),window.Echo.connector.socket.on("reconnect_attempt",()=>{console.log("Попытка переподключения к WebSocket...")}))}setInterval(()=>{if(c&&r){const s=document.getElementById("chat-messages");if(!s||!s.querySelector("ul"))return;const n=window.lastLoadedMessageId||0;fetch(`/chats/${r}/${c}/new-messages`,{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json","X-CSRF-TOKEN":h},body:JSON.stringify({last_message_id:n})}).then(t=>{const o=t.headers.get("content-type");return o&&o.indexOf("application/json")!==-1?t.json():t.text().then(a=>{throw new Error(a)})}).then(t=>{if(t.messages&&t.messages.length>0){const o=t.messages[t.messages.length-1];window.lastLoadedMessageId=o.id,f(t.messages,t.current_user_id,u,h,r,c),m(c,r)}}).catch(t=>{console.error("Ошибка при получении новых сообщений:",t)})}},2e3),setInterval(()=>{c&&r&&fetch(`/chats/${r}/${c}/mark-delivered`,{method:"POST",headers:{"X-CSRF-TOKEN":h}}).catch(s=>console.error("Ошибка при обновлении статуса доставки:",s))},5e3);function B(s,e){let n;return function(...t){clearTimeout(n),n=setTimeout(()=>s.apply(this,t),e)}}const T=document.getElementById("chat-list");T&&T.addEventListener("click",s=>{const e=s.target.closest("li");if(!e)return;const n=e.getAttribute("data-chat-id"),t=e.getAttribute("data-chat-type");c===n&&r===t||v(n,t)});const C=document.getElementById("search-chats"),w=document.getElementById("search-results");C&&C.addEventListener("input",B(function(){const s=C.value.trim().toLowerCase();s===""?(w.style.display="none",Array.from(chatList.children).forEach(e=>{e.style.display="flex"})):(Array.from(chatList.children).forEach(e=>{const n=e.querySelector("h5").textContent.toLowerCase();e.style.display=n.includes(s)?"flex":"none"}),fetch("/chats/search",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":h},body:JSON.stringify({query:s})}).then(e=>e.json()).then(e=>{let n="";e.chats&&e.chats.length>0&&(n+="<h5>Чаты</h5><ul>",e.chats.forEach(t=>{n+=`<li data-chat-id="${t.id}" data-chat-type="${t.type}">${t.name}</li>`}),n+="</ul>"),e.messages&&e.messages.length>0&&(n+="<h5>Сообщения</h5><ul>",e.messages.forEach(t=>{let o=t.chat_id,a="group";o||(a="personal",o=t.sender_id==i?t.receiver_id:t.sender_id),n+=`<li data-chat-id="${o}" data-chat-type="${a}" data-message-id="${t.id}">
                                <strong>${t.sender_name}:</strong> ${t.message.substring(0,50)}...
                                <br><small>${x(t.created_at)}</small>
                            </li>`}),n+="</ul>"),w.innerHTML=n,w.style.display=n.trim()===""?"none":"block",Array.from(w.querySelectorAll("li")).forEach(t=>{t.addEventListener("click",function(){const o=this.getAttribute("data-chat-id"),a=this.getAttribute("data-chat-type"),L=this.getAttribute("data-message-id");v(o,a),C.value="",w.style.display="none",L&&setTimeout(()=>{},1e3)})})}).catch(e=>console.error("Ошибка поиска:",e)))},300));function I(){const s=document.querySelector(".attach-file"),e=document.querySelector(".file-input");s&&e&&(s.addEventListener("click",()=>{e.click()}),e.addEventListener("change",()=>{e.files.length>0&&E()}))}document.readyState!=="loading"?I():document.addEventListener("DOMContentLoaded",I);const S=document.getElementById("send-message"),g=document.getElementById("chat-message");S&&S.addEventListener("click",E),g&&g.addEventListener("keypress",s=>{s.key==="Enter"&&!s.shiftKey&&(s.preventDefault(),E())}),P(g);const $=(k=document.getElementById("chat-list"))==null?void 0:k.querySelector("li");$&&$.click();const M=document.getElementById("toggle-pinned");M&&M.addEventListener("click",()=>{d=!d,M.textContent=d?"Показать все сообщения":"Показать только закрепленные",q(d)}),A();const b=document.getElementById("chat-messages");if(b){const s=document.createElement("div");s.id="scroll-sentinel",b.appendChild(s),new IntersectionObserver(n=>{n.forEach(t=>{if(t.isIntersecting){const o=parseInt(b.getAttribute("data-page")||"1");c&&r?p(`/chats/${r}/${c}/messages?page=${o+1}`).then(a=>{a.messages&&a.messages.length>0&&(f(a.messages,i,u,h,r,c),b.setAttribute("data-page",o+1))}).catch(a=>console.error("Ошибка подгрузки старых сообщений:",a)):console.warn("Неверные параметры чата: currentChatId или currentChatType не установлены")}})},{root:b,threshold:1}).observe(s)}});
