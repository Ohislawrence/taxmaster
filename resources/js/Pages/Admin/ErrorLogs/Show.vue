<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    error: Object,
});

const resolve = () => {
    if (confirm('Mark this error as resolved?')) {
        router.post(route('admin.error-logs.resolve', props.error.id));
    }
};

const deleteError = () => {
    if (confirm('Delete this error log? This cannot be undone.')) {
        router.delete(route('admin.error-logs.destroy', props.error.id), {
            onSuccess: () => {
                router.visit(route('admin.error-logs.index'));
            }
        });
    }
};

const getSeverityClass = (severity) => {
    const classes = {
        critical: 'bg-red-100 text-red-800',
        error: 'bg-orange-100 text-orange-800',
        warning: 'bg-yellow-100 text-yellow-800',
    };
    return classes[severity] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('en-NG');
};
</script>

<template>
  <Head title="Error Details" />

  <div class="relative min-h-screen bg-gray-50">
    <!-- Background grid -->
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>

    <div class="relative py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
          <Link :href="route('admin.error-logs.index')" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium mb-4 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Error Logs
          </Link>
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Error Details</h1>
              <p class="mt-2 text-gray-600">Error ID: #{{ error.id }}</p>
            </div>
            <div class="flex gap-3">
              <button v-if="!error.resolved_at" @click="resolve" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-all">
                Mark as Resolved
              </button>
              <button @click="deleteError" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-all">
                Delete
              </button>
            </div>
          </div>
        </div>

        <!-- Error Overview -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Overview</h2>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-2 gap-6">
              <div>
                <p class="text-sm text-gray-600 mb-1">Severity</p>
                <span :class="getSeverityClass(error.severity)" class="inline-flex px-3 py-1 rounded-full text-sm font-medium capitalize">
                  {{ error.severity }}
                </span>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">Status</p>
                <span v-if="error.resolved_at" class="inline-flex items-center text-green-600 text-sm font-medium">
                  <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                  </svg>
                  Resolved
                </span>
                <span v-else class="inline-flex items-center text-orange-600 text-sm font-medium">
                  <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                  Unresolved
                </span>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">Exception</p>
                <p class="text-sm font-mono text-gray-900 bg-gray-100 px-3 py-2 rounded">{{ error.exception_class }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">User</p>
                <p class="text-sm text-gray-900">{{ error.user?.name || 'Guest' }} {{ error.user ? `(ID: ${error.user.id})` : '' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">Occurred At</p>
                <p class="text-sm text-gray-900">{{ formatDate(error.created_at) }}</p>
              </div>
              <div v-if="error.resolved_at">
                <p class="text-sm text-gray-600 mb-1">Resolved At</p>
                <p class="text-sm text-gray-900">{{ formatDate(error.resolved_at) }}</p>
                <p class="text-xs text-gray-500 mt-1">By: {{ error.resolver?.name }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Error Message -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Error Message</h2>
          </div>
          <div class="p-6">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
              <p class="text-sm text-red-900 font-mono whitespace-pre-wrap">{{ error.message }}</p>
            </div>
          </div>
        </div>

        <!-- Location -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Location</h2>
          </div>
          <div class="p-6">
            <div class="space-y-3">
              <div>
                <p class="text-sm text-gray-600 mb-1">File</p>
                <p class="text-sm font-mono text-gray-900 bg-gray-100 px-3 py-2 rounded break-all">{{ error.file }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">Line</p>
                <p class="text-sm font-mono text-gray-900 bg-gray-100 px-3 py-2 rounded inline-block">{{ error.line }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Request Details -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Request Details</h2>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-2 gap-6">
              <div>
                <p class="text-sm text-gray-600 mb-1">URL</p>
                <p class="text-sm text-gray-900 bg-gray-100 px-3 py-2 rounded break-all">{{ error.url || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">Method</p>
                <span class="inline-flex px-3 py-1 bg-blue-100 text-blue-800 rounded text-sm font-medium">{{ error.method || 'N/A' }}</span>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">IP Address</p>
                <p class="text-sm text-gray-900 font-mono">{{ error.ip_address || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">User Agent</p>
                <p class="text-xs text-gray-900 bg-gray-100 px-3 py-2 rounded break-all">{{ error.user_agent || 'N/A' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Stack Trace -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Stack Trace</h2>
          </div>
          <div class="p-6">
            <div class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-x-auto">
              <pre class="text-xs font-mono whitespace-pre-wrap">{{ error.trace }}</pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
