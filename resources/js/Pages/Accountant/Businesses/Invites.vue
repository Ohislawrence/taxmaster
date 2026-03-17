<template>
  <AccountantLayout>
    <template #default>
      <Head :title="`${business.name} — Invitations`" />

      <div class="py-6 lg:py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl mx-auto">
          <div class="mb-6 flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Invitations for {{ business.name }}</h1>
              <p class="text-sm text-gray-500">Pending and accepted invitations for this business.</p>
            </div>
            <div>
              <Link :href="`/accountant/businesses/${business.id}`" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                Back to business
              </Link>
            </div>
          </div>

          <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
            <div class="p-6">
              <div v-if="invites.length === 0" class="text-sm text-gray-600">No invitations have been sent for this business.</div>

              <div v-else class="overflow-x-auto">
                <table class="min-w-full text-sm">
                  <thead>
                    <tr class="text-left text-xs text-gray-500 uppercase tracking-wide">
                      <th class="px-3 py-2">Email</th>
                      <th class="px-3 py-2">Invited By</th>
                      <th class="px-3 py-2">Sent</th>
                      <th class="px-3 py-2">Expires</th>
                      <th class="px-3 py-2">Status</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y">
                    <tr v-for="invite in invites" :key="invite.id">
                      <td class="px-3 py-3 text-gray-900">{{ invite.email }}</td>
                      <td class="px-3 py-3 text-gray-700">{{ invite.inviter?.name || 'System' }}</td>
                      <td class="px-3 py-3 text-gray-600">{{ formatDate(invite.created_at) }}</td>
                      <td class="px-3 py-3 text-gray-600">{{ invite.expires_at ? formatDate(invite.expires_at) : '—' }}</td>
                      <td class="px-3 py-3">
                        <span :class="statusClass(invite)" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium">
                          {{ statusLabel(invite) }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </AccountantLayout>
</template>

<script>
import AccountantLayout from '@/Layouts/AccountantLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

export default {
  components: { AccountantLayout, Head, Link },
  props: {
    business: Object,
    invites: { type: Array, default: () => [] },
  },
  methods: {
    statusLabel(invite) {
      if (invite.used_at) return 'Accepted';
      if (invite.expires_at && new Date(invite.expires_at) < new Date()) return 'Expired';
      return 'Pending';
    },
    statusClass(invite) {
      const s = this.statusLabel(invite);
      if (s === 'Accepted') return 'bg-emerald-100 text-emerald-700';
      if (s === 'Expired') return 'bg-amber-100 text-amber-700';
      return 'bg-blue-50 text-blue-700';
    },
    formatDate(value) {
      if (!value) return '—';
      try {
        return new Date(value).toLocaleString();
      } catch (e) {
        return value;
      }
    }
  }
}
</script>

<style scoped>
</style>
