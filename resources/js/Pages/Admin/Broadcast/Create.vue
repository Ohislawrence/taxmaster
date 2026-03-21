<script setup>
import { ref, computed } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

defineOptions({ layout: AdminLayout });

const page = usePage();
const currentUser = page.props?.auth?.user || page.props?.user || {};

const subject = ref('');
const body = ref('');
const roles = ref([]);
const subscribed = ref('all');
const isSending = ref(false);

// available roles: prefer server-provided list, otherwise fall back
const availableRoles = computed(() => {
  return page.props?.availableRoles || ['admin', 'business', 'accountant', 'user'];
});

const renderForUser = (template, user) => {
  const name = user?.name || '';
  let first = '';
  let last = '';
  if (name) {
    const parts = name.split(/\s+/);
    first = parts[0] || '';
    last = parts.length > 1 ? parts[parts.length - 1] : '';
  }
  let business = '';
  try {
    const b = user?.defaultBusiness;
    business = b?.name || '';
  } catch (e) {
    business = '';
  }
  const map = {
    '{first_name}': first,
    '{last_name}': last,
    '{name}': name,
    '{email}': user?.email || '',
    '{business_name}': business,
  };
  return template.replace(/\{[^}]+\}/g, (m) => map[m] ?? m);
};

const previewHtml = computed(() => renderForUser(body.value || '', currentUser));

const submit = () => {
  if (isSending.value) return;
  isSending.value = true;
  const data = new FormData();
  data.append('subject', subject.value);
  data.append('body', body.value);
  if (roles.value && roles.value.length) {
    roles.value.forEach(r => data.append('roles[]', r));
  }
  data.append('subscribed', subscribed.value);

  router.post(route('admin.broadcast.send'), data, {
    onSuccess() {
      window.dispatchEvent(new CustomEvent('admin:flash', { detail: { type: 'success', message: 'Broadcast queued. Emails will be sent shortly.' } }));
    },
    onError(errors) {
      const msg = errors?.message || 'Failed to queue broadcast. Check validation.';
      window.dispatchEvent(new CustomEvent('admin:flash', { detail: { type: 'error', message: msg } }));
    },
    onFinish() { isSending.value = false; }
  });
};
</script>

<template>
<section class="py-12">
  <div class="mx-auto max-w-3xl px-4">
    <div class="bg-white p-8 rounded shadow">
      <h1 class="text-2xl font-bold mb-4">Send Broadcast Email</h1>
      <div class="mb-4">
        <label class="block font-semibold mb-1">Subject</label>
        <input v-model="subject" class="w-full border rounded p-2" />
      </div>
      <div class="mb-4">
        <label class="block font-semibold mb-1">Body (HTML allowed)</label>
        <QuillEditor v-model="body" theme="snow" class="min-h-[200px] bg-white" />
        <div class="text-sm text-gray-500 mt-2">Available tokens: {first_name}, {last_name}, {name}, {email}, {business_name}</div>
      </div>
      <div class="mb-4">
        <label class="block font-semibold mb-1">Roles (optional)</label>
        <select multiple v-model="roles" class="w-full border rounded p-2 h-36">
          <option v-for="r in availableRoles" :key="r" :value="r">{{ r }}</option>
        </select>
        <div class="text-sm text-gray-500 mt-2">Hold Ctrl/Cmd to multi-select. If left empty, message will be sent to all users (subject to subscription filter).</div>
      </div>
      <div class="mb-4">
        <label class="block font-semibold mb-1">Or enter comma-separated roles</label>
        <input placeholder="admin,business,accountant" @input="e => roles = e.target.value.split(',').map(r => r.trim()).filter(Boolean)" class="w-full border rounded p-2" />
      </div>
      <div class="mb-4">
        <label class="block font-semibold mb-1">Subscription filter</label>
        <select v-model="subscribed" class="border rounded p-2">
          <option value="all">All users</option>
          <option value="subscribed">Subscribed</option>
          <option value="unsubscribed">Not subscribed</option>
        </select>
      </div>
      <div class="mb-6">
        <label class="block font-semibold mb-1">Live Preview (for you)</label>
        <div class="border rounded p-4 bg-gray-50">
          <div class="text-sm text-gray-600 mb-2">This preview shows the template rendered with your admin data.</div>
          <div v-html="previewHtml" class="prose max-w-full"></div>
        </div>
      </div>
      <div class="flex gap-4">
        <button @click="submit" :disabled="isSending" class="bg-blue-600 text-white px-4 py-2 rounded">Send</button>
        <Link :href="route('admin.dashboard')" class="text-gray-600">Cancel</Link>
      </div>
    </div>
  </div>
</section>
</template>
