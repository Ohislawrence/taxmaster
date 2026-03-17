<template>
  <AdminLayout>
    <template #default>
      <h1 class="text-2xl font-bold">Admin Profile</h1>
      <div class="mt-4 bg-white p-6 rounded-lg border">
        <p class="font-medium">{{ user.name }}</p>
        <p class="text-sm text-gray-600">{{ user.email }}</p>
      </div>
    </template>
  </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
export default {
  props: { user: Object },
  components: { AdminLayout }
}
</script>
