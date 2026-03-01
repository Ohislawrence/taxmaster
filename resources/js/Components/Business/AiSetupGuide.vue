<template>
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg shadow-lg border border-blue-200 p-8 mb-8">
        <!-- Header -->
        <div class="flex items-start justify-between mb-8">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Get Started with AI</h2>
                </div>
                <p class="text-gray-600">Power up your tax management with intelligent AI assistance</p>
            </div>
            <button @click="showGuide = !showGuide" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Quick Start Steps -->
        <div v-if="!showGuide" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Step 1 -->
            <div class="bg-white rounded-lg p-6 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold">1</div>
                    <span class="text-xs font-semibold text-blue-600 bg-blue-100 px-3 py-1 rounded-full">2 minutes</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Get API Key</h3>
                <p class="text-gray-600 text-sm mb-4">Choose your AI provider and obtain an API key</p>
                <button @click="selectStep(1)" class="text-blue-600 hover:text-blue-700 font-medium text-sm">Learn More →</button>
            </div>

            <!-- Step 2 -->
            <div class="bg-white rounded-lg p-6 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold">2</div>
                    <span class="text-xs font-semibold text-blue-600 bg-blue-100 px-3 py-1 rounded-full">1 minute</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Configure Settings</h3>
                <p class="text-gray-600 text-sm mb-4">Add your API key in Settings and enable AI</p>
                <button @click="selectStep(2)" class="text-blue-600 hover:text-blue-700 font-medium text-sm">Learn More →</button>
            </div>

            <!-- Step 3 -->
            <div class="bg-white rounded-lg p-6 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold">3</div>
                    <span class="text-xs font-semibold text-blue-600 bg-blue-100 px-3 py-1 rounded-full">Ready!</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Start Using AI</h3>
                <p class="text-gray-600 text-sm mb-4">Chat, analyze, and optimize with AI assistance</p>
                <button @click="selectStep(3)" class="text-blue-600 hover:text-blue-700 font-medium text-sm">Learn More →</button>
            </div>
        </div>

        <!-- Detailed Guide -->
        <div v-else class="space-y-6">
            <!-- Step 1: Get API Key -->
            <div v-if="activeStep === 1" class="bg-white rounded-lg p-8">
                <button @click="showGuide = false" class="text-blue-600 hover:text-blue-700 text-sm font-medium mb-4">← Back to Overview</button>
                
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Step 1: Get Your API Key</h3>
                
                <div class="space-y-6">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3">Option A: Deepseek AI</h4>
                        <ol class="space-y-2 text-gray-700 ml-4">
                            <li class="flex gap-3">
                                <span class="font-semibold text-blue-600 flex-shrink-0">1.</span>
                                <span>Visit <a href="https://platform.deepseek.com/api_keys" target="_blank" class="text-blue-600 hover:underline">Deepseek API Console</a></span>
                            </li>
                            <li class="flex gap-3">
                                <span class="font-semibold text-blue-600 flex-shrink-0">2.</span>
                                <span>Create a new API key</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="font-semibold text-blue-600 flex-shrink-0">3.</span>
                                <span>Copy the key and paste it in Settings</span>
                            </li>
                        </ol>
                        <p class="text-sm text-gray-600 mt-2">Free tier includes $5 credits. Pricing: $0.14 per million input tokens</p>
                    </div>

                    <div class="border-t pt-6">
                        <h4 class="font-semibold text-gray-900 mb-3">Option B: Google Gemini</h4>
                        <ol class="space-y-2 text-gray-700 ml-4">
                            <li class="flex gap-3">
                                <span class="font-semibold text-blue-600 flex-shrink-0">1.</span>
                                <span>Visit <a href="https://ai.google.dev/tutorials/setup" target="_blank" class="text-blue-600 hover:underline">Google AI Studio</a></span>
                            </li>
                            <li class="flex gap-3">
                                <span class="font-semibold text-blue-600 flex-shrink-0">2.</span>
                                <span>Click "Create API Key"</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="font-semibold text-blue-600 flex-shrink-0">3.</span>
                                <span>Select your project and create the key</span>
                            </li>
                        </ol>
                        <p class="text-sm text-gray-600 mt-2">Free tier: Up to 60 requests/minute. Upgrade for higher limits.</p>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
                        <p class="text-sm text-blue-900 flex gap-2">
                            <svg class="w-5 h-5 flex-shrink-0 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" />
                            </svg>
                            <span><strong>Pro Tip:</strong> Start with the free tier to test, then upgrade based on usage</span>
                        </p>
                    </div>

                    <div class="flex gap-4 mt-8 pt-6 border-t">
                        <button @click="selectStep(2)" class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                            Next: Configure Settings →
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Configure Settings -->
            <div v-else-if="activeStep === 2" class="bg-white rounded-lg p-8">
                <button @click="showGuide = false" class="text-blue-600 hover:text-blue-700 text-sm font-medium mb-4">← Back to Overview</button>
                
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Step 2: Configure in Settings</h3>
                
                <div class="space-y-6">
                    <div class="bg-gray-50 rounded-lg p-6 space-y-4">
                        <p class="text-gray-700 font-medium">Follow these steps in your TaxMaster Settings:</p>
                        
                        <ol class="space-y-3 text-gray-700 ml-4">
                            <li class="flex gap-3">
                                <span class="font-semibold text-blue-600 flex-shrink-0">1.</span>
                                <span>Go to <strong>Settings → AI Configuration</strong></span>
                            </li>
                            <li class="flex gap-3">
                                <span class="font-semibold text-blue-600 flex-shrink-0">2.</span>
                                <span>Select your AI Provider (Deepseek or Google)</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="font-semibold text-blue-600 flex-shrink-0">3.</span>
                                <span>Paste your API Key in the API Key field</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="font-semibold text-blue-600 flex-shrink-0">4.</span>
                                <span>Check "Enable AI Analysis"</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="font-semibold text-blue-600 flex-shrink-0">5.</span>
                                <span>Click "Save AI Configuration"</span>
                            </li>
                        </ol>
                    </div>

                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-sm text-green-900 flex gap-2">
                            <svg class="w-5 h-5 flex-shrink-0 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span><strong>Secure:</strong> Your API key is encrypted and never shared</span>
                        </p>
                    </div>

                    <div class="flex gap-4 mt-8 pt-6 border-t">
                        <button @click="selectStep(1)" class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-900 rounded-lg font-medium transition">
                            ← Previous
                        </button>
                        <button @click="selectStep(3)" class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                            Next: Start Using AI →
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Start Using AI -->
            <div v-else-if="activeStep === 3" class="bg-white rounded-lg p-8">
                <button @click="showGuide = false" class="text-blue-600 hover:text-blue-700 text-sm font-medium mb-4">← Back to Overview</button>
                
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Step 3: Start Using AI Features</h3>
                
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- AI Chat -->
                        <div class="border rounded-lg p-6">
                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900 mb-2">AI Chat</h4>
                            <p class="text-gray-600 text-sm mb-4">Ask questions about tax planning, deductions, compliance, and payroll.</p>
                            <Link href="/business/ai/chat" class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                                Go to Chat →
                            </Link>
                        </div>

                        <!-- Tax Analysis -->
                        <div class="border rounded-lg p-6">
                            <div class="w-10 h-10 bg-green-100 text-green-600 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900 mb-2">Tax Analysis</h4>
                            <p class="text-gray-600 text-sm mb-4">Get AI-powered analysis of your tax returns and optimization recommendations.</p>
                            <Link href="/business/tax-returns" class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                                View Tax Returns →
                            </Link>
                        </div>

                        <!-- Tax Insights -->
                        <div class="border rounded-lg p-6">
                            <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900 mb-2">Tax Insights Dashboard</h4>
                            <p class="text-gray-600 text-sm mb-4">View AI-generated insights, compliance alerts, and optimization opportunities.</p>
                            <Link href="/business/ai/insights" class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                                View Insights →
                            </Link>
                        </div>

                        <!-- Compliance Tracking -->
                        <div class="border rounded-lg p-6">
                            <div class="w-10 h-10 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900 mb-2">AI Compliance Alerts</h4>
                            <p class="text-gray-600 text-sm mb-4">Get automatic alerts about tax deadlines and compliance requirements.</p>
                            <span class="text-gray-500 text-sm">Coming Soon</span>
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
                        <p class="text-sm text-blue-900 font-medium">✓ Setup Complete!</p>
                        <p class="text-sm text-blue-800 mt-1">Your AI features are now active. Start by exploring the AI Chat or analyzing a tax return.</p>
                    </div>

                    <div class="flex gap-4 mt-8 pt-6 border-t">
                        <button @click="selectStep(2)" class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-900 rounded-lg font-medium transition">
                            ← Previous
                        </button>
                        <Link href="/business/ai/chat" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white rounded-lg font-medium transition text-center">
                            Start with AI Chat →
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const showGuide = ref(false);
const activeStep = ref(1);

const selectStep = (step) => {
    activeStep.value = step;
    showGuide.value = true;
};
</script>
