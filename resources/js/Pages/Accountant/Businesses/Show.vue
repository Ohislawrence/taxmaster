<template>
  <AccountantLayout>
    <template #default>
      <Head :title="business.name" />
      
      <div class="py-6 lg:py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl mx-auto">
          <!-- Breadcrumb -->
          <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
            <Link href="/accountant/businesses" class="hover:text-blue-600 transition-colors">Businesses</Link>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-700 font-medium">{{ business.name }}</span>
          </div>

          <!-- Header Card -->
          <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden mb-6">
            <div class="p-6 lg:p-8">
              <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                <!-- Business Info -->
                <div class="flex-1">
                  <div class="flex items-start gap-4">
                    <!-- Business Avatar -->
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-2xl flex-shrink-0">
                      {{ business.name.charAt(0).toUpperCase() }}
                    </div>
                    
                    <div class="flex-1 min-w-0">
                      <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">{{ business.name }}</h1>
                      <div class="flex flex-wrap items-center gap-3 mt-2">
                        <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full">
                          {{ business.registration_number || 'No registration number' }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full capitalize">
                          {{ business.business_type?.replace('_', ' ') || 'Business' }}
                        </span>
                        <span v-if="business.tax_identification_number" class="inline-flex items-center px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-medium rounded-full">
                          TIN: {{ business.tax_identification_number }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Contact Info Grid -->
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                    <div class="flex items-center gap-3 text-sm">
                      <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                      </div>
                      <div>
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="font-medium text-gray-900">{{ business.email || '—' }}</p>
                      </div>
                    </div>

                    <div class="flex items-center gap-3 text-sm">
                      <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                      </div>
                      <div>
                        <p class="text-xs text-gray-500">Phone</p>
                        <p class="font-medium text-gray-900">{{ business.phone || '—' }}</p>
                      </div>
                    </div>

                    <div class="flex items-center gap-3 text-sm">
                      <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                      </div>
                      <div>
                        <p class="text-xs text-gray-500">Address</p>
                        <p class="font-medium text-gray-900">{{ business.address || '—' }}</p>
                      </div>
                    </div>

                    <div class="flex items-center gap-3 text-sm">
                      <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                      </div>
                      <div>
                        <p class="text-xs text-gray-500">City/State</p>
                        <p class="font-medium text-gray-900">{{ business.city || '—' }}, {{ business.state || '—' }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row lg:flex-col gap-3">
                  <form ref="openForm" action="/business/switch" method="post" class="w-full sm:w-auto">
                    <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                    <input type="hidden" name="business_id" :value="business.id" />
                    <input v-if="openView" type="hidden" name="redirect" :value="openView" />
                    <button 
                      type="submit" 
                      class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-sm flex items-center justify-center gap-2"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                      </svg>
                      <span>Open Business Dashboard</span>
                    </button>
                  </form>

                  <form :action="`/accountant/businesses/${business.id}/detach`" method="post" class="w-full sm:w-auto" @submit.prevent="confirmDetach">
                    <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                    <button 
                      type="submit" 
                      class="w-full px-6 py-3 bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-red-50 hover:border-red-200 hover:text-red-600 transition-all flex items-center justify-center gap-2"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      <span>Remove Access</span>
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <!-- Description Card -->
          <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden mb-6">
            <div class="p-6">
              <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                  <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                  </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">Business Description</h2>
              </div>
              <p class="text-gray-700 leading-relaxed">{{ business.description || 'No description provided for this business.' }}</p>
            </div>
          </div>

          <!-- Billing Status Card -->
          <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
            <div class="p-6">
              <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                  <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">Billing Information</h2>
              </div>
              
              <div class="flex items-center gap-4 p-4 rounded-xl" :class="business.billing_managed_by_platform ? 'bg-emerald-50 border border-emerald-100' : 'bg-amber-50 border border-amber-100'">
                <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="business.billing_managed_by_platform ? 'bg-emerald-100' : 'bg-amber-100'">
                  <svg class="w-5 h-5" :class="business.billing_managed_by_platform ? 'text-emerald-600' : 'text-amber-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="business.billing_managed_by_platform" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                </div>
                <div>
                  <p class="font-medium" :class="business.billing_managed_by_platform ? 'text-emerald-800' : 'text-amber-800'">
                    {{ business.billing_managed_by_platform ? 'Billing is enabled on-platform' : 'Billing managed externally' }}
                  </p>
                  <p class="text-sm mt-1" :class="business.billing_managed_by_platform ? 'text-emerald-600' : 'text-amber-600'">
                    {{ business.billing_managed_by_platform ? 'All payments and invoices are handled through TaxMaster' : 'Contact admin to enable platform billing' }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="mt-6 flex items-center justify-end gap-3 text-sm">
            
            <Link 
              href="/accountant/businesses" 
              class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              <span>Back to List</span>
            </Link>
          </div>
        </div>
      </div>
    </template>
  </AccountantLayout>
</template>

<script>
import AccountantLayout from '@/Layouts/AccountantLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

export default {
  components: { AccountantLayout, Head, Link },
  props: {
    business: Object,
    openView: { type: String, default: null },
  },
  mounted() {
    // If openView is provided (e.g., ?view=vat), auto-submit the switch form to set business context and redirect
    if (this.openView) {
      setTimeout(() => {
        try {
          this.$refs.openForm.submit();
        } catch (e) {
          window.location.href = `/business/switch?business_id=${this.business.id}&redirect=${encodeURIComponent(this.openView)}`;
        }
      }, 200);
    }
  },
  methods: {
    confirmDetach(event) {
      if (confirm('Are you sure you want to remove your access to this business? This action cannot be undone.')) {
        event.target.submit();
      }
    }
  }
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