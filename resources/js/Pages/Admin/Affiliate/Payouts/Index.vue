<template>
    <AdminLayout>
        <Head title="Affiliate Payouts" />

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Affiliate Payouts</h1>

            <div class="flex items-center gap-3">
                <select v-model="filter" @change="applyFilter" class="border rounded px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="approved">Approved</option>
                    <option value="unapproved">Unapproved</option>
                    <option value="paid">Paid</option>
                    <option value="unpaid">Unpaid</option>
                </select>
                <button @click="bulkApprove" :disabled="selected.length===0" class="px-3 py-2 bg-blue-600 text-white rounded text-sm">Approve Selected</button>
            </div>
        </div>

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b">
                        <th class="text-left p-3 w-8"><input type="checkbox" @change="toggleAll" :checked="allSelected" /></th>
                        <th class="text-left p-3">Accountant</th>
                        <th class="text-left p-3">Bank Details</th>
                        <th class="text-left p-3">Business</th>
                        <th class="text-left p-3">Amount</th>
                        <th class="text-left p-3">Approved</th>
                        <th class="text-left p-3">Paid</th>
                        <th class="text-left p-3">Created</th>
                        <th class="text-right p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in payoutsData" :key="p.id" class="border-b hover:bg-gray-50">
                        <td class="p-3"><input type="checkbox" :value="p.id" v-model="selected" /></td>
                        <td class="p-3">{{ p.referral?.accountant?.name || '—' }}</td>
                        <td class="p-3">
                            <div class="text-sm text-gray-700">{{ p.referral?.accountant?.affiliate_bank_name || '—' }}</div>
                            <div class="text-xs text-gray-500">{{ p.referral?.accountant?.affiliate_bank_account_name || '' }}</div>
                            <div class="text-xs text-gray-500">{{ p.referral?.accountant?.affiliate_bank_account_number || '' }} {{ p.referral?.accountant?.affiliate_bank_code ? '• ' + p.referral.accountant.affiliate_bank_code : '' }}</div>
                        </td>
                        <td class="p-3">{{ p.referral?.business?.name || '—' }}</td>
                        <td class="p-3">₦{{ formatCurrency(p.amount) }}</td>
                        <td class="p-3"> <span v-if="p.approved" class="text-green-700">Yes</span><span v-else class="text-gray-600">No</span></td>
                        <td class="p-3"> <span v-if="p.paid" class="text-green-700">Yes</span><span v-else class="text-gray-600">No</span></td>
                        <td class="p-3">{{ formatDate(p.created_at) }}</td>
                        <td class="p-3 text-right">
                            <div class="inline-flex gap-2">
                                <form :action="route('admin.affiliate.payouts.approve', { payout: p.id })" method="post">
                                    <input type="hidden" name="_token" :value="page.props.csrf_token">
                                    <button v-if="!p.approved" type="submit" class="px-3 py-1 bg-blue-600 text-white rounded">Approve</button>
                                </form>

                                <form :action="route('admin.affiliate.payouts.mark-paid', { payout: p.id })" method="post">
                                    <input type="hidden" name="_token" :value="page.props.csrf_token">
                                    <button v-if="p.approved && !p.paid" type="submit" class="px-3 py-1 bg-green-600 text-white rounded">Mark Paid</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex items-center justify-end space-x-2">
            <button v-if="payoutsMeta.prev" @click="visit(payoutsMeta.prev)" class="px-3 py-1 border rounded">Prev</button>
            <span class="text-sm text-gray-600">Page {{ payoutsMeta.current_page }} of {{ payoutsMeta.last_page }}</span>
            <button v-if="payoutsMeta.next" @click="visit(payoutsMeta.next)" class="px-3 py-1 border rounded">Next</button>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const payouts = page.props.payouts || { data: [], meta: {} };

const selected = ref([]);
const filter = ref(page.props.filter || '');

const payoutsData = computed(() => payouts.data || page.props.payouts || []);
const payoutsMeta = computed(() => payouts.meta || {});

const allSelected = computed(() => {
    return payoutsData.value.length > 0 && selected.value.length === payoutsData.value.length;
});

const toggleAll = () => {
    if (allSelected.value) {
        selected.value = [];
    } else {
        selected.value = payoutsData.value.map(p => p.id);
    }
};

const applyFilter = () => {
    router.get(route('admin.affiliate.payouts.index'), { filter: filter.value });
};

const bulkApprove = () => {
    if (selected.value.length === 0) return;
    router.post(route('admin.affiliate.payouts.bulk-approve'), { ids: selected.value });
};

const visit = (url) => {
    // use Inertia GET to the provided url
    router.visit(url);
};

const formatCurrency = (amount) => {
    if (!amount) return '0.00';
    return new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount);
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleString();
};
</script>

<style scoped>
</style>
