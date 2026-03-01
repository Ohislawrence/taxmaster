<template>
    <BusinessLayout>
        <Head title="AI Tax Assistant" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-2">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <h1 class="text-3xl font-bold text-gray-900">AI Tax Assistant</h1>
                </div>
                <p class="text-gray-600">Get instant tax advice from our AI-powered assistant</p>
            </div>

            <!-- AI Configuration Alert -->
            <div v-if="!aiConfigured && aiError" class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex gap-4">
                    <svg class="w-6 h-6 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2m0-12a9 9 0 110 18 9 9 0 010-18z"></path>
                    </svg>
                    <div>
                        <h3 class="font-bold text-yellow-900 mb-1">AI Not Configured</h3>
                        <p class="text-yellow-800 mb-4">{{ aiError }}</p>
                        <p class="text-sm text-yellow-700">Please contact your administrator to configure AI features.</p>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Chat Panel -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-lg p-6 h-screen max-h-[600px] flex flex-col">
                        <!-- Messages -->
                        <div class="flex-1 overflow-y-auto mb-6 space-y-4" ref="messagesContainer">
                            <div v-if="messages.length === 0" class="flex items-center justify-center h-full">
                                <div class="text-center">
                                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-gray-500 font-medium">Start a conversation</p>
                                    <p class="text-gray-400 text-sm">Ask me anything about your taxes</p>
                                </div>
                            </div>

                            <!-- Chat Messages -->
                            <div v-for="(message, index) in messages" :key="index" :class="[
                                'flex gap-3',
                                message.role === 'user' ? 'justify-end' : 'justify-start'
                            ]">
                                <!-- AI Message -->
                                <div v-if="message.role === 'assistant'" class="flex gap-3 max-w-xs">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="bg-gray-100 rounded-lg p-4 text-gray-900">
                                        {{ message.content }}
                                    </div>
                                </div>

                                <!-- User Message -->
                                <div v-else class="flex gap-3 max-w-xs justify-end">
                                    <div class="bg-blue-600 rounded-lg p-4 text-white">
                                        {{ message.content }}
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-200">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Loading Indicator -->
                            <div v-if="loading" class="flex gap-3">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100">
                                        <svg class="w-5 h-5 text-blue-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="bg-gray-100 rounded-lg p-4">
                                    <div class="flex gap-1">
                                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce animation-delay-200"></div>
                                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce animation-delay-400"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Input Area -->
                        <div class="border-t pt-6">
                            <!-- Context Selector -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Question Category (Optional)</label>
                                <div class="flex flex-wrap gap-2">
                                    <button 
                                        v-for="ctx in contexts"
                                        :key="ctx.value"
                                        @click="selectedContext = ctx.value"
                                        :class="[
                                            'px-3 py-1 rounded-full text-sm font-medium transition',
                                            selectedContext === ctx.value 
                                                ? 'bg-blue-600 text-white' 
                                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                        ]"
                                    >
                                        {{ ctx.label }}
                                    </button>
                                </div>
                            </div>

                            <!-- Input Field -->
                            <form @submit.prevent="sendMessage" class="flex gap-3">
                                <input 
                                    v-model="userMessage"
                                    type="text"
                                    placeholder="Ask me anything about your taxes..."
                                    :disabled="loading || !aiConfigured"
                                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400 disabled:bg-gray-100"
                                />
                                <button 
                                    type="submit"
                                    :disabled="loading || !userMessage.trim() || !aiConfigured"
                                    class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-6 py-3 rounded-lg font-medium transition"
                                >
                                    <svg v-if="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    <svg v-else class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-2">
                            <Link 
                                href="/business/ai/chat"
                                class="flex items-center gap-3 p-4 rounded-lg hover:bg-blue-50 transition border border-transparent hover:border-blue-200 text-gray-700"
                            >
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Tax Planning</span>
                            </Link>
                            <Link 
                                href="/business/ai/insights"
                                class="flex items-center gap-3 p-4 rounded-lg hover:bg-blue-50 transition border border-transparent hover:border-blue-200 text-gray-700"
                            >
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m-6 0V5a2 2 0 012-2h2a2 2 0 012 2v14"></path>
                                </svg>
                                <span>Tax Insights</span>
                            </Link>
                            <button 
                                @click="clearChat"
                                class="w-full flex items-center gap-3 p-4 rounded-lg hover:bg-red-50 transition border border-transparent hover:border-red-200 text-gray-700"
                            >
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                <span>Clear Chat</span>
                            </button>
                        </div>
                    </div>

                    <!-- Tips -->
                    <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                        <h4 class="font-bold text-gray-900 mb-3">💡 Tips</h4>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li>• Ask about tax deductions</li>
                            <li>• Get payroll tax guidance</li>
                            <li>• Learn compliance requirements</li>
                            <li>• Plan tax optimization</li>
                            <li>• Understand tax deadlines</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref, nextTick, watch, computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import BusinessLayout from '@/Layouts/BusinessLayout.vue'
import { useSubscription } from '@/composables/useSubscription'

const page = usePage()
const { can, planName } = useSubscription()

const props = defineProps({
    business: Object,
    provider: String,
    aiConfigured: Boolean,
    aiError: String,
});

const isFeatureAvailable = computed(() => can.useAiChat.value);

const messagesContainer = ref(null);
const messages = ref([]);
const userMessage = ref('');
const loading = ref(false);
const selectedContext = ref('general');

const contexts = [
    { value: 'general', label: 'General' },
    { value: 'tax_planning', label: 'Tax Planning' },
    { value: 'payroll', label: 'Payroll' },
    { value: 'deductions', label: 'Deductions' },
    { value: 'compliance', label: 'Compliance' },
];

// Auto-scroll to latest message
watch(() => messages.value.length, () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
});

const sendMessage = async () => {
    if (!userMessage.value.trim() || loading.value) return;
    
    // Check subscription first
    if (!isFeatureAvailable.value) {
        messages.value.push({
            role: 'assistant',
            content: '❌ Your current plan does not include AI chat. Please upgrade to Professional or higher to use this feature.',
        });
        return;
    }

    // Check if AI is configured
    if (!props.aiConfigured) {
        messages.value.push({
            role: 'assistant',
            content: '❌ AI is not configured. Please contact your administrator to set up AI features.',
        });
        return;
    }

    const message = userMessage.value.trim();
    messages.value.push({
        role: 'user',
        content: message,
    });
    userMessage.value = '';
    loading.value = true;

    try {
        // Get CSRF token from page props (Inertia provides this)
        const csrfToken = page.props.csrf_token || document.querySelector('meta[name="csrf-token"]')?.content || '';

        const response = await fetch('/business/ai/chat/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                message: message,
                context: selectedContext.value,
            }),
        });

        // Check if response is ok (status 200-299)
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.error || errorData.message || `HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        if (data.success || data.message) {
            messages.value.push({
                role: 'assistant',
                content: data.message || 'No response',
            });
        } else if (data.error) {
            throw new Error(data.error);
        } else {
            throw new Error('Invalid response format');
        }
    } catch (error) {
        console.error('Chat Error:', error);
        messages.value.push({
            role: 'assistant',
            content: `❌ ${error.message || 'An error occurred. Please try again.'}`,
        });
    } finally {
        loading.value = false;
    }
};

const clearChat = () => {
    if (confirm('Are you sure you want to clear the chat?')) {
        messages.value = [];
    }
};
</script>

<style scoped>
.animation-delay-200 {
    animation-delay: 200ms;
}

.animation-delay-400 {
    animation-delay: 400ms;
}
</style>
