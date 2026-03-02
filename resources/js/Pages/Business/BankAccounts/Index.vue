<template>
    <BusinessLayout>
        <div class="space-y-4 sm:space-y-6 px-3 sm:px-0">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div class="flex-1">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Bank Accounts</h1>
                    <p class="text-sm sm:text-base text-gray-600 mt-1">Connect your bank accounts to auto-import transactions</p>
                </div>
                <button
                    @click="showConnectModal = true"
                    class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 sm:px-6 py-2 rounded-lg font-medium text-sm transition flex items-center justify-center sm:justify-start space-x-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Connect Bank</span>
                </button>
            </div>

            <!-- Connected Accounts -->
            <div v-if="accounts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="account in accounts"
                    :key="account.id"
                    class="bg-white rounded-lg shadow p-4 sm:p-6 hover:shadow-lg transition"
                >
                    <!-- Bank Info -->
                    <div class="flex items-start justify-between mb-4 gap-2">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-sm sm:text-base">{{ account.bank_name }}</h3>
                            <p class="text-xs sm:text-sm text-gray-600">{{ account.account_name }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ account.account_number }}</p>
                        </div>
                        <div
                            :class="account.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                            class="px-2 py-1 rounded text-xs font-medium whitespace-nowrap"
                        >
                            {{ account.is_active ? 'Active' : 'Inactive' }}
                        </div>
                    </div>

                    <!-- Balance -->
                    <div class="mb-4 p-3 bg-gray-50 rounded">
                        <p class="text-xs text-gray-600 uppercase tracking-wide">Balance</p>
                        <p class="text-lg sm:text-xl font-bold text-gray-900 mt-1">₦{{ formatCurrency(account.balance) }}</p>
                    </div>

                    <!-- Sync Info -->
                    <div class="mb-4 text-xs text-gray-600">
                        <p v-if="account.last_synced_at">
                            Last synced: <span class="font-medium">{{ account.last_synced_at }}</span>
                        </p>
                        <p v-else class="text-orange-600">Never synced</p>
                    </div>

                    <!-- Transaction Count -->
                    <div class="mb-4 p-2 bg-blue-50 rounded text-sm">
                        <p class="text-gray-700 text-xs sm:text-sm">
                            <span class="font-bold text-blue-600">{{ account.transactions_count }}</span> transactions
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-2">
                        <button
                            @click="syncAccount(account)"
                            :disabled="syncing === account.id"
                            class="flex-1 px-3 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded text-sm font-medium transition disabled:opacity-50"
                        >
                            <span v-if="syncing !== account.id">Sync Now</span>
                            <span v-else class="flex items-center justify-center space-x-1">
                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </span>
                        </button>
                        <button
                            @click="toggleAutoSync(account)"
                            :class="account.auto_sync ? 'bg-green-50 text-green-600' : 'bg-gray-50 text-gray-600'"
                            class="flex-1 px-3 py-2 hover:bg-opacity-80 rounded text-sm font-medium transition"
                        >
                            {{ account.auto_sync ? 'Auto On' : 'Auto Off' }}
                        </button>
                        <button
                            @click="disconnectAccount(account)"
                            class="px-3 py-2 bg-red-50 text-red-600 hover:bg-red-100 rounded text-sm font-medium transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No bank accounts connected</h3>
                <p class="text-gray-600 mb-6">Connect your first bank account to start syncing transactions automatically</p>
                <button
                    @click="showConnectModal = true"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition"
                >
                    Connect Your Bank
                </button>
            </div>

            <!-- Connect Bank Modal -->
            <div v-if="showConnectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold text-gray-900">Connect Bank Account</h2>
                        <button
                            @click="showConnectModal = false"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <p class="text-gray-600 mb-6">
                        We'll securely connect your bank account to import transactions automatically.
                        Your credentials are never stored with us.
                    </p>

                    <button
                        @click="initiateMonoAuth"
                        :disabled="connecting"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-medium transition disabled:opacity-50 flex items-center justify-center space-x-2"
                    >
                        <svg v-if="!connecting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.658 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        <svg v-else class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>{{ connecting ? 'Connecting...' : 'Connect with Mono' }}</span>
                    </button>

                    <p class="text-xs text-gray-500 text-center mt-4">
                        We use <span class="font-semibold">Mono</span> - Nigeria's #1 bank connection service
                    </p>
                </div>
            </div>

            <!-- Success Message -->
            <Teleport to="#app">
                <div
                    v-if="successMessage"
                    class="fixed top-4 right-4 bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg shadow-lg"
                >
                    {{ successMessage }}
                </div>
            </Teleport>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import BusinessLayout from '@/Layouts/BusinessLayout.vue'

const props = defineProps({
    accounts: Array,
    monoPublicKey: String,
})

const showConnectModal = ref(false)
const syncing = ref(null)
const connecting = ref(false)
const successMessage = ref('')

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value)
}

const syncAccount = (account) => {
    syncing.value = account.id

    fetch(`/business/banks/${account.id}/sync`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
        },
    })
    .then(() => {
        successMessage.value = 'Sync started! Transactions are being imported...'
        setTimeout(() => {
            window.location.reload()
        }, 2000)
    })
    .catch(() => {
        successMessage.value = 'Sync failed. Please try again.'
    })
    .finally(() => {
        syncing.value = null
    })
}

const toggleAutoSync = (account) => {
    fetch(`/business/banks/${account.id}/toggle-auto-sync`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
        },
    })
    .then(() => {
        successMessage.value = 'Auto-sync setting updated'
        setTimeout(() => {
            window.location.reload()
        }, 1500)
    })
}

const disconnectAccount = (account) => {
    if (!confirm(`Disconnect ${account.bank_name}? Transaction history will be preserved.`)) {
        return
    }

    fetch(`/business/banks/${account.id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
        },
    })
    .then(() => {
        successMessage.value = 'Bank account disconnected'
        setTimeout(() => {
            window.location.reload()
        }, 1500)
    })
}

const initiateMonoAuth = () => {
    connecting.value = true

    // Load Mono SDK
    const script = document.createElement('script')
    script.src = 'https://cdn.getmono.co/mono.js'
    script.onload = () => {
        if (window.MonoConnect) {
            const monoInstance = new window.MonoConnect({
                key: props.monoPublicKey,
                onClose: () => {
                    connecting.value = false
                },
                onSuccess: (response) => {
                    // Exchange code for account
                    fetch(`/business/banks/callback`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
                        },
                        body: JSON.stringify({
                            code: response.code,
                        }),
                    })
                    .then(() => {
                        showConnectModal.value = false
                        successMessage.value = 'Bank account connected successfully!'
                        setTimeout(() => {
                            window.location.reload()
                        }, 2000)
                    })
                    .catch((error) => {
                        successMessage.value = 'Connection failed: ' + error.message
                    })
                },
            })
            monoInstance.open()
        }
    }
    document.body.appendChild(script)
}
</script>
