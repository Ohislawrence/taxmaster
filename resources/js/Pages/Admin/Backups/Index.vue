<template>
    <AdminLayout title="System Backups">
        <div class="bg-white rounded-lg shadow">
            <!-- Header -->
            <div class="border-b border-gray-200 p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">System Backups</h1>
                        <p class="text-gray-600 mt-1">Manage automated and manual database backups</p>
                    </div>
                    <button
                        @click="triggerBackup"
                        :disabled="isCreatingBackup"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                    >
                        <span v-if="!isCreatingBackup">Trigger Backup Now</span>
                        <span v-else>Creating Backup...</span>
                    </button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div v-if="backupStats && !backupStats.error" class="grid grid-cols-4 divide-x divide-gray-200 border-b border-gray-200">
                <div class="p-6">
                    <p class="text-gray-600 text-sm font-medium">Total Backups</p>
                    <p class="text-3xl font-bold text-gray-900">{{ backupStats.total_backups }}</p>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 text-sm font-medium">Last Backup</p>
                    <p class="text-lg font-semibold text-gray-900">{{ backupStats.last_backup }}</p>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 text-sm font-medium">Total Size</p>
                    <p class="text-lg font-semibold text-gray-900">{{ backupStats.total_size }}</p>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 text-sm font-medium">Status</p>
                    <p class="text-lg font-semibold text-green-600">{{ backupStats.health_status }}</p>
                </div>
            </div>

            <!-- Error Message -->
            <div v-if="backupStats && backupStats.error" class="p-6 bg-red-50 border border-red-200 rounded">
                <p class="text-red-700">{{ backupStats.error }}</p>
                <Link href="/admin/backups/health" class="text-red-600 hover:underline mt-2 inline-block">
                    Check backup health →
                </Link>
            </div>

            <!-- Health Status -->
            <div v-if="healthStatus && Object.keys(healthStatus).length" class="border-b border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Backup Destination Health</h2>
                <div class="space-y-4">
                    <div
                        v-for="(health, key) in healthStatus"
                        :key="key"
                        class="border border-gray-200 rounded-lg p-4"
                    >
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <p class="font-semibold text-gray-900">{{ health.name }}</p>
                                <p class="text-sm text-gray-600">{{ health.path }}</p>
                            </div>
                            <span :class="[
                                'px-3 py-1 rounded-full text-xs font-medium',
                                health.reachable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                            ]">
                                {{ health.reachable ? 'Reachable' : 'Unreachable' }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600">Free Storage</p>
                                <p class="font-semibold text-gray-900">{{ formatBytes(health.free_storage) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Used Storage</p>
                                <p class="font-semibold text-gray-900">{{ formatBytes(health.used_storage) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <Link href="/admin/backups/health" class="text-blue-600 hover:underline mt-4 inline-block">
                    View detailed health metrics →
                </Link>
            </div>

            <!-- Backups List -->
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Backups</h2>
                <div v-if="backups.length" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Size</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">File</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="backup in backups" :key="backup.path" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ backup.date_formatted }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ backup.size_human }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ backup.path }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-12">
                    <p class="text-gray-500">No backups found</p>
                </div>
            </div>

            <!-- Cleanup Section -->
            <div class="border-t border-gray-200 p-6 bg-gray-50">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-semibold text-gray-900">Cleanup Old Backups</p>
                        <p class="text-sm text-gray-600 mt-1">Run cleanup policy to remove old backups and free storage</p>
                    </div>
                    <button
                        @click="runCleanup"
                        :disabled="isRunningCleanup"
                        class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 disabled:opacity-50"
                    >
                        <span v-if="!isRunningCleanup">Run Cleanup</span>
                        <span v-else>Running...</span>
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';

export default {
    components: { AdminLayout, Link },
    props: ['backups', 'healthStatus', 'backupStats'],
    data() {
        return {
            isCreatingBackup: false,
            isRunningCleanup: false,
        };
    },
    methods: {
        async triggerBackup() {
            this.isCreatingBackup = true;
            try {
                const response = await fetch('/admin/backups', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                });
                if (response.ok) {
                    this.$page.props.flash.success = 'Backup initiated successfully';
                    setTimeout(() => location.reload(), 1000);
                }
            } catch (error) {
                console.error('Backup failed:', error);
            } finally {
                this.isCreatingBackup = false;
            }
        },
        async runCleanup() {
            if (!confirm('Remove old backups according to cleanup policy?')) return;
            this.isRunningCleanup = true;
            try {
                const response = await fetch('/admin/backups/cleanup', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                });
                if (response.ok) {
                    this.$page.props.flash.success = 'Cleanup completed';
                    setTimeout(() => location.reload(), 1000);
                }
            } catch (error) {
                console.error('Cleanup failed:', error);
            } finally {
                this.isRunningCleanup = false;
            }
        },
        formatBytes(bytes) {
            if (!bytes) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            let size = bytes;
            let unitIndex = 0;
            while (size >= 1024 && unitIndex < units.length - 1) {
                size /= 1024;
                unitIndex++;
            }
            return size.toFixed(2) + ' ' + units[unitIndex];
        },
    },
};
</script>
