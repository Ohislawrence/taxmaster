<template>
    <AdminLayout title="Sync Failure Details">
        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Sync Failure Details</h1>
                        <p v-if="bankAccount" class="text-gray-600 mt-1">
                            {{ bankAccount.bank_name }} • {{ bankAccount.account_number }}
                        </p>
                    </div>
                    <span :class="notification.is_read ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                        class="px-4 py-2 rounded-lg text-sm font-medium">
                        {{ notification.is_read ? 'Resolved' : 'Unresolved' }}
                    </span>
                </div>
            </div>

            <!-- Error Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Error Information</h2>
                <div class="space-y-4 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div>
                        <p class="text-sm font-medium text-gray-700 mb-1">Error Message</p>
                        <p class="text-gray-900 font-mono text-sm break-words">
                            {{ notification.data.error_message }}
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-700 font-medium">Failed Attempts</p>
                            <p class="text-gray-900">{{ notification.data.attempt_count }}/3</p>
                        </div>
                        <div>
                            <p class="text-gray-700 font-medium">First Failure</p>
                            <p class="text-gray-900">{{ new Date(notification.created_at).toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Account Status -->
            <div v-if="bankAccount" class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Bank Account Status</h2>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-600 font-medium mb-1">Account Status</p>
                        <span :class="bankAccount.is_active ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'"
                            class="px-3 py-1 rounded text-sm font-medium display-inline-block">
                            {{ bankAccount.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 font-medium mb-1">Last Sync</p>
                        <p class="text-gray-900">
                            {{ bankAccount.last_sync_at
                                ? new Date(bankAccount.last_sync_at).toLocaleString()
                                : 'Never' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Failure Context -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Failure Context</h2>
                <div class="space-y-3 text-sm">
                    <div class="grid grid-cols-2">
                        <p class="text-gray-600">Bank Name</p>
                        <p class="text-gray-900 font-medium">{{ notification.data.bank_name }}</p>
                    </div>
                    <div class="grid grid-cols-2">
                        <p class="text-gray-600">Account Number (Masked)</p>
                        <p class="text-gray-900 font-medium">{{ notification.data.account_number }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
                <div class="flex gap-3 flex-wrap">
                    <button
                        @click="retrySync"
                        :disabled="isRetrying"
                        class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ isRetrying ? 'Retrying...' : 'Retry Sync' }}
                    </button>

                    <button
                        v-if="!notification.is_read"
                        @click="markResolved"
                        :disabled="isResolving"
                        class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50"
                    >
                        {{ isResolving ? 'Resolving...' : 'Mark as Resolved' }}
                    </button>

                    <Link href="/admin/sync-failures" class="px-6 py-2 border border-gray-300 rounded hover:bg-gray-50 inline-block">
                        Back to List
                    </Link>
                </div>
            </div>

            <!-- Troubleshooting Tips -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="font-semibold text-blue-900 mb-3">Troubleshooting Tips</h3>
                <ul class="text-sm text-blue-900 space-y-2">
                    <li>• Verify the bank account credentials in Mono are up-to-date</li>
                    <li>• Check if the account requires re-authentication in the Mono app</li>
                    <li>• Ensure the API token hasn't expired or been revoked</li>
                    <li>• Contact support if the issue persists after retry</li>
                </ul>
            </div>
        </div>
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';

export default {
    components: { AdminLayout, Link },
    props: ['notification', 'bankAccount'],
    data() {
        return {
            isRetrying: false,
            isResolving: false,
        };
    },
    methods: {
        async retrySync() {
            if (!confirm('Retry syncing this bank account?')) return;

            this.isRetrying = true;
            try {
                const response = await fetch(route('admin.sync-failures.retry', this.notification.id), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                if (response.ok) {
                    alert('Sync retry queued successfully');
                    router.push(route('admin.sync-failures.index'));
                }
            } catch (error) {
                console.error('Retry failed:', error);
                alert('Failed to queue retry');
            } finally {
                this.isRetrying = false;
            }
        },

        async markResolved() {
            this.isResolving = true;
            try {
                const response = await fetch(route('admin.sync-failures.resolve', this.notification.id), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                if (response.ok) {
                    alert('Sync failure marked as resolved');
                    router.reload();
                }
            } catch (error) {
                console.error('Error resolving:', error);
                alert('Failed to mark as resolved');
            } finally {
                this.isResolving = false;
            }
        },
    },
};
</script>
