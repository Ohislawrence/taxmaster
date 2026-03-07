<template>
    <!-- Floating Chat Widget -->
    <div class="fixed bottom-6 right-6 z-[95]">
        <!-- Chat Panel -->
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
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-sm">TaxMaster AI</h3>
                            <p class="text-blue-100 text-[10px]">Tax Advisor &bull; Nigerian Tax Law</p>
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
                        <div class="w-12 h-12 mx-auto rounded-full bg-blue-50 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                class="block w-full text-left text-xs bg-gray-50 hover:bg-blue-50 text-gray-700 hover:text-blue-700 px-3 py-2 rounded-lg transition border border-gray-100 hover:border-blue-200"
                            >
                                {{ suggestion }}
                            </button>
                        </div>
                    </div>

                    <!-- Chat Messages -->
                    <template v-for="(msg, index) in messages" :key="index">
                        <!-- User Message -->
                        <div v-if="msg.role === 'user'" class="flex justify-end">
                            <div class="bg-blue-600 text-white text-sm px-3 py-2 rounded-2xl rounded-br-md max-w-[85%] break-words">
                                {{ msg.content }}
                            </div>
                        </div>
                        <!-- AI Message -->
                        <div v-else class="flex justify-start gap-2">
                            <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 mt-1">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                                </svg>
                            </div>
                            <div class="bg-gray-100 text-gray-800 text-sm px-3 py-2 rounded-2xl rounded-bl-md max-w-[85%] break-words chat-markdown" v-html="formatMarkdown(msg.content)"></div>
                        </div>
                    </template>

                    <!-- Typing Indicator -->
                    <div v-if="isLoading" class="flex justify-start gap-2">
                        <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 mt-1">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                    <!-- Error -->
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
                            class="flex-1 resize-none text-sm border border-gray-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent max-h-20"
                            :disabled="isLoading"
                        ></textarea>
                        <button
                            type="submit"
                            :disabled="!userInput.trim() || isLoading"
                            class="w-9 h-9 flex items-center justify-center rounded-full bg-blue-600 text-white hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition flex-shrink-0"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </transition>

        <!-- Floating Toggle Button -->
        <button
            @click="toggleChat"
            class="w-14 h-14 rounded-full shadow-lg flex items-center justify-center transition-all duration-200 hover:scale-105 active:scale-95"
            :class="isOpen ? 'bg-gray-700 hover:bg-gray-800' : 'bg-blue-600 hover:bg-blue-700'"
            :title="isOpen ? 'Close TaxMaster' : 'Ask TaxMaster'"
        >
            <!-- Chat icon (when closed) -->
            <svg v-if="!isOpen" class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <!-- X icon (when open) -->
            <svg v-else class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>

<script setup>
import { ref, nextTick, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

/**
 * Lightweight markdown formatter for chat messages.
 * Supports: **bold**, *italic*, `code`, ```code blocks```, - lists, numbered lists, newlines
 */
const formatMarkdown = (text) => {
    if (!text) return '';
    let html = text
        // Escape HTML
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        // Code blocks (```...```)
        .replace(/```([\s\S]*?)```/g, '<pre class="bg-gray-200 rounded px-2 py-1 my-1 text-xs overflow-x-auto">$1</pre>')
        // Inline code (`...`)
        .replace(/`([^`]+)`/g, '<code class="bg-gray-200 rounded px-1 py-0.5 text-xs">$1</code>')
        // Bold (**...**)
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        // Italic (*...*)
        .replace(/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/g, '<em>$1</em>')
        // Headings (### ... at line start)
        .replace(/^### (.+)$/gm, '<span class="font-semibold text-gray-900">$1</span>')
        .replace(/^## (.+)$/gm, '<span class="font-bold text-gray-900">$1</span>')
        // Unordered lists (- item)
        .replace(/^[\-\•] (.+)$/gm, '<li class="ml-4 list-disc">$1</li>')
        // Ordered lists (1. item)
        .replace(/^\d+\. (.+)$/gm, '<li class="ml-4 list-decimal">$1</li>')
        // Wrap adjacent <li> in <ul>/<ol>
        .replace(/((?:<li class="ml-4 list-disc">.*<\/li>\n?)+)/g, '<ul class="my-1">$1</ul>')
        .replace(/((?:<li class="ml-4 list-decimal">.*<\/li>\n?)+)/g, '<ol class="my-1">$1</ol>')
        // Paragraphs — double newline
        .replace(/\n\n/g, '</p><p class="mt-2">')
        // Single newlines
        .replace(/\n/g, '<br>');
    return '<p>' + html + '</p>';
};

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

const sendMessage = async () => {
    const text = userInput.value.trim();
    if (!text || isLoading.value) return;

    errorMessage.value = '';
    messages.value.push({ role: 'user', content: text });
    userInput.value = '';
    isLoading.value = true;
    scrollToBottom();

    try {
        // Detect page context from current route
        const currentPath = window.location.pathname;
        let context = 'general';
        if (currentPath.includes('/paye')) context = 'payroll';
        else if (currentPath.includes('/compliance')) context = 'compliance';
        else if (currentPath.includes('/cit') || currentPath.includes('/tax-returns')) context = 'tax_planning';
        else if (currentPath.includes('/wht') || currentPath.includes('/vat')) context = 'deductions';

        const csrfToken = page.props.csrf_token || document.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch('/business/ai/chat/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                message: text,
                context: context,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            if (response.status === 403) {
                errorMessage.value = 'AI Chat requires a Pro subscription. Please upgrade your plan.';
            } else {
                errorMessage.value = data.error || 'Something went wrong. Please try again.';
            }
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

// Auto-scroll when messages change
watch(messages, scrollToBottom, { deep: true });
</script>
