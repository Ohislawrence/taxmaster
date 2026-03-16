<template>
  <AdminLayout>
    <Head title="Create Business" />

    <div class="p-6">
      <div class="max-w-3xl mx-auto bg-white rounded shadow p-6">
        <h1 class="text-2xl font-semibold mb-4">Create Business</h1>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium">Business Name</label>
            <input v-model="form.name" type="text" class="w-full border rounded px-3 py-2" required />
          </div>

          <div>
            <label class="block text-sm font-medium">Email</label>
            <input v-model="form.email" type="email" class="w-full border rounded px-3 py-2" required />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium">Phone</label>
              <input v-model="form.phone" type="text" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
              <label class="block text-sm font-medium">Business Type</label>
              <select v-model="form.business_type" class="w-full border rounded px-3 py-2" required>
                <option value="company">Company</option>
                <option value="sole_proprietorship">Sole Proprietorship</option>
                <option value="partnership">Partnership</option>
                <option value="limited_liability">Limited Liability</option>
                <option value="corporation">Corporation</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium">Address</label>
            <input v-model="form.address" type="text" class="w-full border rounded px-3 py-2" required />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium">State</label>
              <input v-model="form.state" type="text" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
              <label class="block text-sm font-medium">City</label>
              <input v-model="form.city" type="text" class="w-full border rounded px-3 py-2" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium">Assign To</label>
            <div class="flex gap-2 items-center mt-2">
              <select v-model="assignedRole" class="border rounded px-3 py-2">
                <option value="">Select role</option>
                <option value="business">Business (owner)</option>
                <option value="accountant">Accountant (manager)</option>
              </select>

              <select v-model="form.assigned_user_id" class="flex-1 border rounded px-3 py-2">
                <option value="">Select user</option>
                <optgroup label="Business users">
                  <option v-for="u in owners" :key="'b-'+u.id" v-if="assignedRole === 'business'" :value="u.id">{{ u.name }}</option>
                </optgroup>
                <optgroup label="Accountants">
                  <option v-for="u in accountants" :key="'a-'+u.id" v-if="assignedRole === 'accountant'" :value="u.id">{{ u.name }}</option>
                </optgroup>
              </select>
            </div>
          </div>

          <div class="flex gap-3 pt-4">
            <button type="submit" :disabled="processing" class="bg-blue-600 text-white px-4 py-2 rounded">
              {{ processing ? 'Creating...' : 'Create Business' }}
            </button>
            <Link href="/admin/businesses" class="px-4 py-2 bg-gray-200 rounded">Cancel</Link>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({
  owners: Array,
  accountants: Array,
});

const form = useForm({
  name: '',
  email: '',
  phone: '',
  business_type: 'company',
  address: '',
  city: '',
  state: '',
  assigned_user_id: '',
  assigned_role: '',
});

const assignedRole = ref('');
const owners = props.owners || [];
const accountants = props.accountants || [];
const processing = ref(false);

watch(assignedRole, (v) => {
  form.assigned_role = v;
  form.assigned_user_id = '';
});

function submit() {
  processing.value = true;
  form.post('/admin/businesses', {
    onError: () => (processing.value = false),
    onSuccess: () => (processing.value = false),
  });
}
</script>
