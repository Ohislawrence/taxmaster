<template>
    <BusinessLayout>
        <Head title="Dashboard" />

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <!-- Subscription Status Banner -->
            <SubscriptionBanner :currentSubscription="currentSubscription" :usageStats="usageStats" class="mb-8" />

            <!-- Get Started Widget -->
            <GetStartedWidget :showWidget="true" class="mb-8" />

            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Welcome, {{ business.name }}</h1>
                <p class="text-gray-600 mt-1">{{ business.description || 'Manage your business tax compliance and finances' }}</p>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-gray-600 text-sm font-medium">Bank Balance</div>
                    <div class="text-3xl font-bold text-green-600 mt-2">₦{{ formatCurrency(stats.bank_balance) }}</div>
                    <Link href="/business/bank-accounts" class="text-blue-600 text-sm mt-2 hover:underline">View Accounts →</Link>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-gray-600 text-sm font-medium">This Month Income</div>
                    <div class="text-3xl font-bold text-blue-600 mt-2">₦{{ formatCurrency(stats.monthly_income) }}</div>
                    <p class="text-gray-500 text-xs mt-2">Expenses: ₦{{ formatCurrency(stats.monthly_expenses) }}</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-gray-600 text-sm font-medium">Pending Deadlines</div>
                    <div class="text-3xl font-bold text-yellow-600 mt-2">{{ stats.pending_deadlines }}</div>
                    <p v-if="stats.overdue_deadlines > 0" class="text-red-600 text-sm mt-2 font-semibold">{{ stats.overdue_deadlines }} overdue!</p>
                    <Link v-else href="/business/compliance" class="text-blue-600 text-sm mt-2 hover:underline">View Calendar →</Link>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-gray-600 text-sm font-medium">VAT Pending</div>
                    <div class="text-3xl font-bold text-orange-600 mt-2">₦{{ formatCurrency(stats.vat_pending) }}</div>
                    <Link href="/business/vat-returns" class="text-blue-600 text-sm mt-2 hover:underline">View VAT →</Link>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <Link href="/business/bank-accounts" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-center font-medium">
                    🏦 Bank Accounts
                </Link>
                <Link href="/business/transactions/create" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-center font-medium">
                    + Record Transaction
                </Link>
                <Link href="/business/vat-returns" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-center font-medium">
                    📊 VAT Returns
                </Link>
                <Link href="/business/compliance" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg text-center font-medium">
                    📅 Compliance
                </Link>
            </div>

            <!-- Upcoming Compliance Deadlines -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">📅 Upcoming Deadlines (Next 30 Days)</h2>
                    <Link href="/business/compliance" class="text-blue-600 hover:text-blue-800">View Calendar</Link>
                </div>
                <div v-if="upcomingDeadlines.length > 0" class="space-y-3">
                    <div
                        v-for="deadline in upcomingDeadlines"
                        :key="deadline.id"
                        class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50"
                    >
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ deadline.deadline_type }}</p>
                            <p class="text-sm text-gray-600">{{ deadline.description }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">{{ formatDate(deadline.due_date) }}</p>
                            <span
                                :class="getDaysUntilClass(deadline.due_date)"
                                class="text-xs px-2 py-1 rounded-full"
                            >
                                {{ getDaysUntil(deadline.due_date) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                    🎉 No upcoming deadlines! You're all caught up.
                </div>
            </div>

            <!-- Bank Accounts & Recent Transactions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Bank Accounts -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">🏦 Bank Accounts</h2>
                        <Link href="/business/bank-accounts" class="text-blue-600 hover:text-blue-800">Manage</Link>
                    </div>
                    <div v-if="bankAccounts.length > 0" class="space-y-3">
                        <div
                            v-for="account in bankAccounts"
                            :key="account.id"
                            class="flex justify-between items-center p-3 border rounded-lg"
                        >
                            <div>
                                <p class="font-medium text-gray-900">{{ account.bank_name }}</p>
                                <p class="text-sm text-gray-600">{{ account.account_number }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-green-600">₦{{ formatCurrency(account.balance) }}</p>
                                <p class="text-xs text-gray-500">{{ account.currency }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        No bank accounts connected yet.
                        <Link href="/business/bank-accounts" class="text-blue-600 hover:underline block mt-2">Connect account</Link>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">💰 Recent Transactions</h2>
                        <Link href="/business/transactions" class="text-blue-600 hover:text-blue-800">View All</Link>
                    </div>
                    <div v-if="recentTransactions.length > 0" class="space-y-2">
                        <div
                            v-for="transaction in recentTransactions"
                            :key="transaction.id"
                            class="flex justify-between items-center p-2 hover:bg-gray-50 rounded"
                        >
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ transaction.description }}</p>
                                <p class="text-xs text-gray-500">{{ transaction.category }} • {{ formatDate(transaction.transaction_date) }}</p>
                            </div>
                            <p
                                :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'"
                                class="font-semibold text-sm"
                            >
                                {{ transaction.type === 'income' ? '+' : '-' }}₦{{ formatCurrency(transaction.amount) }}
                            </p>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        No transactions yet.
                        <Link href="/business/transactions/create" class="text-blue-600 hover:underline block mt-2">Record transaction</Link>
                    </div>
                </div>
            </div>

            <!-- Recent VAT Returns -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">📊 Recent VAT Returns</h2>
                    <Link href="/business/vat-returns" class="text-blue-600 hover:text-blue-800">View All</Link>
                </div>
                <div v-if="recentVatReturns.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-4 font-medium text-gray-600">Period</th>
                                <th class="text-right py-2 px-4 font-medium text-gray-600">Output VAT</th>
                                <th class="text-right py-2 px-4 font-medium text-gray-600">Input VAT</th>
                                <th class="text-right py-2 px-4 font-medium text-gray-600">Net VAT</th>
                                <th class="text-left py-2 px-4 font-medium text-gray-600">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="vatReturn in recentVatReturns" :key="vatReturn.id" class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">{{ vatReturn.period_label }}</td>
                                <td class="py-3 px-4 text-right">₦{{ formatCurrency(vatReturn.output_vat) }}</td>
                                <td class="py-3 px-4 text-right">₦{{ formatCurrency(vatReturn.input_vat) }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-orange-600">₦{{ formatCurrency(vatReturn.net_vat) }}</td>
                                <td class="py-3 px-4">
                                    <span :class="getVatStatusClass(vatReturn.status)" class="px-3 py-1 rounded-full text-sm font-medium capitalize">
                                        {{ vatReturn.status_label }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                    No VAT returns yet. <Link href="/business/vat-returns" class="text-blue-600 hover:underline">Get started</Link>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import SubscriptionBanner from '@/Components/Business/SubscriptionBanner.vue';import GetStartedWidget from '@/Components/GetStarted/Widget.vue';
defineProps({
    business: Object,
    stats: Object,
    upcomingDeadlines: Array,
    recentTransactions: Array,
    bankAccounts: Array,
    recentVatReturns: Array,
    currentSubscription: Object,
    usageStats: Object,
});

function formatCurrency(value) {
    return new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2 }).format(value || 0);
}

function formatDate(date) {
    return date ? new Date(date).toLocaleDateString('en-NG', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';
}

function getDaysUntil(dueDate) {
    const today = new Date();
    const due = new Date(dueDate);
    const days = Math.ceil((due - today) / (1000 * 60 * 60 * 24));

    if (days < 0) return 'Overdue';
    if (days === 0) return 'Due Today';
    if (days === 1) return 'Due Tomorrow';
    return `${days} days left`;
}

function getDaysUntilClass(dueDate) {
    const today = new Date();
    const due = new Date(dueDate);
    const days = Math.ceil((due - today) / (1000 * 60 * 60 * 24));

    if (days < 0) return 'bg-red-100 text-red-800 font-bold';
    if (days <= 3) return 'bg-orange-100 text-orange-800 font-semibold';
    if (days <= 7) return 'bg-yellow-100 text-yellow-800';
    return 'bg-green-100 text-green-800';
}

function getVatStatusClass(status) {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        submitted: 'bg-blue-100 text-blue-800',
        paid: 'bg-green-100 text-green-800',
        overdue: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}
</script>

