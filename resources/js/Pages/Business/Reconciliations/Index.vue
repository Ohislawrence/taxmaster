<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

defineOptions({ layout: BusinessLayout });

const page = usePage();
const reconciliations = reactive(page.props.reconciliations || {});

const form = useForm();
const processingMap = ref({});
const selected = ref(null);
const showModal = ref(false);

async function openDetails(item) {
  selected.value = item;
  showModal.value = true;

  // Reset signed urls
  selected.value.signedInvoiceUrl = null;
  selected.value.attachmentUrls = null;

  try {
    // Fetch signed invoice PDF URL if invoice exists
    if (selected.value.invoice && selected.value.invoice.id) {
      const res = await fetch(route('business.invoices.pdf.signed.business', { invoice: selected.value.invoice.id }), { credentials: 'same-origin' });
      if (res.ok) {
        const payload = await res.json();
        selected.value.signedInvoiceUrl = payload.url;
      }
    }

    // Fetch signed URLs for attachments if available
    if (selected.value.transaction && selected.value.transaction.attachments && selected.value.transaction.attachments.length) {
      selected.value.attachmentUrls = [];
      for (let i = 0; i < selected.value.transaction.attachments.length; i++) {
        try {
          const r = await fetch(route('business.transactions.reconciliations.attachment.signed', { reconciliation: selected.value.id, index: i }), { credentials: 'same-origin' });
          if (r.ok) {
            const p = await r.json();
            selected.value.attachmentUrls[i] = p.url;
          } else {
            selected.value.attachmentUrls[i] = null;
          }
        } catch (err) {
          selected.value.attachmentUrls[i] = null;
        }
      }
    }
  } catch (e) {
    console.error('Failed to fetch signed urls', e);
  }
}

const confirm = (id) => {
  if (!window.confirm('Confirm this reconciliation?')) return;

  // optimistic update
  const idx = reconciliations.data.findIndex((r) => r.id === id);
  const original = idx !== -1 ? { ...reconciliations.data[idx] } : null;
  if (idx !== -1) reconciliations.data[idx].status = 'confirmed';
  processingMap.value[id] = true;

  form.post(route('business.transactions.reconciliations.confirm', { reconciliation: id }), {
    preserveScroll: true,
    onSuccess: () => {
      processingMap.value[id] = false;
    },
    onError: () => {
      // revert optimistic change
      if (idx !== -1 && original) reconciliations.data[idx].status = original.status;
      processingMap.value[id] = false;
    },
  });
};

