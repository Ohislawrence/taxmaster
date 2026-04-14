<template>
    <BusinessLayout>
        <Head title="Shopify Integration" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Shopify Integration</h1>
                            <p class="mt-2 text-sm text-gray-600">
                                Automatically sync orders and products from your Shopify store
                            </p>
                        </div>
                        <div class="flex space-x-3">
                            <button
                                v-if="connection && connection.status === 'active'"
                                @click="openSettings"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Settings
                            </button>
                            <button
                                v-if="connection && connection.status === 'active'"
                                @click="triggerSync"
                                :disabled="syncing"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg :class="['w-4 h-4 mr-2', syncing ? 'animate-spin' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                {{ syncing ? 'Syncing...' : 'Sync Now' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Connection Status Card -->
                <div class="mb-6">
                    <div v-if="connection && connection.status === 'active'" class="bg-white shadow rounded-lg p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-16 h-16 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Connected to Shopify</h3>
                                    <p class="mt-1 text-sm text-gray-600">{{ connection.shop_name || connection.shop_domain }}</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                        <span>{{ connection.shop_domain }}</span>
                                        <span>•</span>
                                        <span v-if="connection.last_synced_at">Last synced {{ formatDate(connection.last_synced_at) }}</span>
                                        <span v-else>Never synced</span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <span v-if="connection.auto_sync_enabled" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                            </svg>
                                            Auto-sync {{ connection.sync_frequency }}
                                        </span>
                                        <span v-if="connection.total_orders_synced > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ connection.total_orders_synced }} orders synced
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <button
                                @click="confirmDisconnect"
                                class="text-sm text-red-600 hover:text-red-700 font-medium"
                            >
                                Disconnect
                            </button>
                        </div>
                    </div>

                    <!-- Connection Setup Form -->
                    <div v-else class="bg-white shadow rounded-lg p-8">
                        <div class="max-w-2xl mx-auto">
                            <div class="text-center mb-8">
                                <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Connect Your Shopify Store</h3>
                                <p class="text-sm text-gray-600">
                                    Connect TaxMaster with your Shopify store to automatically sync orders for Nigerian tax compliance.
                                </p>
                            </div>

                            <!-- Instructions -->
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                                <h4 class="font-semibold text-green-900 mb-2">How to get your Shopify Admin API Access Token:</h4>
                                <ol class="text-sm text-green-800 space-y-1 list-decimal list-inside">
                                    <li>Go to your Shopify Admin → <strong>Settings</strong> → <strong>Apps and sales channels</strong></li>
                                    <li>Click <strong>Develop apps</strong> (you may need to enable custom app development first)</li>
                                    <li>Click <strong>Create an app</strong> and give it a name (e.g., "TaxMaster Integration")</li>
                                    <li>Go to <strong>Configuration</strong> tab → <strong>Admin API integration</strong></li>
                                    <li>Under <strong>Admin API access scopes</strong>, select: <code class="bg-white px-1 rounded text-xs">read_orders</code>, <code class="bg-white px-1 rounded text-xs">read_products</code></li>
                                    <li>Click <strong>Save</strong>, then go to the <strong>API credentials</strong> tab</li>
                                    <li>Click <strong>Install app</strong>, then reveal and copy your <strong>Admin API access token</strong></li>
                                    <li>Paste your shop domain and access token below</li>
                                </ol>
                            </div>

                            <!-- Connection Form -->
                            <form @submit.prevent="connectShopify" class="space-y-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Shop Domain <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="connectionForm.shop_domain"
                                        type="text"
                                        required
                                        placeholder="your-store.myshopify.com"
                                        pattern="[a-zA-Z0-9\-]+\.myshopify\.com"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">Enter your full Shopify domain (e.g., mystore.myshopify.com)</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Admin API Access Token <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="connectionForm.access_token"
                                        type="password"
                                        required
                                        placeholder="shpat_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm font-mono"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">Your Admin API access token from the Shopify admin panel</p>
                                </div>

                                <div class="pt-4">
                                    <button
                                        type="submit"
                                        :disabled="connectionForm.processing"
                                        class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <svg v-if="connectionForm.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ connectionForm.processing ? 'Connecting...' : 'Connect Shopify Store' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sync Logs -->
                <div v-if="connection && connection.status === 'active' && syncLogs.length > 0" class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Sync History</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Records</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="log in syncLogs" :key="log.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="capitalize">{{ log.entity_type }}</span>
                                        <span class="text-gray-500 ml-1">({{ log.sync_type }})</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            log.status === 'completed' ? 'bg-green-100 text-green-800' :
                                            log.status === 'failed' ? 'bg-red-100 text-red-800' :
                                            log.status === 'processing' ? 'bg-blue-100 text-blue-800' :
                                            'bg-gray-100 text-gray-800'
                                        ]">
                                            {{ log.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="text-green-600 font-medium">{{ log.success_count }}</span>
                                        <span class="text-gray-400"> / {{ log.total_records }}</span>
                                        <span v-if="log.failure_count > 0" class="text-red-600 ml-1">({{ log.failure_count }} failed)</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(log.started_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ log.duration_seconds ? `${log.duration_seconds}s` : '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Settings Modal -->
                <TransitionRoot as="template" :show="showSettingsModal">
                    <Dialog as="div" class="relative z-10" @close="showSettingsModal = false">
                        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                        </TransitionChild>

                        <div class="fixed inset-0 z-10 overflow-y-auto">
                            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                                    <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                                        <div>
                                            <div class="flex items-center justify-between mb-4">
                                                <h3 class="text-lg font-semibold text-gray-900">Sync Settings</h3>
                                                <button @click="showSettingsModal = false" class="text-gray-400 hover:text-gray-500">
                                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <form @submit.prevent="saveSettings" class="space-y-5">
                                                <div>
                                                    <label class="flex items-center">
                                                        <input
                                                            v-model="settingsForm.auto_sync_enabled"
                                                            type="checkbox"
                                                            class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                                                        />
                                                        <span class="ml-2 text-sm font-medium text-gray-700">Enable automatic syncing</span>
                                                    </label>
                                                </div>

                                                <div v-if="settingsForm.auto_sync_enabled">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sync Frequency</label>
                                                    <select
                                                        v-model="settingsForm.sync_frequency"
                                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm"
                                                    >
                                                        <option value="hourly">Every hour</option>
                                                        <option value="daily">Daily</option>
                                                        <option value="weekly">Weekly</option>
                                                    </select>
                                                </div>

                                                <div class="border-t border-gray-200 pt-4">
                                                    <p class="text-sm font-medium text-gray-700 mb-3">What to sync:</p>
                                                    <div class="space-y-2">
                                                        <label class="flex items-center">
                                                            <input
                                                                v-model="settingsForm.sync_settings.sync_orders"
                                                                type="checkbox"
                                                                class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                                                            />
                                                            <span class="ml-2 text-sm text-gray-700">Orders</span>
                                                        </label>
                                                        <label class="flex items-center">
                                                            <input
                                                                v-model="settingsForm.sync_settings.sync_products"
                                                                type="checkbox"
                                                                class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                                                            />
                                                            <span class="ml-2 text-sm text-gray-700">Products (Coming Soon)</span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="flex space-x-3 pt-4">
                                                    <button
                                                        type="submit"
                                                        :disabled="settingsForm.processing"
                                                        class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                                                    >
                                                        Save Settings
                                                    </button>
                                                    <button
                                                        type="button"
                                                        @click="showSettingsModal = false"
                                                        class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                                    >
                                                        Cancel
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </DialogPanel>
                                </TransitionChild>
                            </div>
                        </div>
                    </Dialog>
                </TransitionRoot>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import BusinessLayout from '@/Layouts/BusinessLayout.vue'
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { format, parseISO } from 'date-fns'

const props = defineProps({
    connection: Object,
    syncLogs: Array,
})

const syncing = ref(false)
const showSettingsModal = ref(false)

const connectionForm = useForm({
    shop_domain: '',
    access_token: '',
})

const settingsForm = useForm({
    auto_sync_enabled: props.connection?.auto_sync_enabled || false,
    sync_frequency: props.connection?.sync_frequency || 'daily',
    sync_settings: {
        sync_orders: props.connection?.sync_settings?.sync_orders ?? true,
        sync_products: props.connection?.sync_settings?.sync_products ?? false,
    },
})

function connectShopify() {
    connectionForm.post(route('business.integrations.shopify.save-credentials'), {
        preserveScroll: true,
        onSuccess: () => {
            connectionForm.reset()
        },
    })
}

function confirmDisconnect() {
    if (confirm('Are you sure you want to disconnect your Shopify store? This will not delete your synced data.')) {
        router.post(route('business.integrations.shopify.disconnect'), {}, {
            preserveScroll: true,
        })
    }
}

function triggerSync() {
    if (syncing.value) return
    
    syncing.value = true
    router.post(route('business.integrations.shopify.sync'), {
        date_range: 'last_30_days',
    }, {
        preserveScroll: true,
        onFinish: () => {
            syncing.value = false
        },
    })
}

function openSettings() {
    settingsForm.auto_sync_enabled = props.connection?.auto_sync_enabled || false
    settingsForm.sync_frequency = props.connection?.sync_frequency || 'daily'
    settingsForm.sync_settings = props.connection?.sync_settings || { sync_orders: true, sync_products: false }
    showSettingsModal.value = true
}

function saveSettings() {
    settingsForm.patch(route('business.integrations.shopify.update-settings'), {
        preserveScroll: true,
        onSuccess: () => {
            showSettingsModal.value = false
        },
    })
}

function formatDate(dateString) {
    if (!dateString) return 'Never'
    try {
        return format(parseISO(dateString), 'MMM d, yyyy h:mm a')
    } catch {
        return dateString
    }
}
</script>
