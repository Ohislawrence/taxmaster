<template>
  <AccountantLayout>
    <template #default>
      <Head title="My Businesses" />
      
      <div class="py-6 lg:py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="max-w-7xl mx-auto">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 lg:mb-8">
            <div>
              <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">My Businesses</h1>
              <p class="text-sm text-gray-500 mt-1">Manage and oversee all businesses under your portfolio</p>
            </div>
            
            <Link 
              :href="route('accountant.businesses.create')" 
              class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-sm"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" />
              </svg>
              <span>Create New Business</span>
            </Link>
          </div>

          <!-- Owned Businesses Section -->
          <section class="mb-8 lg:mb-10">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <h2 class="text-xl font-semibold text-gray-900">Owned Businesses</h2>
              <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">{{ businesses.length }}</span>
            </div>

            <div v-if="businesses.length === 0" class="bg-white rounded-xl border border-gray-200/50 p-8 text-center">
              <h3 class="text-sm font-medium text-gray-900 mb-1">No owned businesses yet</h3>
              <p class="text-sm text-gray-500 mb-4">Create your first business to start managing tax compliance</p>
              <Link 
                :href="route('accountant.businesses.create')" 
                class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium"
              >
                <span>Create a business</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                </svg>
              </Link>
            </div>

            <div v-else class="grid gap-4">
              <div 
                v-for="b in businesses" 
                :key="b.id" 
                class="bg-white rounded-xl border border-gray-200/50 hover:border-gray-200 hover:shadow-lg transition-all overflow-hidden group"
              >
                <div class="p-5">
                  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <!-- Business Info -->
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                          {{ b.name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                          <Link 
                            :href="route('accountant.businesses.show', b.id)" 
                            class="text-lg font-semibold text-gray-900 hover:text-blue-600 transition-colors"
                          >
                            {{ b.name }}
                          </Link>
                          <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5">
                            <span>{{ b.registration_number || 'No reg. number' }}</span>
                            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                            <span class="capitalize">{{ b.business_type?.replace('_', ' ') || 'Business' }}</span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2">
                      <Link 
                        :href="route('accountant.businesses.show', b.id)" 
                        class="px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm font-medium rounded-lg transition-colors"
                      >
                        Manage
                      </Link>
                      <form @submit.prevent="switchBusiness(b.id)" class="inline">
                        <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                        <button 
                          type="submit" 
                          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm"
                        >
                          Open Dashboard
                        </button>
                      </form>
                    </div>
                  </div>

                  <!-- Quick Stats -->
                  <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-100">
                    <div>
                      <p class="text-xs text-gray-500 mb-1">VAT Status</p>
                      <p :class="statusClass(b.vat_status)" class="text-sm font-medium">{{ formatStatus(b.vat_status) }}</p>
                    </div>
                    <div>
                      <p class="text-xs text-gray-500 mb-1">PAYE Status</p>
                      <p :class="statusClass(b.paye_status)" class="text-sm font-medium">{{ formatStatus(b.paye_status) }}</p>
                    </div>
                    <div>
                      <p class="text-xs text-gray-500 mb-1">Upcoming</p>
                      <p class="text-sm font-medium text-gray-900">{{ b.upcoming_deadlines_count || 0 }} deadlines</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- Managed Businesses Section -->
          <section>
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <h2 class="text-xl font-semibold text-gray-900">Managed Businesses</h2>
              <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">{{ managedBusinesses.length }}</span>
            </div>

            <div v-if="managedBusinesses.length === 0" class="bg-white rounded-xl border border-gray-200/50 p-8 text-center">
              <h3 class="text-sm font-medium text-gray-900 mb-1">No managed businesses yet</h3>
              <p class="text-sm text-gray-500">Businesses you've been granted access to will appear here</p>
            </div>

            <div v-else class="grid gap-4">
              <div 
                v-for="b in managedBusinesses" 
                :key="b.id" 
                class="bg-white rounded-xl border border-gray-200/50 hover:border-gray-200 hover:shadow-lg transition-all overflow-hidden"
              >
                <div class="p-5">
                  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <!-- Business Info -->
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                          {{ b.name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                          <Link 
                            :href="route('accountant.businesses.show', b.id)" 
                            class="text-lg font-semibold text-gray-900 hover:text-blue-600 transition-colors"
                          >
                            {{ b.name }}
                          </Link>
                          <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5">
                            <span>{{ b.registration_number || 'No reg. number' }}</span>
                            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                            <span class="capitalize">{{ b.business_type?.replace('_', ' ') || 'Business' }}</span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2">
                      <Link 
                        :href="route('accountant.businesses.show', b.id)" 
                        class="px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm font-medium rounded-lg transition-colors"
                      >
                        Manage
                      </Link>
                      <form @submit.prevent="detachBusiness(b.id)" class="inline">
                        <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                        <button 
                          type="submit" 
                          class="p-2 text-gray-400 hover:text-red-600 transition-colors"
                          title="Remove access"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </form>
                    </div>
                  </div>

                  <!-- Quick Stats -->
                  <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-100">
                    <div>
                      <p class="text-xs text-gray-500 mb-1">VAT Status</p>
                      <p :class="statusClass(b.vat_status)" class="text-sm font-medium">{{ formatStatus(b.vat_status) }}</p>
                    </div>
                    <div>
                      <p class="text-xs text-gray-500 mb-1">PAYE Status</p>
                      <p :class="statusClass(b.paye_status)" class="text-sm font-medium">{{ formatStatus(b.paye_status) }}</p>
                    </div>
                    <div>
                      <p class="text-xs text-gray-500 mb-1">Upcoming</p>
                      <p class="text-sm font-medium text-gray-900">{{ b.upcoming_deadlines_count || 0 }} deadlines</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>
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
  components: { AccountantLayout, Link, Head },
  props: {
    businesses: Array,
    managedBusinesses: Array,
  },
  methods: {
    formatStatus(status) {
      if (!status || status === 'none') return 'Not Filed';
      return status.charAt(0).toUpperCase() + status.slice(1);
    },
    statusClass(status) {
      if (!status || status === 'none') return 'text-gray-500';
      if (['paid','filed','submitted'].includes(status)) return 'text-green-600';
      if (['overdue'].includes(status)) return 'text-red-600';
      return 'text-yellow-600';
    },
    switchBusiness(businessId) {
      router.post('/business/switch', {
        business_id: businessId
      }, {
        preserveScroll: true,
        onSuccess: () => {
          // Optional: Show success message
        }
      });
    },
    detachBusiness(businessId) {
      if (confirm('Are you sure you want to remove your access to this business?')) {
        router.post(`/accountant/businesses/${businessId}/detach`, {}, {
          preserveScroll: true,
          onSuccess: () => {
            // Optional: Show success message
          }
        });
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