<template>
  <AdminLayout>
    <div class="p-6 space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Accountant: {{ accountant.name }}</h1>
      </div>

      <div class="grid grid-cols-2 gap-6">
        <div class="bg-white rounded shadow p-4">
          <h3 class="font-semibold mb-2">Managed Businesses</h3>
          <div class="mb-4">
            <form :action="`/admin/accountants/${accountant.id}/assign`" method="post" class="flex gap-2">
              <input type="hidden" name="_token" :value="$page.props.csrf_token" />
              <select name="business_id" class="flex-1 border border-gray-300 rounded px-3 py-2">
                <option value="">Select a business to assign</option>
                <option v-for="b in availableBusinesses" :key="b.id" :value="b.id">
                  {{ b.name }} — {{ b.owner?.name || 'Unclaimed' }}
                </option>
              </select>
              <button class="bg-blue-600 text-white px-3 py-2 rounded">Assign</button>
            </form>
          </div>
          <ul class="space-y-2">
            <li v-for="b in managedBusinesses" :key="b.id" class="flex items-center justify-between">
              <div>
                <div class="font-medium">{{ b.name }}</div>
                <div class="text-sm text-gray-600">Owner: {{ b.owner?.name || 'Unclaimed' }}</div>
              </div>
              <div class="space-x-2">
                <form :action="`/admin/accountants/${accountant.id}/detach/${b.id}`" method="post">
                  <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                  <button class="text-sm text-red-600">Detach</button>
                </form>
                <form :action="`/admin/accountants/${accountant.id}/enable-billing/${b.id}`" method="post">
                  <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                  <button class="text-sm text-green-600">Enable Billing</button>
                </form>
              </div>
            </li>
          </ul>
        </div>

        <div class="bg-white rounded shadow p-4">
          <h3 class="font-semibold mb-2">Recent Activity</h3>
          <ul class="text-sm space-y-2">
            <li v-for="a in activities" :key="a.id">
              <div class="text-gray-700">{{ a.description || a.message || a.event }}</div>
              <div class="text-xs text-gray-500">{{ new Date(a.created_at).toLocaleString() }}</div>
            </li>
            <li v-if="activities.length === 0" class="text-gray-500">No recent activity</li>
          </ul>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
  components: { AdminLayout },
  props: {
    accountant: Object,
    managedBusinesses: Array,
    activities: Array,
  },
};
</script>
