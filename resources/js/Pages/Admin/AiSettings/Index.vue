<template>
    <AdminLayout>
  <div class="space-y-6 max-w-4xl">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-bold text-gray-900">AI Settings</h1>
      <p class="mt-2 text-gray-600">Configure AI providers for all businesses</p>
    </div>

    <!-- Success/Error Messages -->
    <div v-if="page.props.flash?.success" class="alert alert-success">
      {{ page.props.flash.success }}
    </div>
    <div v-if="page.props.flash?.error" class="alert alert-error">
      {{ page.props.flash.error }}
    </div>

    <!-- Settings Form -->
    <div class="bg-white rounded-lg shadow-sm p-6 space-y-6">
      <form @submit.prevent="updateSettings" class="space-y-6">
        <!-- AI Provider Selection -->
        <div class="space-y-3">
          <h2 class="text-lg font-semibold text-gray-900">Active AI Provider</h2>
          <p class="text-sm text-gray-600">Select which AI provider to use globally for all businesses</p>

          <div class="grid grid-cols-2 gap-4">
            <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition" :class="form.ai_provider === 'deepseek' ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
              <input
                v-model="form.ai_provider"
                type="radio"
                value="deepseek"
                class="w-4 h-4 accent-blue-600"
              />
              <div class="ml-3">
                <p class="font-semibold text-gray-900">Deepseek</p>
                <p class="text-sm text-gray-600">Fast and efficient AI processing</p>
              </div>
            </label>

            <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition" :class="form.ai_provider === 'gemini' ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
              <input
                v-model="form.ai_provider"
                type="radio"
                value="gemini"
                class="w-4 h-4 accent-blue-600"
              />
              <div class="ml-3">
                <p class="font-semibold text-gray-900">Google Gemini</p>
                <p class="text-sm text-gray-600">Advanced language model</p>
              </div>
            </label>
          </div>
        </div>

        <!-- Enable/Disable AI -->
        <div class="space-y-3">
          <label class="flex items-center">
            <input v-model="form.ai_enabled" type="checkbox" class="w-4 h-4 rounded" />
            <span class="ml-3 font-semibold text-gray-900">Enable AI Features</span>
          </label>
          <p class="text-sm text-gray-600 ml-7">Disable to temporarily turn off AI for all businesses</p>
        </div>

        <!-- Deepseek Key -->
        <div class="space-y-3 p-4 bg-gray-50 rounded-lg">
          <h3 class="font-semibold text-gray-900">Deepseek API Key</h3>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
            <input
              v-model="form.deepseek_api_key"
              type="password"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :placeholder="settings.deepseek_api_key ? 'Leave blank to keep current key' : 'Enter Deepseek API key'"
            />
            <p v-if="settings.deepseek_api_key" class="text-xs text-green-600 mt-1">✓ Key configured ({{ settings.deepseek_api_key }})</p>
            <p class="text-xs text-gray-500 mt-2">Get your API key from <a href="https://platform.deepseek.com" target="_blank" class="text-blue-600 hover:underline">platform.deepseek.com</a></p>
          </div>
        </div>

        <!-- Gemini Key -->
        <div class="space-y-3 p-4 bg-gray-50 rounded-lg">
          <h3 class="font-semibold text-gray-900">Google Gemini API Key</h3>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
            <input
              v-model="form.gemini_api_key"
              type="password"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :placeholder="settings.gemini_api_key ? 'Leave blank to keep current key' : 'Enter Gemini API key'"
            />
            <p v-if="settings.gemini_api_key" class="text-xs text-green-600 mt-1">✓ Key configured ({{ settings.gemini_api_key }})</p>
            <p class="text-xs text-gray-500 mt-2">Get your API key from <a href="https://makersuite.google.com/app/apikey" target="_blank" class="text-blue-600 hover:underline">makersuite.google.com/app/apikey</a></p>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex gap-4 pt-4 border-t">
          <button
            type="submit"
            :disabled="processing"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 disabled:bg-gray-400"
          >
            {{ processing ? 'Saving...' : 'Save Settings' }}
          </button>

          <button
            type="button"
            @click="testConnection"
            :disabled="testingConnection"
            class="px-6 py-2 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 disabled:bg-gray-400"
          >
            {{ testingConnection ? 'Testing...' : `Test ${form.ai_provider} Connection` }}
          </button>
        </div>
      </form>
    </div>

    <!-- Status Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 space-y-4">
      <h3 class="font-semibold text-gray-900">Current Configuration</h3>
      <div class="space-y-2 text-sm">
        <div class="flex justify-between">
          <span class="text-gray-700">Active Provider:</span>
          <span class="font-semibold text-gray-900">{{ settings.ai_provider.toUpperCase() }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-700">AI Status:</span>
          <span :class="['font-semibold', settings.ai_enabled ? 'text-green-600' : 'text-red-600']">
            {{ settings.ai_enabled ? 'Enabled' : 'Disabled' }}
          </span>
        </div>
        <div class="text-gray-600 mt-4">
          All businesses will use the {{ settings.ai_provider === 'deepseek' ? 'Deepseek' : 'Google Gemini' }} AI provider.
        </div>
      </div>
    </div>

    <!-- Test Result -->
    <div v-if="testResult" :class="['rounded-lg p-4', testResult.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200']">
      <p :class="testResult.success ? 'text-green-800' : 'text-red-800'">
        {{ testResult.message }}
      </p>
    </div>
  </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const page = usePage();
const settings = ref(page.props.settings);

const form = ref({
  ai_provider: settings.value.ai_provider,
  deepseek_api_key: '',
  gemini_api_key: '',
  ai_enabled: settings.value.ai_enabled,
});

const processing = ref(false);
const testingConnection = ref(false);
const testResult = ref(null);

const updateSettings = async () => {
  processing.value = true;

  try {
    await axios.put('/admin/ai-settings', form.value);
    testResult.value = null;
    // Reload page to show success message
    window.location.reload();
  } catch (error) {
    console.error('Error updating settings:', error);
    processing.value = false;
  }
};

const testConnection = async () => {
  testingConnection.value = true;
  testResult.value = null;

  try {
    const response = await axios.post('/admin/ai-settings/test', {
      provider: form.value.ai_provider,
    });

    testResult.value = {
      success: response.data.success,
      message: response.data.message,
    };
  } catch (error) {
    testResult.value = {
      success: false,
      message: error.response?.data?.message || 'Connection test failed',
    };
  } finally {
    testingConnection.value = false;
  }
};
</script>

<style scoped>
.alert {
  @apply px-4 py-3 rounded-lg;
}

.alert-success {
  @apply bg-green-50 text-green-800 border border-green-200;
}

.alert-error {
  @apply bg-red-50 text-red-800 border border-red-200;
}
</style>
