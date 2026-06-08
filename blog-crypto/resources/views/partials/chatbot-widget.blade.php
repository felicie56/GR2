@php
    $chatbotEndpoint = route('chatbot.ask');
@endphp

<div id="crypto-chatbot-widget" class="fixed bottom-5 right-5 z-50">
    {{-- Chat Window --}}
    <div id="chatbot-window"
         class="hidden mb-4 w-[calc(100vw-2.5rem)] sm:w-[420px] max-h-[680px] rounded-[2rem] border border-white/10 bg-slate-950/95 shadow-2xl shadow-cyan-950/40 backdrop-blur-xl overflow-hidden">

        {{-- Header --}}
        <div class="relative overflow-hidden border-b border-white/10 p-5">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-16 -right-16 h-40 w-40 rounded-full bg-cyan-500/20 blur-3xl"></div>
                <div class="absolute -bottom-16 -left-16 h-40 w-40 rounded-full bg-indigo-500/20 blur-3xl"></div>
            </div>

            <div class="relative flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-500 flex items-center justify-center text-2xl shadow-lg shadow-cyan-500/20">
                        🤖
                    </div>

                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-white">
                                Crypto Assistant
                            </h3>

                            <span class="inline-flex items-center gap-1 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-2 py-0.5 text-[11px] font-bold text-emerald-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                                Online
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-slate-400">
                            Hỗ trợ crypto, blog, news, giá coin và cách dùng website.
                        </p>
                    </div>
                </div>

                <button id="chatbot-close"
                        type="button"
                        class="h-9 w-9 rounded-xl border border-white/10 bg-white/[0.04] text-slate-300 hover:text-white hover:bg-white/10 transition">
                    ✕
                </button>
            </div>
        </div>

        {{-- Messages --}}
        <div id="chatbot-messages" class="h-[410px] overflow-y-auto p-5 space-y-4 scroll-smooth">
            <div class="flex items-start gap-3">
                <div class="h-9 w-9 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-500 flex items-center justify-center text-sm shrink-0">
                    🤖
                </div>

                <div class="max-w-[85%] rounded-3xl rounded-tl-lg border border-white/10 bg-white/[0.06] px-4 py-3">
                    <p class="text-sm text-slate-200 leading-6">
                        Xin chào! Mình có thể hỗ trợ bạn về crypto, giá coin, bài blog, tin tức, bình luận và quy trình đăng ký tác giả.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-2">
                <button type="button"
                        class="chatbot-suggestion text-left rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3 text-xs text-slate-300 hover:bg-cyan-400/10 hover:text-cyan-100 transition"
                        data-message="Bitcoin là gì?">
                    Bitcoin là gì?
                </button>

                <button type="button"
                        class="chatbot-suggestion text-left rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3 text-xs text-slate-300 hover:bg-cyan-400/10 hover:text-cyan-100 transition"
                        data-message="Làm sao để trở thành tác giả?">
                    Làm sao để trở thành tác giả?
                </button>

                <button type="button"
                        class="chatbot-suggestion text-left rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3 text-xs text-slate-300 hover:bg-cyan-400/10 hover:text-cyan-100 transition"
                        data-message="Vì sao bài viết của tôi chưa hiển thị?">
                    Vì sao bài viết của tôi chưa hiển thị?
                </button>
            </div>
        </div>

        {{-- Input --}}
        <div class="border-t border-white/10 p-4">
            <form id="chatbot-form" class="space-y-3">
                <div class="flex items-end gap-2">
                    <textarea id="chatbot-input"
                              rows="1"
                              maxlength="500"
                              class="min-h-[48px] max-h-28 flex-1 resize-none rounded-2xl border border-white/10 bg-slate-900/80 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-400 focus:ring-cyan-400/30"
                              placeholder="Hỏi về Bitcoin, DeFi, giá BTC, đăng ký tác giả..."
                              required></textarea>

                    <button id="chatbot-submit"
                            type="submit"
                            class="h-12 w-12 rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 text-white font-black shadow-lg shadow-cyan-500/20 hover:from-indigo-400 hover:to-cyan-400 transition flex items-center justify-center">
                        ➤
                    </button>
                </div>

                <p class="text-[11px] text-slate-500 leading-5">
                    Chatbot chỉ hỗ trợ trong phạm vi crypto và website. Nội dung không phải lời khuyên đầu tư.
                </p>
            </form>
        </div>
    </div>

    {{-- Floating Button --}}
    <button id="chatbot-toggle"
            type="button"
            class="group relative h-16 w-16 rounded-3xl bg-gradient-to-br from-indigo-500 to-cyan-500 text-white shadow-2xl shadow-cyan-500/30 hover:scale-105 transition flex items-center justify-center">
        <span class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-emerald-400 border-2 border-slate-950 shadow-[0_0_14px_rgba(52,211,153,0.9)]"></span>
        <span id="chatbot-toggle-icon" class="text-2xl">💬</span>
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const widget = document.getElementById('crypto-chatbot-widget');
        const toggleButton = document.getElementById('chatbot-toggle');
        const closeButton = document.getElementById('chatbot-close');
        const windowBox = document.getElementById('chatbot-window');
        const messagesBox = document.getElementById('chatbot-messages');
        const form = document.getElementById('chatbot-form');
        const input = document.getElementById('chatbot-input');
        const submitButton = document.getElementById('chatbot-submit');
        const toggleIcon = document.getElementById('chatbot-toggle-icon');

        if (!widget || !toggleButton || !windowBox || !messagesBox || !form || !input || !submitButton) {
            return;
        }

        const endpoint = @json($chatbotEndpoint);
        const csrfToken = @json(csrf_token());

        function openChatbot() {
            windowBox.classList.remove('hidden');
            toggleIcon.textContent = '✕';
            setTimeout(() => input.focus(), 100);
            scrollToBottom();
        }

        function closeChatbot() {
            windowBox.classList.add('hidden');
            toggleIcon.textContent = '💬';
        }

        function toggleChatbot() {
            if (windowBox.classList.contains('hidden')) {
                openChatbot();
            } else {
                closeChatbot();
            }
        }

        function scrollToBottom() {
            messagesBox.scrollTop = messagesBox.scrollHeight;
        }

        function escapeHtml(value) {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function nl2br(value) {
            return escapeHtml(value).replace(/\n/g, '<br>');
        }

        function addUserMessage(message) {
            const wrapper = document.createElement('div');
            wrapper.className = 'flex items-start justify-end gap-3';

            wrapper.innerHTML = `
                <div class="max-w-[85%] rounded-3xl rounded-tr-lg bg-gradient-to-r from-indigo-500 to-cyan-500 px-4 py-3 shadow-lg shadow-cyan-500/10">
                    <p class="text-sm text-white leading-6 break-words [overflow-wrap:anywhere]">${nl2br(message)}</p>
                </div>
                <div class="h-9 w-9 rounded-2xl bg-slate-800 border border-white/10 flex items-center justify-center text-sm shrink-0">
                    👤
                </div>
            `;

            messagesBox.appendChild(wrapper);
            scrollToBottom();
        }

        function addBotMessage(answer, relatedLinks = [], suggestions = []) {
            const wrapper = document.createElement('div');
            wrapper.className = 'flex items-start gap-3';

            const linksHtml = Array.isArray(relatedLinks) && relatedLinks.length > 0
                ? `
                    <div class="mt-3 space-y-2">
                        ${relatedLinks.map(link => `
                            <a href="${escapeHtml(link.url || '#')}"
                               class="block rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-3 py-2 text-xs font-semibold text-cyan-100 hover:bg-cyan-400/15 transition">
                                ${escapeHtml(link.type || 'Link')}: ${escapeHtml(link.title || 'Nội dung liên quan')} →
                            </a>
                        `).join('')}
                    </div>
                `
                : '';

            const suggestionsHtml = Array.isArray(suggestions) && suggestions.length > 0
                ? `
                    <div class="mt-3 flex flex-wrap gap-2">
                        ${suggestions.slice(0, 3).map(item => `
                            <button type="button"
                                    class="chatbot-suggestion rounded-full border border-white/10 bg-white/[0.04] px-3 py-1.5 text-[11px] text-slate-300 hover:bg-cyan-400/10 hover:text-cyan-100 transition"
                                    data-message="${escapeHtml(item)}">
                                ${escapeHtml(item)}
                            </button>
                        `).join('')}
                    </div>
                `
                : '';

            wrapper.innerHTML = `
                <div class="h-9 w-9 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-500 flex items-center justify-center text-sm shrink-0">
                    🤖
                </div>

                <div class="max-w-[85%] rounded-3xl rounded-tl-lg border border-white/10 bg-white/[0.06] px-4 py-3">
                    <p class="text-sm text-slate-200 leading-6 break-words [overflow-wrap:anywhere]">${nl2br(answer)}</p>
                    ${linksHtml}
                    ${suggestionsHtml}
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
                <div class="h-9 w-9 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-500 flex items-center justify-center text-sm shrink-0">
                    🤖
                </div>

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
            const typing = document.getElementById('chatbot-typing');

            if (typing) {
                typing.remove();
            }
        }

        function setLoading(isLoading) {
            submitButton.disabled = isLoading;
            input.disabled = isLoading;

            if (isLoading) {
                submitButton.classList.add('opacity-60', 'cursor-not-allowed');
            } else {
                submitButton.classList.remove('opacity-60', 'cursor-not-allowed');
            }
        }

        async function sendMessage(message) {
            const trimmedMessage = message.trim();

            if (!trimmedMessage) {
                return;
            }

            addUserMessage(trimmedMessage);
            input.value = '';
            input.style.height = 'auto';

            addTypingIndicator();
            setLoading(true);

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        message: trimmedMessage,
                    }),
                });

                const data = await response.json();

                removeTypingIndicator();

                if (!response.ok) {
                    addBotMessage('Xin lỗi, mình chưa thể xử lý câu hỏi này lúc này. Bạn có thể thử hỏi lại ngắn gọn hơn hoặc kiểm tra kết nối hệ thống.');
                    return;
                }

                addBotMessage(
                    data.answer || 'Mình chưa có câu trả lời phù hợp cho câu hỏi này.',
                    data.related_links || [],
                    data.suggestions || []
                );
            } catch (error) {
                removeTypingIndicator();

                addBotMessage('Xin lỗi, hiện chatbot chưa thể kết nối tới hệ thống. Bạn hãy thử lại sau nhé.');
            } finally {
                setLoading(false);
                input.focus();
            }
        }

        toggleButton.addEventListener('click', toggleChatbot);

        if (closeButton) {
            closeButton.addEventListener('click', closeChatbot);
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            sendMessage(input.value);
        });

        input.addEventListener('input', function () {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 112) + 'px';
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                form.requestSubmit();
            }
        });

        document.addEventListener('click', function (event) {
            const suggestion = event.target.closest('.chatbot-suggestion');

            if (!suggestion) {
                return;
            }

            const message = suggestion.getAttribute('data-message');

            if (!message) {
                return;
            }

            openChatbot();
            sendMessage(message);
        });
    });
</script>