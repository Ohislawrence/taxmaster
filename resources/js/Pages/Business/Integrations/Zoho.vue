<template>
    <BusinessLayout>
        <Head title="Zoho Books Integration" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Zoho Books Integration</h1>
                            <p class="mt-2 text-sm text-gray-600">
                                Automatically sync invoices, bills, and expenses from Zoho Books
                            </p>
                        </div>
                        <div class="flex space-x-3">
                            <button
                                v-if="connection && connection.status === 'active'"
                                @click="openSettings"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
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
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
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
                                    <div class="w-16 h-16 bg-orange-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-10 h-10 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Connected to Zoho Books</h3>
                                    <p class="mt-1 text-sm text-gray-600">{{ connection.organization_name }}</p>
                                    <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                                        <span>Connected {{ formatDate(connection.created_at) }}</span>
                                        <span>•</span>
                                        <span v-if="connection.last_synced_at">Last synced {{ formatDate(connection.last_synced_at) }}</span>
                                        <span v-else>Never synced</span>
                                    </div>
                                    <div v-if="connection.auto_sync_enabled" class="mt-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                            </svg>
                                            Auto-sync {{ connection.sync_frequency }}
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

                    <div v-else-if="connection && connection.status === 'expired'" class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-r-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-medium text-yellow-800">Connection Expired</h3>
                                <p class="mt-1 text-sm text-yellow-700">
                                    Your Zoho Books connection has expired. Please reconnect to continue syncing.
                                </p>
                                <div class="mt-4">
                                    <a
                                        :href="route('business.integrations.zoho.connect')"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700"
                                    >
                                        Reconnect Zoho Books
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Credentials Setup (No credentials yet) -->
                    <div v-else-if="!connection || !connection.has_credentials" class="bg-white shadow rounded-lg p-8">
                        <div class="max-w-2xl mx-auto">
                            <div class="text-center mb-8">
                                <div class="mx-auto w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Set Up Zoho Books Integration</h3>
                                <p class="text-sm text-gray-600">
                                    To connect TaxMaster with your Zoho Books account, you need to create a Zoho API client and provide its credentials.
                                </p>
                            </div>

                            <!-- Instructions -->
                            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
                                <h4 class="font-semibold text-orange-900 mb-2">How to get your Zoho Books credentials:</h4>
                                <ol class="text-sm text-orange-800 space-y-1 list-decimal list-inside">
                                    <li>Go to <a href="https://api-console.zoho.com/" target="_blank" class="underline font-medium">Zoho API Console</a></li>
                                    <li>Sign in with your Zoho account</li>
                                    <li>Click "Add Client" → Select "Server-based Applications"</li>
                                    <li>Set Client Name (e.g., "TaxMaster Integration")</li>
                                    <li>Set Authorized Redirect URI to: <code class="bg-white px-2 py-0.5 rounded text-xs">{{ credentialsForm.redirect_uri }}</code></li>
                                    <li>Copy your <strong>Client ID</strong> and <strong>Client Secret</strong></li>
                                    <li>Paste the credentials below</li>
                                </ol>
                            </div>

                            <!-- Credentials Form -->
                            <form @submit.prevent="saveCredentials" class="space-y-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Client ID <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="credentialsForm.client_id"
                                        type="text"
                                        required
                                        placeholder="e.g., 1000.XXXXXXXXXXXX"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Client Secret <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="credentialsForm.client_secret"
                                        type="password"
                                        required
                                        placeholder="Enter your Client Secret"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Redirect URI <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="credentialsForm.redirect_uri"
                                        type="url"
                                        required
                                        readonly
                                        class="block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">Use this exact URL in your Zoho API Console settings</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Data Center <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="credentialsForm.data_center"
                                        required
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                    >
                                        <option value="com">United States (.com)</option>
                                        <option value="eu">Europe (.eu)</option>
                                        <option value="in">India (.in)</option>
                                        <option value="com.au">Australia (.com.au)</option>
                                        <option value="com.cn">China (.com.cn)</option>
                                        <option value="jp">Japan (.jp)</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">Select your Zoho data center location</p>
                                </div>

                                <div class="flex items-center space-x-3 pt-4">
                                    <button
                                        type="submit"
                                        :disabled="savingCredentials"
                                        class="flex-1 inline-flex justify-center items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50"
                                    >
                                        <svg v-if="!savingCredentials" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ savingCredentials ? 'Saving...' : 'Save & Continue' }}
                                    </button>
                                </div>
                            </form>

                            <div class="mt-6 text-center">
                                <p class="text-xs text-gray-500">
                                    🔒 Your credentials are encrypted and stored securely
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Ready to Connect (Credentials set, not connected yet) -->
                    <div v-else-if="connection && connection.has_credentials && connection.status === 'credentials_set'" class="bg-white shadow rounded-lg p-12 text-center">
                        <div class="mx-auto w-24 h-24 bg-orange-100 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-12 h-12 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Credentials Configured Successfully!</h3>
                        <p class="text-sm text-gray-600 mb-6 max-w-md mx-auto">
                            Your Zoho Books API credentials are set up. Now connect your Zoho Books account to start syncing data.
                        </p>
                        <div class="mb-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                Data Center: <strong class="ml-1 uppercase">{{ connection.data_center }}</strong>
                            </span>
                        </div>
                        <a
                            :href="route('business.integrations.zoho.connect')"
                            class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-orange-600 hover:bg-orange-700"
                        >
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                            </svg>
                            Connect to Zoho Books
                        </a>
                        <p class="mt-4 text-xs text-gray-500">
                            Secure OAuth 2.0 authentication • Your Zoho login is never shared with TaxMaster
                        </p>
                        <button
                            @click="showCredentialsModal = true"
                            class="mt-4 text-xs text-gray-500 hover:text-gray-700 underline"
                        >
                            Update credentials
                        </button>
                    </div>

                    <!-- Fallback: Should not reach here -->
                    <div v-else class="bg-white shadow rounded-lg p-12 text-center">
                        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Something went wrong</h3>
                        <p class="text-sm text-gray-600 mb-6">Please refresh the page and try again.</p>
                    </div>
                </div>

                <!-- Sync Logs -->
                <div v-if="connection && connection.status === 'active'" class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Sync History</h3>
                    </div>
                    <div v-if="syncLogs.length > 0" class="divide-y divide-gray-200">
                        <div
                            v-for="log in syncLogs"
                            :key="log.id"
                            class="px-6 py-4 hover:bg-gray-50 cursor-pointer"
                            @click="viewLogDetails(log)"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4 flex-1">
                                    <div class="flex-shrink-0">
                                        <div v-if="log.status === 'completed'" class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div v-else-if="log.status === 'failed'" class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div v-else class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-orange-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-3">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ log.sync_type === 'all' ? 'Full Sync' : log.sync_type === 'invoices' ? 'Invoices Only' : 'Bills Only' }}
                                            </p>
                                            <span :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                log.status === 'completed' ? 'bg-green-100 text-green-800' :
                                                log.status === 'failed' ? 'bg-red-100 text-red-800' :
                                                'bg-orange-100 text-orange-800'
                                            ]">
                                                {{ log.status }}
                                            </span>
                                        </div>
                                        <div class="mt-1 flex items-center space-x-4 text-xs text-gray-500">
                                            <span>{{ formatDate(log.started_at) }}</span>
                                            <span v-if="log.status === 'completed'">
                                                • {{ log.success_count }} successful, {{ log.failure_count }} failed
                                            </span>
                                            <span v-if="log.duration_seconds">
                                                • {{ log.duration_seconds }}s
                                            </span>
                                        </div>
                                        <div v-if="log.status === 'failed' && log.error_message" class="mt-1">
                                            <p class="text-xs text-red-600">{{ log.error_message }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">No sync history yet</p>
                        <p class="text-xs text-gray-400">Click "Sync Now" to start syncing your data</p>
                    </div>
                </div>

                <!-- Settings Modal -->
                <TransitionRoot :show="showSettingsModal" as="template">
                    <Dialog as="div" class="relative z-50" @close="showSettingsModal = false">
                        <TransitionChild
                            enter="ease-out duration-300"
                            enter-from="opacity-0"
                            enter-to="opacity-100"
                            leave="ease-in duration-200"
                            leave-from="opacity-100"
                            leave-to="opacity-0"
                        >
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                        </TransitionChild>

                        <div class="fixed inset-0 z-10 overflow-y-auto">
                            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                <TransitionChild
                                    enter="ease-out duration-300"
                                    enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                    enter-to="opacity-100 translate-y-0 sm:scale-100"
                                    leave="ease-in duration-200"
                                    leave-from="opacity-100 translate-y-0 sm:scale-100"
                                    leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                >
                                    <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                                        <div>
                                            <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                                <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                                                    Zoho Books Sync Settings
                                                </DialogTitle>
                                                <div class="mt-6 space-y-6">
                                                    <!-- Auto Sync Toggle -->
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <label class="text-sm font-medium text-gray-900">Automatic Sync</label>
                                                            <p class="text-xs text-gray-500">Sync data automatically on a schedule</p>
                                                        </div>
                                                        <button
                                                            type="button"
                                                            @click="settingsForm.auto_sync_enabled = !settingsForm.auto_sync_enabled"
                                                            :class="[
                                                                settingsForm.auto_sync_enabled ? 'bg-orange-600' : 'bg-gray-200',
                                                                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-orange-600 focus:ring-offset-2'
                                                            ]"
                                                        >
                                                            <span
                                                                :class="[
                                                                    settingsForm.auto_sync_enabled ? 'translate-x-5' : 'translate-x-0',
                                                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                                                                ]"
                                                            />
                                                        </button>
                                                    </div>

                                                    <!-- Sync Frequency -->
                                                    <div v-if="settingsForm.auto_sync_enabled">
                                                        <label class="block text-sm font-medium text-gray-900">Sync Frequency</label>
                                                        <select
                                                            v-model="settingsForm.sync_frequency"
                                                            class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-orange-500 focus:outline-none focus:ring-orange-500 sm:text-sm"
                                                        >
                                                            <option value="hourly">Every Hour</option>
                                                            <option value="daily">Daily</option>
                                                            <option value="weekly">Weekly</option>
                                                        </select>
                                                        <p class="mt-1 text-xs text-gray-500">
                                                            How often to automatically sync data from Zoho Books
                                                        </p>
                                                    </div>

                                                    <!-- What to Sync -->
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-900 mb-3">What to Sync</label>
                                                        <div class="space-y-3">
                                                            <div class="flex items-center">
                                                                <input
                                                                    id="sync-invoices"
                                                                    v-model="settingsForm.sync_settings.sync_invoices"
                                                                    type="checkbox"
                                                                    class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-600"
                                                                />
                                                                <label for="sync-invoices" class="ml-3 block text-sm text-gray-700">
                                                                    Sales Invoices
                                                                </label>
                                                            </div>
                                                            <div class="flex items-center">
                                                                <input
                                                                    id="sync-bills"
                                                                    v-model="settingsForm.sync_settings.sync_bills"
                                                                    type="checkbox"
                                                                    class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-600"
                                                                />
                                                                <label for="sync-bills" class="ml-3 block text-sm text-gray-700">
                                                                    Bills & Expenses
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-6 sm:flex sm:flex-row-reverse gap-3">
                                            <button
                                                type="button"
                                                @click="saveSettings"
                                                :disabled="savingSettings"
                                                class="inline-flex w-full justify-center rounded-md bg-orange-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-500 sm:w-auto disabled:opacity-50"
                                            >
                                                {{ savingSettings ? 'Saving...' : 'Save Changes' }}
                                            </button>
                                            <button
                                                type="button"
                                                @click="showSettingsModal = false"
                                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </DialogPanel>
                                </TransitionChild>
                            </div>
                        </div>
                    </Dialog>
                </TransitionRoot>

                <!-- Sync Modal -->
                <TransitionRoot :show="showSyncModal" as="template">
                    <Dialog as="div" class="relative z-50" @close="showSyncModal = false">
                        <TransitionChild
                            enter="ease-out duration-300"
                            enter-from="opacity-0"
                            enter-to="opacity-100"
                            leave="ease-in duration-200"
                            leave-from="opacity-100"
                            leave-to="opacity-0"
                        >
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                        </TransitionChild>

                        <div class="fixed inset-0 z-10 overflow-y-auto">
                            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                <TransitionChild
                                    enter="ease-out duration-300"
                                    enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                    enter-to="opacity-100 translate-y-0 sm:scale-100"
                                    leave="ease-in duration-200"
                                    leave-from="opacity-100 translate-y-0 sm:scale-100"
                                    leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                >
                                    <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                                        <div>
                                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-orange-100">
                                                <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                            </div>
                                            <div class="mt-3 text-center sm:mt-5">
                                                <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                                                    Sync Zoho Books Data
                                                </DialogTitle>
                                                <div class="mt-6 space-y-4">
                                                    <div>
                                                        <label class="block text-left text-sm font-medium text-gray-900 mb-2">Date Range</label>
                                                        <select
                                                            v-model="syncForm.date_range"
                                                            class="block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-orange-500 focus:outline-none focus:ring-orange-500 sm:text-sm"
                                                        >
                                                            <option value="last_30_days">Last 30 Days</option>
                                                            <option value="last_month">Last Month</option>
                                                            <option value="last_3_months">Last 3 Months</option>
                                                            <option value="last_6_months">Last 6 Months</option>
                                                            <option value="this_year">This Year</option>
                                                            <option value="all_time">All Time</option>
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label class="block text-left text-sm font-medium text-gray-900 mb-2">What to Sync</label>
                                                        <select
                                                            v-model="syncForm.sync_type"
                                                            class="block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-orange-500 focus:outline-none focus:ring-orange-500 sm:text-sm"
                                                        >
                                                            <option value="all">Everything (Invoices & Bills)</option>
                                                            <option value="invoices">Invoices Only</option>
                                                            <option value="bills">Bills Only</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-6 sm:flex sm:flex-row-reverse gap-3">
                                            <button
                                                type="button"
                                                @click="startSync"
                                                :disabled="syncing"
                                                class="inline-flex w-full justify-center rounded-md bg-orange-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-500 sm:w-auto disabled:opacity-50"
                                            >
                                                {{ syncing ? 'Starting Sync...' : 'Start Sync' }}
                                            </button>
                                            <button
                                                type="button"
                                                @click="showSyncModal = false"
                                                :disabled="syncing"
                                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto disabled:opacity-50"
                                            >
                                                Cancel
                                            </button>
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
import { ref, reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue';
import { format, parseISO } from 'date-fns';

const props = defineProps({
    connection: Object,
    syncLogs: {
        type: Array,
        default: () => []
    }
});

const syncing = ref(false);
const showSettingsModal = ref(false);
const showCredentialsModal = ref(false);
const showSyncModal = ref(false);
const savingSettings = ref(false);
const savingCredentials = ref(false);

const settingsForm = reactive({
    auto_sync_enabled: props.connection?.auto_sync_enabled || false,
    sync_frequency: props.connection?.sync_frequency || 'daily',
    sync_settings: {
        sync_invoices: props.connection?.sync_settings?.sync_invoices ?? true,
        sync_bills: props.connection?.sync_settings?.sync_bills ?? true,
    }
});

const credentialsForm = reactive({
    client_id: '',
    client_secret: '',
    redirect_uri: typeof window !== 'undefined' ? window.location.origin + '/business/integrations/zoho/callback' : '/business/integrations/zoho/callback',
    data_center: 'com'
});

const syncForm = reactive({
    date_range: 'last_30_days',
    sync_type: 'all'
});

const formatDate = (dateString) => {
    if (!dateString) return 'Never';
    try {
        return format(parseISO(dateString), 'MMM d, yyyy h:mm a');
    } catch {
        return dateString;
    }
};

const openSettings = () => {
    showSettingsModal.value = true;
};

const saveSettings = () => {
    savingSettings.value = true;
    router.patch(route('business.integrations.zoho.update-settings'), settingsForm, {
        onSuccess: () => {
            showSettingsModal.value = false;
        },
        onFinish: () => {
            savingSettings.value = false;
        }
    });
};

const saveCredentials = () => {
    savingCredentials.value = true;
    router.post(route('business.integrations.zoho.save-credentials'), credentialsForm, {
        onSuccess: () => {
            // Credentials saved, page will reload with new state
        },
        onFinish: () => {
            savingCredentials.value = false;
        }
    });
};

const triggerSync = () => {
    showSyncModal.value = true;
};

const startSync = () => {
    syncing.value = true;
    router.post(route('business.integrations.zoho.sync'), syncForm, {
        onSuccess: () => {
            showSyncModal.value = false;
        },
        onFinish: () => {
            syncing.value = false;
        }
    });
};

const confirmDisconnect = () => {
    if (confirm('Are you sure you want to disconnect Zoho Books? Existing synced transactions will not be deleted, but new data will not sync.')) {
        router.post(route('business.integrations.zoho.disconnect'), {}, {
            onSuccess: () => {
                // Connection will be removed
            }
        });
    }
};

const viewLogDetails = (log) => {
    router.get(route('business.integrations.zoho.logs.show', log.id));
};
</script>
