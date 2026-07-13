@php
    $chatbotAskEndpoint = route('chatbot.ask');
    $chatbotStartEndpoint = route('chatbot.sessions.start');
    $chatbotHistoryBase = url('/chatbot/sessions');
    $chatbotFeedbackBase = url('/chatbot/messages');
@endphp

<div id="crypto-chatbot-widget" class="fixed bottom-5 right-5 z-50">
    <div
        id="chatbot-window"
        class="hidden mb-4 w-[calc(100vw-2.5rem)] sm:w-[440px] max-h-[720px] rounded-[2rem] border border-white/10 bg-slate-950/95 shadow-2xl shadow-cyan-950/40 backdrop-blur-xl overflow-hidden"
    >
        <div class="relative overflow-hidden border-b border-white/10 p-5">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-16 -right-16 h-40 w-40 rounded-full bg-cyan-500/20 blur-3xl"></div>
                <div class="absolute -bottom-16 -left-16 h-40 w-40 rounded-full bg-indigo-500/20 blur-3xl"></div>
            </div>

            <div class="relative flex items-start justify-between gap-4">
                <div class="flex items-start gap-3 min-w-0">
                    <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-500 flex items-center justify-center text-2xl shadow-lg shadow-cyan-500/20 shrink-0">
                        🤖
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-white truncate">
                                Crypto AI Assistant
                            </h3>

                            <span class="inline-flex items-center gap-1 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-2 py-0.5 text-[11px] font-bold text-emerald-200 shrink-0">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                                AI + RAG
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-slate-400">
                            Hiểu ngữ cảnh, đọc Blog/News và tra dữ liệu giá coin.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button
                        id="chatbot-new-session"
                        type="button"
                        title="Cuộc trò chuyện mới"
                        class="h-9 w-9 rounded-xl border border-white/10 bg-white/[0.04] text-slate-300 hover:text-white hover:bg-white/10 transition"
                    >
                        ＋
                    </button>

                    <button
                        id="chatbot-close"
                        type="button"
                        class="h-9 w-9 rounded-xl border border-white/10 bg-white/[0.04] text-slate-300 hover:text-white hover:bg-white/10 transition"
                    >
                        ✕
                    </button>
                </div>
            </div>
        </div>

        <div
            id="chatbot-messages"
            class="h-[440px] overflow-y-auto p-5 space-y-4 scroll-smooth"
        ></div>

        <div class="border-t border-white/10 p-4">
            <form id="chatbot-form" class="space-y-3">
                <div class="flex items-end gap-2">
                    <textarea
                        id="chatbot-input"
                        rows="1"
                        maxlength="2000"
                        class="min-h-[48px] max-h-28 flex-1 resize-none rounded-2xl border border-white/10 bg-slate-900/80 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-400 focus:ring-cyan-400/30"
                        placeholder="Hỏi về Bitcoin, tin mới, một bài blog hoặc tiếp tục câu trước..."
                        required
                    ></textarea>

                    <button
                        id="chatbot-submit"
                        type="submit"
                        class="h-12 w-12 rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 text-white font-black shadow-lg shadow-cyan-500/20 hover:from-indigo-400 hover:to-cyan-400 transition flex items-center justify-center"
                    >
                        ➤
                    </button>
                </div>

                <div class="flex items-center justify-between gap-3 text-[11px] text-slate-500 leading-5">
                    <span>AI có thể sai. Hãy kiểm tra các nguồn được đính kèm.</span>
                    <span id="chatbot-character-count">0 / 2000</span>
                </div>
            </form>
        </div>
    </div>

    <button
        id="chatbot-toggle"
        type="button"
        class="group relative h-16 w-16 rounded-3xl bg-gradient-to-br from-indigo-500 to-cyan-500 text-white shadow-2xl shadow-cyan-500/30 hover:scale-105 transition flex items-center justify-center"
    >
        <span class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-emerald-400 border-2 border-slate-950 shadow-[0_0_14px_rgba(52,211,153,0.9)]"></span>
        <span id="chatbot-toggle-icon" class="text-2xl">💬</span>
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleButton = document.getElementById('chatbot-toggle');
        const closeButton = document.getElementById('chatbot-close');
        const newSessionButton = document.getElementById('chatbot-new-session');
        const windowBox = document.getElementById('chatbot-window');
        const messagesBox = document.getElementById('chatbot-messages');
        const form = document.getElementById('chatbot-form');
        const input = document.getElementById('chatbot-input');
        const submitButton = document.getElementById('chatbot-submit');
        const toggleIcon = document.getElementById('chatbot-toggle-icon');
        const characterCount = document.getElementById('chatbot-character-count');

        if (!toggleButton || !windowBox || !messagesBox || !form || !input || !submitButton) {
            return;
        }

        const askEndpoint = @json($chatbotAskEndpoint);
        const startEndpoint = @json($chatbotStartEndpoint);
        const historyBase = @json($chatbotHistoryBase);
        const feedbackBase = @json($chatbotFeedbackBase);
        const csrfToken = @json(csrf_token());
        const storageKey = 'cryptoblog_chatbot_session_uuid';

        let sessionUuid = localStorage.getItem(storageKey) || null;
        let historyLoaded = false;
        let isLoading = false;

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function nl2br(value) {
            return escapeHtml(value).replace(/\n/g, '<br>');
        }

        function scrollToBottom() {
            messagesBox.scrollTop = messagesBox.scrollHeight;
        }

        function addWelcome() {
            messagesBox.innerHTML = '';

            const wrapper = document.createElement('div');
            wrapper.className = 'space-y-3';
            wrapper.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="h-9 w-9 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-500 flex items-center justify-center text-sm shrink-0">🤖</div>
                    <div class="max-w-[86%] rounded-3xl rounded-tl-lg border border-white/10 bg-white/[0.06] px-4 py-3">
                        <p class="text-sm text-slate-200 leading-6">
                            Xin chào! Mình có thể nhớ nội dung trong phiên hiện tại, tìm Blog/News liên quan và tra dữ liệu coin từ website.
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-2 pl-12">
                    <button type="button" class="chatbot-suggestion text-left rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3 text-xs text-slate-300 hover:bg-cyan-400/10 hover:text-cyan-100 transition" data-message="Website có tin Bitcoin mới nào?">Website có tin Bitcoin mới nào?</button>
                    <button type="button" class="chatbot-suggestion text-left rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3 text-xs text-slate-300 hover:bg-cyan-400/10 hover:text-cyan-100 transition" data-message="Giá BTC hiện tại là bao nhiêu?">Giá BTC hiện tại là bao nhiêu?</button>
                    <button type="button" class="chatbot-suggestion text-left rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3 text-xs text-slate-300 hover:bg-cyan-400/10 hover:text-cyan-100 transition" data-message="Làm sao để trở thành tác giả?">Làm sao để trở thành tác giả?</button>
                </div>
            `;

            messagesBox.appendChild(wrapper);
            scrollToBottom();
        }

        function addUserMessage(message) {
            const wrapper = document.createElement('div');
            wrapper.className = 'flex items-start justify-end gap-3';
            wrapper.innerHTML = `
                <div class="max-w-[86%] rounded-3xl rounded-tr-lg bg-gradient-to-r from-indigo-500 to-cyan-500 px-4 py-3 shadow-lg shadow-cyan-500/10">
                    <p class="text-sm text-white leading-6 break-words [overflow-wrap:anywhere]">${nl2br(message)}</p>
                </div>
                <div class="h-9 w-9 rounded-2xl bg-slate-800 border border-white/10 flex items-center justify-center text-sm shrink-0">👤</div>
            `;
            messagesBox.appendChild(wrapper);
            scrollToBottom();
        }

        function sourceCards(sources) {
            if (!Array.isArray(sources) || sources.length === 0) {
                return '';
            }

            return `
                <div class="mt-4 space-y-2">
                    <div class="text-[11px] uppercase tracking-[0.18em] text-cyan-300/80">Nguồn tham khảo</div>
                    ${sources.map(source => `
                        <a
                            href="${escapeHtml(source.url || '#')}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="block rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-3 py-3 hover:bg-cyan-400/15 transition"
                        >
                            <div class="text-xs font-bold text-cyan-100">${escapeHtml(source.title || 'Nội dung liên quan')}</div>
                            ${source.excerpt ? `<div class="mt-1 text-[11px] leading-5 text-slate-400 line-clamp-2">${escapeHtml(source.excerpt)}</div>` : ''}
                            <div class="mt-1 text-[10px] uppercase tracking-wide text-cyan-300/70">${escapeHtml(source.type || 'source')} →</div>
                        </a>
                    `).join('')}
                </div>
            `;
        }

        function suggestionButtons(suggestions) {
            if (!Array.isArray(suggestions) || suggestions.length === 0) {
                return '';
            }

            return `
                <div class="mt-3 flex flex-wrap gap-2">
                    ${suggestions.slice(0, 3).map(item => `
                        <button type="button" class="chatbot-suggestion rounded-full border border-white/10 bg-white/[0.04] px-3 py-1.5 text-[11px] text-slate-300 hover:bg-cyan-400/10 hover:text-cyan-100 transition" data-message="${escapeHtml(item)}">${escapeHtml(item)}</button>
                    `).join('')}
                </div>
            `;
        }

        function addBotMessage(answer, sources = [], suggestions = [], messageId = null) {
            const wrapper = document.createElement('div');
            wrapper.className = 'flex items-start gap-3';
            wrapper.innerHTML = `
                <div class="h-9 w-9 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-500 flex items-center justify-center text-sm shrink-0">🤖</div>
                <div class="max-w-[86%] rounded-3xl rounded-tl-lg border border-white/10 bg-white/[0.06] px-4 py-3 min-w-0">
                    <p class="text-sm text-slate-200 leading-6 break-words [overflow-wrap:anywhere]">${nl2br(answer)}</p>
                    ${sourceCards(sources)}
                    ${suggestionButtons(suggestions)}
                    ${messageId ? `
                        <div class="mt-3 flex items-center gap-2 border-t border-white/10 pt-3">
                            <span class="text-[10px] text-slate-500">Câu trả lời hữu ích?</span>
                            <button type="button" class="chatbot-feedback rounded-lg border border-white/10 px-2 py-1 text-xs hover:bg-emerald-400/10" data-message-id="${messageId}" data-rating="helpful">👍</button>
                            <button type="button" class="chatbot-feedback rounded-lg border border-white/10 px-2 py-1 text-xs hover:bg-rose-400/10" data-message-id="${messageId}" data-rating="not_helpful">👎</button>
                        </div>
                    ` : ''}
                </div>
            `;
            messagesBox.appendChild(wrapper);
            scrollToBottom();
        }

        function addTypingIndicator() {
            const wrapper = document.createElement('div');
            wrapper.id = 'chatbot-typing';
            wrapper.className = 'flex items-start gap-3';
            wrapper.innerHTML = `
                <div class="h-9 w-9 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-500 flex items-center justify-center text-sm shrink-0">🤖</div>
                <div class="rounded-3xl rounded-tl-lg border border-white/10 bg-white/[0.06] px-4 py-3">
                    <div class="flex items-center gap-1">
                        <span class="h-2 w-2 rounded-full bg-slate-400 animate-pulse"></span>
                        <span class="h-2 w-2 rounded-full bg-slate-400 animate-pulse [animation-delay:150ms]"></span>
                        <span class="h-2 w-2 rounded-full bg-slate-400 animate-pulse [animation-delay:300ms]"></span>
                    </div>
                </div>
            `;
            messagesBox.appendChild(wrapper);
            scrollToBottom();
        }

        function removeTypingIndicator() {
            document.getElementById('chatbot-typing')?.remove();
        }

        function setLoading(value) {
            isLoading = value;
            submitButton.disabled = value;
            input.disabled = value;
            submitButton.classList.toggle('opacity-60', value);
            submitButton.classList.toggle('cursor-not-allowed', value);
        }

        async function loadHistory() {
            if (!sessionUuid || historyLoaded) {
                addWelcome();
                historyLoaded = true;
                return;
            }

            try {
                const response = await fetch(`${historyBase}/${encodeURIComponent(sessionUuid)}`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Không thể tải lịch sử.');
                }

                const data = await response.json();
                messagesBox.innerHTML = '';

                if (!Array.isArray(data.messages) || data.messages.length === 0) {
                    addWelcome();
                } else {
                    data.messages.forEach(message => {
                        if (message.role === 'user') {
                            addUserMessage(message.content);
                        } else if (message.role === 'assistant') {
                            addBotMessage(message.content, message.sources || [], [], message.id);
                        }
                    });
                }

                historyLoaded = true;
            } catch (error) {
                localStorage.removeItem(storageKey);
                sessionUuid = null;
                historyLoaded = true;
                addWelcome();
            }
        }

        async function startNewSession() {
            if (isLoading) {
                return;
            }

            try {
                const response = await fetch(startEndpoint, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                });

                const data = await response.json();

                if (!response.ok || !data.session_uuid) {
                    throw new Error(data.message || 'Không thể tạo phiên mới.');
                }

                sessionUuid = data.session_uuid;
                localStorage.setItem(storageKey, sessionUuid);
                historyLoaded = true;
                addWelcome();
                input.focus();
            } catch (error) {
                addBotMessage(error.message || 'Không thể tạo phiên mới lúc này.');
            }
        }

        async function sendMessage(message) {
            const trimmedMessage = message.trim();

            if (!trimmedMessage || isLoading) {
                return;
            }

            addUserMessage(trimmedMessage);
            input.value = '';
            input.style.height = 'auto';
            characterCount.textContent = '0 / 2000';
            addTypingIndicator();
            setLoading(true);

            try {
                const response = await fetch(askEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        message: trimmedMessage,
                        session_uuid: sessionUuid,
                    }),
                });

                const data = await response.json();
                removeTypingIndicator();

                if (!response.ok) {
                    throw new Error(data.message || 'Chatbot chưa thể xử lý câu hỏi.');
                }

                if (data.session_uuid) {
                    sessionUuid = data.session_uuid;
                    localStorage.setItem(storageKey, sessionUuid);
                }

                addBotMessage(
                    data.answer || 'Mình chưa tạo được câu trả lời phù hợp.',
                    data.sources || data.related_links || [],
                    data.suggestions || [],
                    data.message_id || null
                );
            } catch (error) {
                removeTypingIndicator();
                addBotMessage(error.message || 'Chatbot hiện chưa thể kết nối tới hệ thống.');
            } finally {
                setLoading(false);
                input.focus();
            }
        }

        async function sendFeedback(button) {
            const messageId = button.dataset.messageId;
            const rating = button.dataset.rating;

            if (!messageId || !rating) {
                return;
            }

            button.disabled = true;

            try {
                const response = await fetch(`${feedbackBase}/${messageId}/feedback`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ rating }),
                });

                if (!response.ok) {
                    throw new Error('Không thể lưu đánh giá.');
                }

                const group = button.parentElement;
                group?.querySelectorAll('.chatbot-feedback').forEach(item => {
                    item.classList.remove('bg-emerald-400/15', 'bg-rose-400/15');
                });
                button.classList.add(
                    rating === 'helpful' ? 'bg-emerald-400/15' : 'bg-rose-400/15'
                );
            } catch (error) {
                button.disabled = false;
            }
        }

        async function openChatbot() {
            windowBox.classList.remove('hidden');
            toggleIcon.textContent = '✕';
            await loadHistory();
            setTimeout(() => input.focus(), 100);
            scrollToBottom();
        }

        function closeChatbot() {
            windowBox.classList.add('hidden');
            toggleIcon.textContent = '💬';
        }

        toggleButton.addEventListener('click', function () {
            if (windowBox.classList.contains('hidden')) {
                openChatbot();
            } else {
                closeChatbot();
            }
        });

        closeButton?.addEventListener('click', closeChatbot);
        newSessionButton?.addEventListener('click', startNewSession);

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            sendMessage(input.value);
        });

        input.addEventListener('input', function () {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 112) + 'px';
            characterCount.textContent = `${input.value.length} / 2000`;
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                form.requestSubmit();
            }
        });

        document.addEventListener('click', function (event) {
            const suggestion = event.target.closest('.chatbot-suggestion');

            if (suggestion) {
                const message = suggestion.getAttribute('data-message');

                if (message) {
                    openChatbot();
                    sendMessage(message);
                }

                return;
            }

            const feedback = event.target.closest('.chatbot-feedback');

            if (feedback) {
                sendFeedback(feedback);
            }
        });
    });
</script>