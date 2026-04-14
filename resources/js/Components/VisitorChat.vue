<template>
    <!-- Floating Visitor Chat Widget -->
    <div class="fixed bottom-6 right-6 z-[95]">
        <transition
            enter-active-class="transition ease-out duration-200 origin-bottom-right"
            enter-from-class="opacity-0 scale-95 translate-y-2"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-150 origin-bottom-right"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-2"
        >
            <div
                v-if="isOpen"
                class="absolute bottom-16 right-0 w-[340px] sm:w-[380px] bg-white rounded-2xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden"
                style="max-height: 500px;"
            >
                <!-- Header -->
                <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-4 py-3 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-sm">TaxMaster AI</h3>
                            <p class="text-gray-300 text-[10px]">Tax Advisor &bull; Nigerian Tax Law</p>
                        </div>
                    </div>
                    <button @click="isOpen = false" class="text-white/70 hover:text-white transition p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>

                <!-- Messages Area -->
                <div ref="messagesContainer" class="flex-1 overflow-y-auto px-4 py-3 space-y-3" style="min-height: 280px; max-height: 340px;">
                    <!-- Welcome message -->
                    <div v-if="messages.length === 0" class="text-center py-6">
                        <div class="w-12 h-12 mx-auto rounded-full bg-gray-100 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-800">Hi! I'm TaxMaster</p>
                        <p class="text-xs text-gray-500 mt-1">Your Nigerian tax advisor. Ask me about tax laws, filing, payments, or how to use the app.</p>
                        <div class="mt-4 space-y-2">
                            <button
                                v-for="suggestion in quickSuggestions"
                                :key="suggestion"
                                @click="sendSuggestion(suggestion)"
                                class="block w-full text-left text-xs bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900 px-3 py-2 rounded-lg transition border border-gray-100 hover:border-gray-200"
                            >
                                {{ suggestion }}
                            </button>
                        </div>
                    </div>

                    <!-- Chat Messages -->
                    <template v-for="(msg, index) in messages" :key="index">
                        <div v-if="msg.role === 'user'" class="flex justify-end">
                            <div class="bg-gray-700 text-white text-sm px-3 py-2 rounded-2xl rounded-br-md max-w-[85%] break-words">
                                {{ msg.content }}
                            </div>
                        </div>
                        <div v-else class="flex justify-start gap-2">
                            <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0 mt-1">
                                <svg class="w-3.5 h-3.5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                                </svg>
                            </div>
                            <div class="bg-gray-100 text-gray-800 text-sm px-3 py-2 rounded-2xl rounded-bl-md max-w-[85%] break-words chat-markdown" v-html="formatMarkdown(msg.content)"></div>
                        </div>
                    </template>

                    <div v-if="isLoading" class="flex justify-start gap-2">
                        <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0 mt-1">
                            <svg class="w-3.5 h-3.5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                            </svg>
                        </div>
                        <div class="bg-gray-100 text-gray-500 text-sm px-4 py-2 rounded-2xl rounded-bl-md">
                            <span class="flex gap-1 items-center">
                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms;"></span>
                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms;"></span>
                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms;"></span>
                            </span>
                        </div>
                    </div>

                    <div v-if="errorMessage" class="flex justify-start gap-2">
                        <div class="bg-red-50 text-red-700 text-xs px-3 py-2 rounded-lg border border-red-200 w-full">
                            {{ errorMessage }}
                        </div>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="border-t border-gray-200 px-3 py-3 flex-shrink-0">
                    <form @submit.prevent="sendMessage" class="flex gap-2 items-end">
                        <textarea
                            ref="inputEl"
                            v-model="userInput"
                            @keydown.enter.exact.prevent="sendMessage"
                            placeholder="Ask about taxes, filing, payments..."
                            rows="1"
                            class="flex-1 resize-none text-sm border border-gray-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-700 focus:border-transparent max-h-20"
                            :disabled="isLoading"
                        ></textarea>
                        <button
                            type="submit"
                            :disabled="!userInput.trim() || isLoading"
                            class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-700 text-white hover:bg-gray-800 disabled:bg-gray-300 disabled:cursor-not-allowed transition flex-shrink-0"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </form>
                    <a
                        href="https://wa.me/2348117297730"
                        target="_blank"
                        rel="noopener"
                        class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-emerald-100 bg-emerald-50/70 px-3 py-2 text-xs font-medium text-emerald-700 transition-colors hover:border-emerald-200 hover:bg-emerald-100/70 hover:text-emerald-800"
                        aria-label="Chat with us on WhatsApp"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
                            <path d="M16.04 3C8.86 3 3 8.84 3 16.02c0 2.3.6 4.56 1.74 6.56L3 29l6.6-1.7a13.02 13.02 0 006.44 1.68H16.05c7.18 0 13.02-5.84 13.02-13.02C29.07 8.84 23.22 3 16.04 3zm0 23.84h-.01c-2 0-3.95-.54-5.64-1.56l-.4-.24-3.92 1 1.05-3.82-.26-.39a10.8 10.8 0 01-1.65-5.76c0-5.97 4.86-10.83 10.84-10.83 2.9 0 5.62 1.13 7.67 3.17a10.77 10.77 0 013.17 7.67c0 5.98-4.86 10.84-10.84 10.84zm5.94-8.11c-.33-.16-1.97-.97-2.27-1.08-.31-.11-.53-.16-.76.16-.22.33-.86 1.08-1.06 1.3-.2.22-.38.25-.71.08-.33-.16-1.38-.51-2.64-1.62-.98-.88-1.64-1.95-1.83-2.28-.19-.33-.02-.5.14-.67.14-.14.33-.39.49-.58.16-.2.22-.33.33-.55.11-.22.06-.41-.03-.58-.08-.16-.76-1.83-1.04-2.5-.27-.65-.54-.56-.76-.57h-.65c-.22 0-.58.08-.88.41-.3.33-1.16 1.13-1.16 2.75 0 1.62 1.19 3.18 1.35 3.4.16.22 2.33 3.56 5.64 4.99.79.34 1.4.55 1.88.7.79.25 1.5.21 2.06.13.63-.09 1.97-.81 2.25-1.58.28-.77.28-1.42.19-1.58-.08-.16-.3-.25-.63-.41z"/>
                        </svg>
                        Continue on WhatsApp
                    </a>
                </div>
            </div>
        </transition>

        <!-- Floating Toggle Button -->
        <button
            @click="toggleChat"
            class="w-14 h-14 rounded-full shadow-lg flex items-center justify-center transition-all duration-200 hover:scale-105 active:scale-95"
            :class="isOpen ? 'bg-gray-700 hover:bg-gray-800' : 'bg-gray-700 hover:bg-gray-800'"
            :title="isOpen ? 'Close TaxMaster' : 'Ask TaxMaster'"
        >
            <svg v-if="!isOpen" class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <svg v-else class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';

