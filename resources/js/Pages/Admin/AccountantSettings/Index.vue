<template>
  <AdminLayout>
    <div class="p-6 max-w-3xl">
      <h1 class="text-2xl font-bold mb-4">Accountant Role Settings</h1>

      <form :action="route('admin.accountant-settings.update')" method="post" class="space-y-4">
        <input type="hidden" name="_method" value="put" />
        <input type="hidden" name="_token" :value="$page.props.csrf_token" />

        <div class="bg-white rounded-lg border p-4">
          <label class="flex items-center justify-between gap-4">
            <div>
              <div class="text-sm font-medium">Allow accountants to create client businesses</div>
              <div class="text-xs text-gray-500">If enabled, accountants can create businesses on behalf of clients during onboarding.</div>
            </div>
            <select name="accountant_can_create_businesses" class="border rounded px-3 py-2">
              <option :value="1" :selected="settings.accountant_can_create_businesses">Yes</option>
              <option :value="0" :selected="!settings.accountant_can_create_businesses">No</option>
            </select>
          </label>
        </div>

        <div class="bg-white rounded-lg border p-4">
          <label class="flex items-center justify-between gap-4">
            <div>
              <div class="text-sm font-medium">Require admin approval for new accountant accounts</div>
              <div class="text-xs text-gray-500">If enabled, admin must approve or create accountant accounts manually.</div>
            </div>
            <select name="accountant_require_admin_approval" class="border rounded px-3 py-2">
              <option :value="1" :selected="settings.accountant_require_admin_approval">Yes</option>
              <option :value="0" :selected="!settings.accountant_require_admin_approval">No</option>
            </select>
          </label>
        </div>

        <div class="bg-white rounded-lg border p-4">
          <div class="text-sm font-medium">Default permissions for new accountants</div>
          <div class="text-xs text-gray-500 mb-2">Comma-separated permission keys assigned when an accountant is created (e.g., manage-businesses,view-reports)</div>
          <input type="text" name="accountant_default_permissions" :value="settings.accountant_default_permissions" class="w-full border rounded px-3 py-2" />
        </div>

        <div class="bg-white rounded-lg border p-4">
          <div class="text-sm font-medium">Default commission for referrals (₦)</div>
          <div class="text-xs text-gray-500 mb-2">Optional fixed commission assigned when an accountant refers a new client. Use 0.00 for none.</div>
          <input type="number" step="0.01" name="accountant_default_commission" :value="settings.accountant_default_commission" class="w-full border rounded px-3 py-2" />
        </div>

        <div class="bg-white rounded-lg border p-4">
          <div class="text-sm font-medium">Onboarding email template</div>
          <div class="text-xs text-gray-500 mb-2">Email body sent to newly created accountants. You can use placeholders like {name} and {login_link}.</div>
          <textarea name="accountant_onboarding_email" rows="6" class="w-full border rounded px-3 py-2">{{ settings.accountant_onboarding_email }}</textarea>
        </div>

        <div class="flex items-center gap-3">
          <button type="submit" class="inline-flex items-center gap-2 rounded bg-blue-600 px-4 py-2 text-white">Save settings</button>
          <a :href="route('admin.accountants.index')" class="text-sm text-gray-600">Back to accountants</a>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
  components: { AdminLayout },
  props: {
    settings: Object,
  },
};
</script>

<style scoped>
textarea { font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto; }
</style>
