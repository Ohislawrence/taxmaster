<template>
  <AccountantLayout>
    <Head title="Affiliate Dashboard" />

    <div class="py-6 lg:py-8 px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-6 lg:mb-8">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Affiliate Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Track your referrals, commissions, and payouts</p>
      </div>

      <!-- Bank Details & Affiliate Link Card -->
      <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden mb-6 lg:mb-8">
        <div class="p-6">
          <!-- Bank Details Section -->
          <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 pb-6 border-b border-gray-100">
            <div class="flex items-start gap-3">
              <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
              </div>
              <div>
                <h2 class="text-lg font-semibold text-gray-900">Payout Bank Details</h2>
                <p class="text-sm text-gray-500 mt-0.5">Provide your bank details so we can pay out approved affiliate commissions</p>
              </div>
            </div>
            
            <div class="flex items-center gap-2 flex-shrink-0">
              <!-- Hide/Show Toggle Button -->
              <button 
                v-if="hasBankDetails" 
                @click="showBankDetails = !showBankDetails" 
                class="px-3 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm font-medium rounded-lg transition-colors border border-gray-200 flex items-center gap-1.5"
                :title="showBankDetails ? 'Hide details' : 'Show details'"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="showBankDetails" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>{{ showBankDetails ? 'Hide' : 'Show' }}</span>
              </button>
              
              <button 
                v-if="hasBankDetails" 
                @click="showModal = true" 
                class="px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm font-medium rounded-lg transition-colors border border-gray-200"
              >
                Edit
              </button>
              <button 
                v-else 
                @click="showModal = true" 
                class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-medium rounded-lg transition-all shadow-sm"
              >
                Add Bank Details
              </button>
            </div>
          </div>

          <!-- Bank Details Display with Hide/Show -->
          <div v-if="hasBankDetails" class="mt-4">
            <!-- Collapsible Section -->
            <transition
              enter-active-class="transition-all duration-300 ease-out"
              enter-from-class="opacity-0 -translate-y-2"
              enter-to-class="opacity-100 translate-y-0"
              leave-active-class="transition-all duration-200 ease-in"
              leave-from-class="opacity-100 translate-y-0"
              leave-to-class="opacity-0 -translate-y-2"
            >
              <div v-if="showBankDetails" class="p-4 bg-gray-50 rounded-xl">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-lg bg-white border border-gray-200 flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                    </svg>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">{{ bankData.affiliate_bank_name }}</p>
                    <p class="text-sm text-gray-600 mt-0.5">{{ bankData.affiliate_bank_account_name }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 flex items-center flex-wrap gap-2">
                      <span class="font-mono">{{ maskAccountNumber(bankData.affiliate_bank_account_number) }}</span>
                      <span v-if="bankData.affiliate_bank_code" class="px-2 py-0.5 bg-gray-200 rounded-full text-xs">Code: {{ bankData.affiliate_bank_code }}</span>
                    </p>
                  </div>
                </div>
              </div>
            </transition>

            <!-- Minimal Preview when Hidden -->
            <div v-if="!showBankDetails" class="flex items-center gap-3 p-2 text-sm text-gray-500">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              <span>Bank details hidden • {{ maskAccountNumber(bankData.affiliate_bank_account_number) }}</span>
              <button 
                @click="showBankDetails = true" 
                class="text-blue-600 hover:text-blue-700 text-xs font-medium underline"
              >
                Show
              </button>
            </div>
          </div>

          <!-- Affiliate Link Section -->
          <div class="mt-6 pt-6 border-t border-gray-100">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 14.828a4 4 0 01-5.656 0 4 4 0 010-5.656m5.656 5.656a4 4 0 105.656-5.656m-5.656 5.656L15.172 7m-5.656 5.656L7 15.172" />
                </svg>
              </div>
              <h3 class="text-md font-semibold text-gray-900">Your Affiliate Link</h3>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3">
              <div class="flex-1 relative">
                <input 
                  readonly 
                  :value="affiliateUrl" 
                  class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 font-mono pr-24"
                />
                <button 
                  @click="copyAffiliateLinkPage" 
                  class="absolute right-2 top-1/2 -translate-y-1/2 px-4 py-1.5 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
                >
                  Copy
                </button>
              </div>
              <div v-if="!affiliateUrl" class="text-sm text-amber-600 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>No affiliate code available</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6 mb-6 lg:mb-8">
        <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm p-6 hover:shadow-md transition-all">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm0 0h6v-2a6 6 0 00-9-5.593M9 21h6" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500 mb-1">Total Referrals</p>
              <p class="text-2xl lg:text-3xl font-bold text-gray-900">{{ totals.referrals }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm p-6 hover:shadow-md transition-all">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
              <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500 mb-1">Total Payouts</p>
              <p class="text-2xl lg:text-3xl font-bold text-gray-900">₦{{ formatCurrency(totals.total) }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm p-6 hover:shadow-md transition-all sm:col-span-2 lg:col-span-1">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
              <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m-6 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500 mb-1">Paid / Unpaid</p>
              <div class="flex items-baseline gap-2">
                <p class="text-2xl lg:text-3xl font-bold text-green-600">₦{{ formatCurrency(totals.paid) }}</p>
                <span class="text-gray-400">/</span>
                <p class="text-2xl lg:text-3xl font-bold text-amber-600">₦{{ formatCurrency(totals.unpaid) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Payouts Table Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
        <div class="flex items-center gap-3">
          <label class="text-sm font-medium text-gray-700">Filter:</label>
          <select 
            v-model="filter" 
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
          >
            <option value="all">All Payouts</option>
            <option value="paid">Paid Only</option>
            <option value="unpaid">Unpaid Only</option>
          </select>
        </div>
        
        <button 
          @click="downloadCsv" 
          class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm text-sm font-medium group"
        >
          <svg class="w-4 h-4 text-gray-500 group-hover:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M16 12l-4 4-4-4M12 12V4" />
          </svg>
          Download CSV
        </button>
      </div>

      <!-- Payouts Table -->
      <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-200">
                <th class="text-left p-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Business</th>
                <th class="text-left p-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                <th class="text-left p-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Approved</th>
                <th class="text-left p-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</th>
                <th class="text-left p-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                <th class="text-left p-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="p in filteredPayouts" :key="p.id" class="hover:bg-gray-50/50 transition-colors">
                <td class="p-4">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-gray-500 to-gray-600 flex items-center justify-center text-white font-semibold text-xs flex-shrink-0">
                      {{ p.referral?.business?.name?.charAt(0).toUpperCase() || '?' }}
                    </div>
                    <span class="font-medium text-gray-900">{{ p.referral?.business?.name || '—' }}</span>
                  </div>
                </td>
                <td class="p-4 font-medium text-gray-900">₦{{ formatCurrency(p.amount) }}</td>
                <td class="p-4">
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" 
                    :class="p.approved ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'">
                    {{ p.approved ? 'Approved' : 'Pending' }}
                  </span>
                </td>
                <td class="p-4">
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                    :class="p.paid ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'">
                    {{ p.paid ? 'Paid' : 'Unpaid' }}
                  </span>
                </td>
                <td class="p-4 text-gray-500">{{ formatDate(p.created_at) }}</td>
                <td class="p-4">
                  <Link 
                    v-if="p.referral?.business" 
                    :href="route('accountant.businesses.show', p.referral.business.id)" 
                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 hover:bg-blue-100 text-gray-700 hover:text-blue-600 rounded-lg text-xs font-medium transition-colors"
                  >
                    <span>View Business</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </Link>
                </td>
              </tr>
              <tr v-if="filteredPayouts.length === 0">
                <td colspan="6" class="p-8 text-center">
                  <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-100 rounded-full mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                  </div>
                  <p class="text-sm text-gray-500">No payouts to display</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Bank Details Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showModal = false"></div>
      
      <div class="bg-white rounded-2xl shadow-2xl z-10 w-full max-w-2xl overflow-hidden">
        <!-- Modal Header -->
        <div class="px-6 py-5 bg-gradient-to-r from-blue-600 to-indigo-600">
          <h3 class="text-lg font-semibold text-white flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            Save Payout Bank Details
          </h3>
        </div>

        <!-- Modal Body -->
        <form @submit.prevent="submitBank" class="p-6 space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="block text-sm font-medium text-gray-700">Bank Name</label>
              <input 
                v-model="bankData.affiliate_bank_name" 
                type="text" 
                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all" 
                placeholder="e.g., First Bank"
              />
            </div>

            <div class="space-y-1.5">
              <label class="block text-sm font-medium text-gray-700">Account Name</label>
              <input 
                v-model="bankData.affiliate_bank_account_name" 
                type="text" 
                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all" 
                placeholder="Full account name"
              />
            </div>

            <div class="space-y-1.5">
              <label class="block text-sm font-medium text-gray-700">Account Number</label>
              <input 
                v-model="bankData.affiliate_bank_account_number" 
                type="text" 
                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all" 
                placeholder="10-digit account number"
                maxlength="10"
              />
            </div>

            <div class="space-y-1.5">
              <label class="block text-sm font-medium text-gray-700">Bank Code (Optional)</label>
              <input 
                v-model="bankData.affiliate_bank_code" 
                type="text" 
                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all" 
                placeholder="e.g., 011"
              />
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <button 
              type="button" 
              @click="showModal = false" 
              class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-sm"
            >
              Save Details
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Bank Details Modal (unchanged) -->
    <!-- ... -->
  </AccountantLayout>
</template>

<script setup>
import { usePage, Link, Head, router } from '@inertiajs/vue3';
import AccountantLayout from '@/Layouts/AccountantLayout.vue';
import { computed, ref } from 'vue';

const page = usePage();
const referrals = computed(() => page.props.referrals || []);
const user = computed(() => page.props.user || page.props.auth?.user || {});

const bankData = ref({
  affiliate_bank_name: user.value?.affiliate_bank_name || '',
  affiliate_bank_account_name: user.value?.affiliate_bank_account_name || '',
  affiliate_bank_account_number: user.value?.affiliate_bank_account_number || '',
  affiliate_bank_code: user.value?.affiliate_bank_code || '',
});

const showModal = ref(false);
const showBankDetails = ref(false); // New state for hide/show

const hasBankDetails = computed(() => {
  return !!(bankData.value.affiliate_bank_account_number || bankData.value.affiliate_bank_account_name || bankData.value.affiliate_bank_name);
});

// Helper function to mask account number
const maskAccountNumber = (accountNumber) => {
  if (!accountNumber) return '';
  if (accountNumber.length <= 4) return '••••' + accountNumber.slice(-4);
  return '••••••••' + accountNumber.slice(-4);
};

const submitBank = () => {
  router.post(route('accountant.affiliate.bank.update'), bankData.value, {
    preserveState: false,
    onSuccess: () => {
      showModal.value = false;
      showBankDetails.value = true; // Show details after update
    },
    onError: () => {
      // rely on Inertia flash
    }
  });
};

const affiliateUrl = computed(() => {
  const code = user.value?.affiliate_code || '';
  return code ? `${window.location.origin}/affiliate/${code}` : '';
});

const copyAffiliateLinkPage = async () => {
  try {
    if (!affiliateUrl.value) return;
    await navigator.clipboard.writeText(affiliateUrl.value);
    alert('Affiliate link copied to clipboard');
  } catch (e) {
    alert('Unable to copy link');
  }
};

const payouts = computed(() => {
  const list = [];
  referrals.value.forEach(r => {
    (r.payouts || []).forEach(p => list.push(Object.assign({}, p, { referral: r })));
  });
  return list;
});

const filter = ref('all');

const filteredPayouts = computed(() => {
  if (filter.value === 'paid') return payouts.value.filter(p => p.paid);
  if (filter.value === 'unpaid') return payouts.value.filter(p => !p.paid);
  return payouts.value;
});

const totals = computed(() => {
  const t = { referrals: referrals.value.length, total: 0, paid: 0, unpaid: 0 };
  payouts.value.forEach(p => {
    const amt = parseFloat(p.amount) || 0;
    t.total += amt;
    if (p.paid) t.paid += amt; else t.unpaid += amt;
  });
  return t;
});

const formatCurrency = (amount) => {
  if (!amount && amount !== 0) return '0.00';
  return new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount);
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-NG', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const downloadCsv = () => {
  const headers = ['Business', 'Amount', 'Approved', 'Paid', 'Created'];
  const rows = filteredPayouts.value.map(p => [
    p.referral?.business?.name || '',
    p.amount,
    p.approved ? 'Yes' : 'No',
    p.paid ? 'Yes' : 'No',
    p.created_at
  ]);
  
  const csv = [headers, ...rows]
    .map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(','))
    .join('\n');
    
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `affiliate_payouts_${new Date().toISOString().split('T')[0]}.csv`;
  a.click();
  URL.revokeObjectURL(url);
};
</script>

<style scoped>
/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}
</style>