<template>
  <AdminLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Accountants</h1>
      <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-left">
          <thead class="bg-gray-50">
            <tr>
              <th class="p-3">Name</th>
              <th class="p-3">Email</th>
              <th class="p-3">Managed Businesses</th>
              <th class="p-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="acct in accountants" :key="acct.id" class="border-t">
              <td class="p-3">{{ acct.name }}</td>
              <td class="p-3">{{ acct.email }}</td>
              <td class="p-3">{{ acct.managed_businesses_count || acct.managedBusinessesCount || 0 }}</td>
              <td class="p-3">
                <Link :href="`/admin/accountants/${acct.id}`" class="text-blue-600">View</Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';

export default {
  components: { AdminLayout, Link },
  props: {
    accountants: Array,
  },
};
</script>