const reject = (id) => {
  if (!window.confirm('Reject this reconciliation?')) return;

  const idx = reconciliations.data.findIndex((r) => r.id === id);
  const original = idx !== -1 ? { ...reconciliations.data[idx] } : null;
  if (idx !== -1) reconciliations.data[idx].status = 'rejected';
  processingMap.value[id] = true;

  form.post(route('business.transactions.reconciliations.reject', { reconciliation: id }), {
    preserveScroll: true,
    onSuccess: () => {
      processingMap.value[id] = false;
    },
    onError: () => {
      if (idx !== -1 && original) reconciliations.data[idx].status = original.status;
      processingMap.value[id] = false;
    },
  });
};
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold">Reconciliations</h1>
      <Link :href="route('business.transactions.index')" class="text-sm text-blue-600">Back to transactions</Link>
    </div>

    <div class="bg-white rounded-lg p-4 shadow-sm">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500 border-b">
            <th class="p-2">Date</th>
            <th class="p-2">Transaction</th>
            <th class="p-2">Invoice</th>
            <th class="p-2">Method</th>
            <th class="p-2">Confidence</th>
            <th class="p-2">Status</th>
            <th class="p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in reconciliations.data" :key="item.id" class="border-b hover:bg-gray-50">
            <td class="p-3 align-top w-36">{{ item.matched_at ? new Date(item.matched_at).toLocaleString() : '-' }}</td>
            <td class="p-3 align-top">
              <div class="font-medium truncate">{{ item.transaction ? item.transaction.description : '—' }}</div>
              <div class="text-xs text-gray-500">{{ item.transaction ? (item.transaction.reference || '') + ' • ₦' + (item.transaction.amount ?? '') : '' }}</div>
            </td>
            <td class="p-3 align-top">
              <div class="font-medium">{{ item.invoice ? item.invoice.invoice_number : '—' }}</div>
              <div class="text-xs text-gray-500">{{ item.invoice ? '₦' + item.invoice.total : '' }}</div>
            </td>
            <td class="p-3 align-top">{{ item.match_method }}</td>
            <td class="p-3 align-top">{{ (item.confidence * 1).toFixed(2) }}</td>
            <td class="p-3 align-top">
              <span :class="{
                'px-2 py-1 rounded-full text-xs font-semibold': true,
                'bg-green-100 text-green-800': item.status === 'confirmed' || item.status === 'matched',
                'bg-yellow-100 text-yellow-800': item.status === 'pending',
                'bg-red-100 text-red-800': item.status === 'rejected',
                'bg-gray-100 text-gray-800': item.status === 'pending' === false && !['confirmed','matched','rejected'].includes(item.status)
              }">{{ item.status }}</span>
            </td>
            <td class="p-3 align-top">
              <div class="flex gap-2 items-center">
                <button @click.prevent="openDetails(item)" class="px-2 py-1 border rounded text-sm">Details</button>
                <button :disabled="processingMap[item.id]" v-if="item.status !== 'confirmed'" @click="confirm(item.id)" class="px-3 py-1 bg-green-600 text-white rounded text-sm">{{ processingMap[item.id] ? 'Confirming…' : 'Confirm' }}</button>
                <button :disabled="processingMap[item.id]" v-if="item.status !== 'rejected'" @click="reject(item.id)" class="px-3 py-1 bg-red-600 text-white rounded text-sm">{{ processingMap[item.id] ? 'Rejecting…' : 'Reject' }}</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="mt-4 flex justify-end">
        <Link v-if="reconciliations.prev_page_url" :href="reconciliations.prev_page_url" class="px-3 py-1 border rounded mr-2">Previous</Link>
        <Link v-if="reconciliations.next_page_url" :href="reconciliations.next_page_url" class="px-3 py-1 border rounded">Next</Link>
      </div>
    </div>

    <!-- Details modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/40" @click="showModal = false"></div>
      <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full z-10 p-6">
        <div class="flex justify-between items-start">
          <h3 class="text-lg font-semibold">Reconciliation details</h3>
          <button @click="showModal = false" class="text-gray-500">Close</button>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-6">
          <div>
            <h4 class="font-medium">Transaction</h4>
            <p class="text-sm text-gray-700">Description: {{ selected?.transaction?.description || '—' }}</p>
            <p class="text-sm text-gray-700">Amount: ₦{{ selected?.transaction?.amount || '—' }}</p>
            <p class="text-sm text-gray-700">Date: {{ selected?.transaction?.transaction_date || '—' }}</p>
            <div class="mt-2">
              <h5 class="font-medium text-sm mb-2">Attachments</h5>
              <div class="space-y-2">
                <template v-if="selected?.transaction?.attachments && selected.transaction.attachments.length">
                  <div v-for="(att, i) in selected.transaction.attachments" :key="i" class="flex items-center gap-3">
                    <div v-if="/(jpg|jpeg|png|gif)$/i.test(att)">
                      <img :src="att.startsWith('/') ? att : `/storage/${att}`" class="w-24 h-16 object-cover rounded" alt="attachment" />
                    </div>
                    <a :href="att.startsWith('/') ? att : `/storage/${att}`" target="_blank" class="text-sm text-blue-600 hover:underline">View / Download</a>
                  </div>
                </template>
                <div v-else class="text-xs text-gray-500">No attachments</div>
              </div>
            </div>
            <pre class="mt-2 text-xs bg-gray-100 p-2 rounded overflow-auto">{{ JSON.stringify(selected?.transaction?.meta || selected?.transaction || {}, null, 2) }}</pre>
          </div>

          <div>
            <h4 class="font-medium">Invoice</h4>
            <p class="text-sm text-gray-700">Number: {{ selected?.invoice?.invoice_number || '—' }}</p>
            <p class="text-sm text-gray-700">Total: ₦{{ selected?.invoice?.total || '—' }}</p>
            <p class="text-sm text-gray-700">Date: {{ selected?.invoice?.invoice_date || '—' }}</p>

            <div class="mt-3">
              <h5 class="font-medium text-sm mb-2">Invoice preview</h5>
              <div v-if="selected?.invoice?.pdf_path" class="border rounded overflow-hidden">
                <iframe :src="selected.invoice.pdf_path.startsWith('/') ? selected.invoice.pdf_path : `/storage/${selected.invoice.pdf_path}`" class="w-full h-96" />
              </div>
              <div v-else class="text-xs text-gray-500">No PDF available. <a v-if="selected?.invoice" :href="route('business.invoices.show', { invoice: selected.invoice.id })" class="text-blue-600 hover:underline">Open invoice</a></div>
            </div>

            <pre class="mt-2 text-xs bg-gray-100 p-2 rounded overflow-auto">{{ JSON.stringify(selected?.invoice || {}, null, 2) }}</pre>
          </div>
        </div>

        <div class="mt-4 flex justify-end gap-2">
          <button @click="confirm(selected.id)" class="px-4 py-2 bg-green-600 text-white rounded">Confirm</button>
          <button @click="reject(selected.id)" class="px-4 py-2 bg-red-600 text-white rounded">Reject</button>
        </div>
      </div>
    </div>
  </div>
</template>
