<template>
    <BusinessLayout>
        <Head title="Dashboard" />

        <div class="py-4 sm:py-8 px-3 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
            <!-- Get Started Widget -->
            <GetStartedWidget :showWidget="true" class="mb-2 sm:mb-6" />

            <div class="mb-4 sm:mb-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Welcome, {{ business.name }}</h1>
                <p class="text-xs sm:text-base text-gray-600 mt-1">{{ business.description || 'Manage your business tax compliance and finances' }}</p>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-4 sm:mb-8">
                <div class="bg-white rounded-lg shadow p-3 sm:p-6">
                    <div class="text-gray-600 text-xs sm:text-sm font-medium">Bank Balance</div>
                    <div class="text-lg sm:text-3xl font-bold text-green-600 mt-1 sm:mt-2">₦{{ formatCurrency(stats.bank_balance) }}</div>
                    <Link :href="route('business.banks.index')" class="text-blue-600 text-xs sm:text-sm mt-1 sm:mt-2 hover:underline block">View Accounts →</Link>
                </div>

                <div class="bg-white rounded-lg shadow p-3 sm:p-6">
                    <div class="text-gray-600 text-xs sm:text-sm font-medium">This Month Income</div>
                    <div class="text-lg sm:text-3xl font-bold text-blue-600 mt-1 sm:mt-2">₦{{ formatCurrency(stats.monthly_income) }}</div>
                    <p class="text-gray-500 text-xs mt-1">Expenses: ₦{{ formatCurrency(stats.monthly_expenses) }}</p>
                </div>

                <div class="bg-white rounded-lg shadow p-3 sm:p-6">
                    <div class="text-gray-600 text-xs sm:text-sm font-medium">Pending Deadlines</div>
                    <div class="text-lg sm:text-3xl font-bold text-yellow-600 mt-1 sm:mt-2">{{ stats.pending_deadlines }}</div>
                    <p v-if="stats.overdue_deadlines > 0" class="text-red-600 text-xs sm:text-sm mt-1 font-semibold">{{ stats.overdue_deadlines }} overdue!</p>
                    <Link v-else href="/business/compliance" class="text-blue-600 text-xs sm:text-sm mt-1 hover:underline block">View Calendar →</Link>
                </div>

                <div class="bg-white rounded-lg shadow p-3 sm:p-6">
                    <div class="text-gray-600 text-xs sm:text-sm font-medium">VAT Pending</div>
                    <div class="text-lg sm:text-3xl font-bold text-orange-600 mt-1 sm:mt-2">₦{{ formatCurrency(stats.vat_pending) }}</div>
                    <Link :href="route('business.vat.index')" class="text-blue-600 text-xs sm:text-sm mt-1 hover:underline block">View VAT →</Link>
                </div>
            </div>

            <!-- Subscription Plan & Usage -->
            <div class="bg-white rounded-lg shadow p-3 sm:p-6 mb-4 sm:mb-8">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                    <div>
                        <p class="text-gray-600 text-xs sm:text-sm font-medium">Plan & Usage</p>
                        <h2 class="text-base sm:text-xl font-bold text-gray-900 mt-1">{{ formatPlanName(usageStats?.plan_name || currentSubscription?.plan?.name || currentSubscription?.plan_type || 'No Plan') }}</h2>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1" v-if="usageStats?.renews_at || currentSubscription?.renews_at">
                            Renews on {{ formatDate(usageStats?.renews_at || currentSubscription?.renews_at) }}
                        </p>
                    </div>
                    <Link href="/business/subscription" class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-200 text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Manage Plan
                    </Link>
                </div>

                <div v-if="usageStats?.has_subscription" class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div class="rounded-lg border border-gray-100 p-3">
                        <p class="text-xs text-gray-500">Staff</p>
                        <p class="text-sm sm:text-base font-semibold text-gray-900 mt-1">{{ formatUsage(usageStats?.staff_count, usageStats?.staff_limit) }}</p>
                        <p v-if="!isUnlimitedLimit(usageStats?.staff_limit)" class="text-xs text-gray-500 mt-1">{{ usageStats?.staff_percentage || 0 }}% used</p>
                        <p v-else class="text-xs text-emerald-600 mt-1 font-medium">Unlimited</p>
                    </div>

                    <div class="rounded-lg border border-gray-100 p-3">
                        <p class="text-xs text-gray-500">Tax Returns (This Year)</p>
                        <p class="text-sm sm:text-base font-semibold text-gray-900 mt-1">{{ formatUsage(usageStats?.returns_this_year, usageStats?.returns_limit) }}</p>
                        <p v-if="!isUnlimitedLimit(usageStats?.returns_limit)" class="text-xs text-gray-500 mt-1">{{ usageStats?.returns_percentage || 0 }}% used</p>
                        <p v-else class="text-xs text-emerald-600 mt-1 font-medium">Unlimited</p>
                    </div>
                </div>

                <div v-else class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs sm:text-sm text-amber-800">
                    No active subscription. Choose a plan to unlock premium tools.
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 mb-4 sm:mb-8">
                <Link :href="route('business.banks.index')" class="bg-blue-600 hover:bg-blue-700 text-white px-2 sm:px-4 py-2 rounded-lg text-center font-medium text-xs sm:text-sm">
                    Bank Accounts
                </Link>
                <Link :href="route('business.transactions.index')" class="bg-green-600 hover:bg-green-700 text-white px-2 sm:px-4 py-2 rounded-lg text-center font-medium text-xs sm:text-sm">
                    Transaction
                </Link>
                <Link :href="route('business.vat.index')" class="bg-purple-600 hover:bg-purple-700 text-white px-2 sm:px-4 py-2 rounded-lg text-center font-medium text-xs sm:text-sm">
                    VAT
                </Link>
                <Link href="/business/compliance" class="bg-orange-600 hover:bg-orange-700 text-white px-2 sm:px-4 py-2 rounded-lg text-center font-medium text-xs sm:text-sm">
                    Compliance
                </Link>
            </div>

            <!-- Upcoming Compliance Deadlines -->
            <div class="bg-white rounded-lg shadow p-3 sm:p-6 mb-4 sm:mb-6">
                <div class="flex justify-between items-center mb-3 sm:mb-4">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900">Upcoming Deadlines</h2>
                    <Link href="/business/compliance" class="text-blue-600 hover:text-blue-800 text-xs sm:text-sm">View Calendar</Link>
                </div>
                <div v-if="upcomingDeadlines.length > 0" class="space-y-2 sm:space-y-3">
                    <div
                        v-for="deadline in upcomingDeadlines"
                        :key="deadline.id"
                        class="flex items-start justify-between gap-2 p-2 sm:p-4 border rounded-lg hover:bg-gray-50 text-sm"
                    >
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 text-sm">{{ deadline.deadline_type }}</p>
                            <p class="text-xs sm:text-sm text-gray-600">{{ deadline.description }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-xs sm:text-sm font-semibold text-gray-900">{{ formatDate(deadline.due_date) }}</p>
                            <span
                                :class="getDaysUntilClass(deadline.due_date)"
                                class="text-xs px-2 py-1 rounded-full inline-block mt-1"
                            >
                                {{ getDaysUntil(deadline.due_date) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-6 sm:py-8 text-gray-500 text-sm">
                    No upcoming deadlines. You're all caught up.
                </div>
            </div>

            <!-- Bank Accounts & Recent Transactions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                <!-- Bank Accounts -->
                <div class="bg-white rounded-lg shadow p-3 sm:p-6">
                    <div class="flex justify-between items-center mb-3 sm:mb-4">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900">Bank Accounts</h2>
                        <Link :href="route('business.banks.index')" class="text-blue-600 hover:text-blue-800 text-xs sm:text-sm">Manage</Link>
                    </div>
                    <div v-if="bankAccounts.length > 0" class="space-y-2 sm:space-y-3">
                        <div
                            v-for="account in bankAccounts"
                            :key="account.id"
                            class="flex justify-between items-center p-2 sm:p-3 border rounded-lg text-sm"
                        >
                            <div>
                                <p class="font-medium text-gray-900 text-sm">{{ account.bank_name }}</p>
                                <p class="text-xs text-gray-600">{{ account.account_number }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="font-bold text-green-600 text-sm">₦{{ formatCurrency(account.balance) }}</p>
                                <p class="text-xs text-gray-500">{{ account.currency }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-6 sm:py-8 text-gray-500 text-xs sm:text-sm">
                        No bank accounts connected yet.
                        <Link :href="route('business.banks.index')" class="text-blue-600 hover:underline block mt-2">Connect account</Link>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white rounded-lg shadow p-3 sm:p-6">
                    <div class="flex justify-between items-center mb-3 sm:mb-4">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900">Recent Transactions</h2>
                        <Link href="/business/transactions" class="text-blue-600 hover:text-blue-800 text-xs sm:text-sm">View All</Link>
                    </div>
                    <div v-if="recentTransactions.length > 0" class="space-y-1 sm:space-y-2">
                        <div
                            v-for="transaction in recentTransactions"
                            :key="transaction.id"
                            class="flex justify-between items-center p-1 sm:p-2 hover:bg-gray-50 rounded text-sm"
                        >
                            <div class="flex-1">
                                <p class="text-xs sm:text-sm font-medium text-gray-900">{{ transaction.description }}</p>
                                <p class="text-xs text-gray-500">{{ transaction.category }} • {{ formatDate(transaction.transaction_date) }}</p>
                            </div>
                            <p
                                :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'"
                                class="font-semibold text-xs sm:text-sm flex-shrink-0"
                            >
                                {{ transaction.type === 'income' ? '+' : '-' }}₦{{ formatCurrency(transaction.amount) }}
                            </p>
                        </div>
                    </div>
                    <div v-else class="text-center py-6 sm:py-8 text-gray-500 text-xs sm:text-sm">
                        No transactions yet.
                        <Link :href="route('business.transactions.index')" class="text-blue-600 hover:underline block mt-2">Record transaction</Link>
                    </div>
                </div>
            </div>

            <!-- Recent VAT Returns -->
            <div class="bg-white rounded-lg shadow p-3 sm:p-6">
                <div class="flex justify-between items-center mb-3 sm:mb-4">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900">Recent VAT</h2>
                    <Link :href="route('business.vat.index')" class="text-blue-600 hover:text-blue-800 text-xs sm:text-sm">View All</Link>
                </div>

                <!-- Desktop Table View -->
                <div v-if="recentVatReturns.length > 0" class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-3 font-medium text-gray-600 text-xs">Period</th>
                                <th class="text-right py-2 px-3 font-medium text-gray-600 text-xs">Output VAT</th>
                                <th class="text-right py-2 px-3 font-medium text-gray-600 text-xs">Input VAT</th>
                                <th class="text-right py-2 px-3 font-medium text-gray-600 text-xs">Net VAT</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600 text-xs">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="vatReturn in recentVatReturns" :key="vatReturn.id" class="border-b hover:bg-gray-50">
                                <td class="py-2 px-3 font-medium text-sm">{{ vatReturn.period_label }}</td>
                                <td class="py-2 px-3 text-right text-xs">₦{{ formatCurrency(vatReturn.output_vat) }}</td>
                                <td class="py-2 px-3 text-right text-xs">₦{{ formatCurrency(vatReturn.input_vat) }}</td>
                                <td class="py-2 px-3 text-right font-semibold text-orange-600 text-xs">₦{{ formatCurrency(vatReturn.net_vat) }}</td>
                                <td class="py-2 px-3">
                                    <span :class="getVatStatusClass(vatReturn.status)" class="px-2 py-1 rounded-full text-xs font-medium capitalize inline-block">
                                        {{ vatReturn.status_label }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div v-if="recentVatReturns.length > 0" class="sm:hidden divide-y divide-gray-200">
                    <div v-for="vatReturn in recentVatReturns" :key="vatReturn.id" class="py-3 space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">{{ vatReturn.period_label }}</p>
                                <span :class="getVatStatusClass(vatReturn.status)" class="px-2 py-1 rounded-full text-xs font-medium capitalize inline-block mt-1">
                                    {{ vatReturn.status_label }}
                                </span>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <div>
                                <p class="text-gray-600">Output</p>
                                <p class="font-semibold text-gray-900">₦{{ formatCurrency(vatReturn.output_vat) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Input</p>
                                <p class="font-semibold text-gray-900">₦{{ formatCurrency(vatReturn.input_vat) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Net</p>
                                <p class="font-semibold text-orange-600">₦{{ formatCurrency(vatReturn.net_vat) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-6 sm:py-8 text-gray-500 text-sm">
                    No VAT returns yet. <Link :href="route('business.vat.index')" class="text-blue-600 hover:underline">Get started</Link>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import GetStartedWidget from '@/Components/GetStarted/Widget.vue';

const props = defineProps({
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

function formatPlanName(name) {
    if (!name) return 'No Plan';
    return String(name)
        .replace(/[_-]+/g, ' ')
        .replace(/\b\w/g, char => char.toUpperCase());
}

function isUnlimitedLimit(limit) {
    if ((props.currentSubscription?.plan_type || '').toLowerCase() === 'enterprise') {
        return true;
    }

    const value = Number(limit);
    return Number.isFinite(value) && value >= 999;
}

function formatLimit(limit) {
    return isUnlimitedLimit(limit) ? 'Unlimited' : String(limit ?? 0);
}

function formatUsage(count, limit) {
    return `${count ?? 0}/${formatLimit(limit)}`;
}
</script>