const isOpen = ref(false);
const isLoading = ref(false);
const userInput = ref('');
const errorMessage = ref('');
const messages = ref([]);
const messagesContainer = ref(null);
const inputEl = ref(null);

const quickSuggestions = [
    'What taxes does my business need to file?',
    'How do I generate an RRR for tax payment?',
    'What are the CIT rates in Nigeria?',
    'How does PAYE calculation work?',
];

const toggleChat = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        nextTick(() => inputEl.value?.focus());
    }
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const sendSuggestion = (text) => {
    userInput.value = text;
    sendMessage();
};

const formatMarkdown = (text) => {
    if (!text) return '';
    let html = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/```([\s\S]*?)```/g, '<pre class="bg-gray-200 rounded px-2 py-1 my-1 text-xs overflow-x-auto">$1</pre>')
        .replace(/`([^`]+)`/g, '<code class="bg-gray-200 rounded px-1 py-0.5 text-xs">$1</code>')
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/g, '<em>$1</em>')
        .replace(/^### (.+)$/gm, '<span class="font-semibold text-gray-900">$1</span>')
        .replace(/^## (.+)$/gm, '<span class="font-bold text-gray-900">$1</span>')
        .replace(/^[\-\•] (.+)$/gm, '<li class="ml-4 list-disc">$1</li>')
        .replace(/^\d+\. (.+)$/gm, '<li class="ml-4 list-decimal">$1</li>')
        .replace(/((?:<li class="ml-4 list-disc">.*<\/li>\n?)+)/g, '<ul class="my-1">$1</ul>')
        .replace(/((?:<li class="ml-4 list-decimal">.*<\/li>\n?)+)/g, '<ol class="my-1">$1</ol>')
        .replace(/\n\n/g, '</p><p class="mt-2">')
        .replace(/\n/g, '<br>');
    return '<p>' + html + '</p>';
};

const sendMessage = async () => {
    const text = userInput.value.trim();
    if (!text || isLoading.value) return;

    errorMessage.value = '';
    messages.value.push({ role: 'user', content: text });
    userInput.value = '';
    isLoading.value = true;
    scrollToBottom();

    try {
        let context = 'general';
        const currentPath = window.location.pathname;
        if (currentPath.includes('/paye')) context = 'payroll';
        else if (currentPath.includes('/compliance')) context = 'compliance';
        else if (currentPath.includes('/cit') || currentPath.includes('/tax-returns')) context = 'tax_planning';
        else if (currentPath.includes('/wht') || currentPath.includes('/vat')) context = 'deductions';

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch('/visitor/ai/chat/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
            },
            body: JSON.stringify({
                message: text,
                context: context,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            errorMessage.value = data.error || 'Something went wrong. Please try again.';
        } else if (data.success) {
            messages.value.push({ role: 'assistant', content: data.message });
        } else {
            errorMessage.value = data.error || 'Failed to get a response.';
        }
    } catch (err) {
        errorMessage.value = 'Network error. Please check your connection.';
    } finally {
        isLoading.value = false;
        scrollToBottom();
    }
};
</script>
