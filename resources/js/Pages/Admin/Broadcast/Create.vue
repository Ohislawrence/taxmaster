<script setup>
import { ref, computed } from 'vue';
import { router, Link, usePage, Head } from '@inertiajs/vue3';
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
const hasBusiness = ref('all');
const plans = ref([]);
const isSending = ref(false);
const showPreview = ref(false);

// available roles: prefer server-provided list, otherwise fall back
const availableRoles = computed(() => {
  return page.props?.availableRoles || ['admin', 'business', 'accountant', 'user'];
});

// available plans from controller
const availablePlans = computed(() => {
  return page.props?.availablePlans || [];
});

// Role labels for better display
const roleLabels = {
  admin: 'Admin',
  business: 'Business Owner',
  accountant: 'Accountant',
  user: 'User'
};

// Toggle role selection
const toggleRole = (role) => {
  const index = roles.value.indexOf(role);
  if (index > -1) {
    roles.value.splice(index, 1);
  } else {
    roles.value.push(role);
  }
};

// Toggle plan selection
const togglePlan = (planId) => {
  const index = plans.value.indexOf(planId);
  if (index > -1) {
    plans.value.splice(index, 1);
  } else {
    plans.value.push(planId);
  }
};

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

  // Get user role (preview with admin role for demo)
  const role = user?.roles?.[0]?.name ? user.roles[0].name.charAt(0).toUpperCase() + user.roles[0].name.slice(1) : 'User';

  // Get user plan (preview with "Free" for demo)
  const plan = 'Free';

  const map = {
    '{first_name}': first,
    '{last_name}': last,
    '{name}': name,
    '{email}': user?.email || '',
    '{business_name}': business,
    '{role}': role,
    '{plan}': plan,
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
  data.append('has_business', hasBusiness.value);
  if (plans.value && plans.value.length) {
    plans.value.forEach(p => data.append('plans[]', p));
  }

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
  <Head title="Broadcast Email" />

  <!-- Subtle grid background - Mono.co style -->
  <div class="relative min-h-screen bg-gray-50">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>

    <div class="relative py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
          <Link :href="route('admin.dashboard')" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium mb-4 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Dashboard
          </Link>
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Broadcast Email</h1>
              <p class="mt-2 text-gray-600">Send targeted emails to your users with custom filters</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Content -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Email Content Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                  Email Content
                </h2>
              </div>
              <div class="p-6 space-y-6">
                <!-- Subject -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Subject Line</label>
                  <input
                    v-model="subject"
                    type="text"
                    placeholder="Enter email subject..."
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  />
                </div>

                <!-- Body -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email Body</label>
                  <div class="border border-gray-300 rounded-lg overflow-hidden">
                    <QuillEditor
                      v-model="body"
                      theme="snow"
                      class="min-h-[300px] bg-white"
                    />
                  </div>
                  <div class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
                    <p class="text-xs font-medium text-blue-900 mb-1">Available Placeholders:</p>
                    <div class="flex flex-wrap gap-2">
                      <span class="inline-flex items-center px-2 py-1 rounded bg-blue-100 text-blue-700 text-xs font-mono">
                        {name}
                      </span>
                      <span class="inline-flex items-center px-2 py-1 rounded bg-blue-100 text-blue-700 text-xs font-mono">
                        {first_name}
                      </span>
                      <span class="inline-flex items-center px-2 py-1 rounded bg-blue-100 text-blue-700 text-xs font-mono">
                        {last_name}
                      </span>
                      <span class="inline-flex items-center px-2 py-1 rounded bg-blue-100 text-blue-700 text-xs font-mono">
                        {email}
                      </span>
                      <span class="inline-flex items-center px-2 py-1 rounded bg-blue-100 text-blue-700 text-xs font-mono">
                        {business_name}
                      </span>
                      <span class="inline-flex items-center px-2 py-1 rounded bg-blue-100 text-blue-700 text-xs font-mono">
                        {role}
                      </span>
                      <span class="inline-flex items-center px-2 py-1 rounded bg-blue-100 text-blue-700 text-xs font-mono">
                        {plan}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Audience Filters Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                  </svg>
                  Audience Filters
                </h2>
              </div>
              <div class="p-6 space-y-6">
                <!-- User Roles -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-3">User Roles</label>
                  <div class="grid grid-cols-2 gap-3">
                    <button
                      v-for="role in availableRoles"
                      :key="role"
                      @click="toggleRole(role)"
                      :class="[
                        'px-4 py-3 rounded-lg border-2 text-sm font-medium transition-all',
                        roles.includes(role)
                          ? 'border-blue-500 bg-blue-50 text-blue-700'
                          : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'
                      ]"
                    >
                      <div class="flex items-center justify-between">
                        <span>{{ roleLabels[role] || role }}</span>
                        <svg v-if="roles.includes(role)" class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                      </div>
                    </button>
                  </div>
                  <p class="mt-2 text-xs text-gray-500">Select one or more roles. Leave empty to target all users.</p>
                </div>

                <!-- Subscription Status -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-3">Subscription Status</label>
                  <div class="grid grid-cols-3 gap-3">
                    <button
                      @click="subscribed = 'all'"
                      :class="[
                        'px-4 py-3 rounded-lg border-2 text-sm font-medium transition-all',
                        subscribed === 'all'
                          ? 'border-blue-500 bg-blue-50 text-blue-700'
                          : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'
                      ]"
                    >
                      All Users
                    </button>
                    <button
                      @click="subscribed = 'subscribed'"
                      :class="[
                        'px-4 py-3 rounded-lg border-2 text-sm font-medium transition-all',
                        subscribed === 'subscribed'
                          ? 'border-blue-500 bg-blue-50 text-blue-700'
                          : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'
                      ]"
                    >
                      Subscribed
                    </button>
                    <button
                      @click="subscribed = 'unsubscribed'"
                      :class="[
                        'px-4 py-3 rounded-lg border-2 text-sm font-medium transition-all',
                        subscribed === 'unsubscribed'
                          ? 'border-blue-500 bg-blue-50 text-blue-700'
                          : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'
                      ]"
                    >
                      Free Users
                    </button>
                  </div>
                </div>

                <!-- Business Status -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-3">Business Status</label>
                  <div class="grid grid-cols-3 gap-3">
                    <button
                      @click="hasBusiness = 'all'"
                      :class="[
                        'px-4 py-3 rounded-lg border-2 text-sm font-medium transition-all',
                        hasBusiness === 'all'
                          ? 'border-blue-500 bg-blue-50 text-blue-700'
                          : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'
                      ]"
                    >
                      All Users
                    </button>
                    <button
                      @click="hasBusiness = 'yes'"
                      :class="[
                        'px-4 py-3 rounded-lg border-2 text-sm font-medium transition-all',
                        hasBusiness === 'yes'
                          ? 'border-blue-500 bg-blue-50 text-blue-700'
                          : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'
                      ]"
                    >
                      Has Business
                    </button>
                    <button
                      @click="hasBusiness = 'no'"
                      :class="[
                        'px-4 py-3 rounded-lg border-2 text-sm font-medium transition-all',
                        hasBusiness === 'no'
                          ? 'border-blue-500 bg-blue-50 text-blue-700'
                          : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'
                      ]"
                    >
                      No Business
                    </button>
                  </div>
                </div>

                <!-- Subscription Plans -->
                <div v-if="availablePlans.length > 0">
                  <label class="block text-sm font-medium text-gray-700 mb-3">Subscription Plans</label>
                  <div class="grid grid-cols-2 gap-3">
                    <button
                      v-for="plan in availablePlans"
                      :key="plan.id"
                      @click="togglePlan(plan.id)"
                      :class="[
                        'px-4 py-3 rounded-lg border-2 text-sm font-medium transition-all',
                        plans.includes(plan.id)
                          ? 'border-blue-500 bg-blue-50 text-blue-700'
                          : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'
                      ]"
                    >
                      <div class="flex items-center justify-between">
                        <span>{{ plan.name }}</span>
                        <svg v-if="plans.includes(plan.id)" class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                      </div>
                    </button>
                  </div>
                  <p class="mt-2 text-xs text-gray-500">Select specific plans. Leave empty to include all plans.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="lg:col-span-1 space-y-6">
            <!-- Preview Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden sticky top-6">
              <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  Preview
                </h2>
                <button
                  @click="showPreview = !showPreview"
                  class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                >
                  {{ showPreview ? 'Hide' : 'Show' }}
                </button>
              </div>
              <div v-if="showPreview" class="p-6 max-h-96 overflow-y-auto">
                <div class="text-xs text-gray-500 mb-3">Preview with your data:</div>
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                  <div class="font-semibold text-sm text-gray-900 mb-2">{{ subject || 'No subject' }}</div>
                  <div v-html="previewHtml" class="prose prose-sm max-w-full text-gray-700"></div>
                </div>
              </div>
            </div>

            <!-- Send Actions Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
              <div class="p-6 space-y-4">
                <button
                  @click="submit"
                  :disabled="isSending || !subject || !body"
                  class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white px-6 py-3 rounded-lg font-semibold text-sm transition-all shadow-lg shadow-blue-600/20 hover:shadow-xl hover:shadow-blue-600/30 active:scale-[0.98] flex items-center justify-center"
                >
                  <svg v-if="!isSending" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                  </svg>
                  <svg v-else class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ isSending ? 'Sending...' : 'Send Broadcast' }}
                </button>

                <Link
                  :href="route('admin.dashboard')"
                  class="w-full inline-flex items-center justify-center border-2 border-gray-200 bg-white hover:bg-gray-50 text-gray-700 px-6 py-3 rounded-lg font-semibold text-sm transition-all"
                >
                  Cancel
                </Link>

                <div class="pt-4 border-t border-gray-200">
                  <div class="flex items-start space-x-2 text-xs text-gray-600">
                    <svg class="w-4 h-4 mt-0.5 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <p>Emails will be queued and sent in the background. Users will receive personalized content based on your placeholders.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
