<template>
  <div class="p-6">
    <h1 class="text-2xl font-semibold mb-4">Tax Trends</h1>

    <!-- Quick stats header -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
      <div v-for="s in stats" :key="s.key" class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">{{ s.label }}</div>
        <div class="text-2xl font-bold mt-1">₦{{ formatMoney(s.total) }}</div>
        <div class="text-sm mt-1" :class="s.change>0 ? 'text-green-600' : s.change<0 ? 'text-rose-600' : 'text-gray-500'">
          {{ s.change === null ? 'No previous month' : formatPercent(s.change) }}
        </div>
      </div>
    </div>

    <div class="mb-4 flex items-center gap-3">
      <label class="text-sm text-gray-600">Months</label>
      <select v-model="months" @change="fetchData" class="border rounded px-2 py-1">
        <option v-for="m in [6,12,24]" :key="m" :value="m">{{ m }}</option>
      </select>

      <label class="ml-4 flex items-center gap-2 text-sm text-gray-600">
        <input type="checkbox" v-model="smooth" class="form-checkbox" />
        Smooth lines
      </label>
    </div>

    <div v-if="loading" class="text-gray-500">Loading...</div>

    <div v-else>
      <div class="mb-4">
        <h2 class="text-lg font-semibold mb-2">Insights</h2>
        <div v-if="insights.length === 0" class="text-sm text-gray-500">No insights at the moment.</div>
        <ul v-else class="space-y-2">
          <li v-for="ins in insights" :key="ins.key" class="bg-white p-3 rounded shadow-sm">
            <div class="flex items-start justify-between">
              <div>
                <div class="text-sm font-semibold">{{ ins.label }}</div>
                <div class="text-sm text-gray-700 mt-1">{{ ins.message }}</div>
                <div v-if="ins.ai_explanation" class="mt-2 text-sm text-gray-600">{{ ins.ai_explanation }}</div>
                <div v-if="(anomalies[ins.key.split('_')[0]] || []).length" class="mt-2 text-sm">
                  <div class="text-xs text-gray-500">Top drivers:</div>
                  <ul class="text-sm">
                    <li v-for="drv in anomalies[ins.key.split('_')[0]]" :key="drv.type + drv.id">
                      <a :href="drv.link" class="text-blue-600 hover:underline">{{ drv.label }}</a> — ₦{{ Number(drv.value).toLocaleString() }}
                    </li>
                  </ul>
                </div>
              </div>
              <div class="text-sm" :class="ins.severity === 'warning' ? 'text-rose-600' : 'text-gray-500'">{{ ins.value ? (ins.value.toFixed ? ins.value.toFixed(1) + '%' : ins.value) : '' }}</div>
            </div>
          </li>
        </ul>
      </div>

      <ApexLineChart :chart-data="chartData" :chart-options="chartOptions" :smooth="smooth" />

      <div class="mt-3 flex gap-4">
        <div v-for="(ds, idx) in datasets" :key="ds.key" class="flex items-center gap-2">
          <span :style="{background: colors[idx]}" class="w-4 h-2 inline-block"></span>
          <div class="text-sm">{{ ds.label }} — <strong>{{ formatMoney(sum(ds.data)) }}</strong></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import ApexLineChart from '@/Components/Charts/ApexLineChart.vue';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

defineOptions({ layout: BusinessLayout });

const months = ref(12);
const loading = ref(false);
const labels = ref([]);
const datasets = ref([]);

const width = 760;
const height = 320;
const pad = 40;
const innerWidth = width - pad * 2;
const innerHeight = height - pad * 2;
const colors = ['#2563EB', '#16A34A', '#F59E0B'];

const chartData = ref({ labels: [], datasets: [] });
const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'top' }
  },
  scales: {
    y: { beginAtZero: true, ticks: { callback: (v) => v.toLocaleString() } }
  }
});

const smooth = ref(true);
const insights = ref([]);
const anomalies = ref({});

// computed quick stats for header
const stats = computed(() => {
  return (datasets.value || []).map(ds => {
    const total = (ds.data || []).reduce((s, v) => s + (Number(v) || 0), 0);
    const len = (ds.data || []).length;
    const last = len ? Number(ds.data[len-1] || 0) : 0;
    const prev = len > 1 ? Number(ds.data[len-2] || 0) : 0;
    const change = prev !== 0 ? ((last - prev) / Math.abs(prev)) * 100 : null;
    return { key: ds.key || ds.label, label: ds.label, total, last, change };
  });
});

function formatPercent(p){
  if (p === null) return '—';
  const sign = p > 0 ? '+' : '';
  return sign + p.toFixed(1) + '%';
}

function shortLabel(lbl) {
  try {
    const [y,m] = lbl.split('-');
    const d = new Date(y, parseInt(m)-1, 1);
    return d.toLocaleString(undefined, { month: 'short' });
  } catch (e) { return lbl; }
}

function sum(arr){ return arr.reduce((s,v)=>s+v,0); }
function formatMoney(v){ return Number(v).toLocaleString(undefined, {maximumFractionDigits:2}); }

function xFor(i){
  if (!labels.value.length) return 0;
  return (i / Math.max(1, labels.value.length - 1)) * innerWidth;
}

function pathFor(values){
  if (!values || values.length === 0) return '';
  const max = Math.max(...values, 1);
  return values.map((v,i)=>{
    const x = xFor(i);
    const y = innerHeight - (v / max) * innerHeight;
    return `${i===0?'M':'L'} ${x} ${y}`;
  }).join(' ');
}

const yValues = computed(()=>{
  // compute scale ticks based on max across datasets
  const all = datasets.value.flatMap(d=>d.data);
  const max = Math.max(...all, 1);
  const step = Math.ceil(max / 4);
  const ticks = {};
  for(let i=4;i>=0;i--){
    const val = i * step;
    const y = innerHeight - (val / Math.max(1, 4*step)) * innerHeight;
    ticks[y.toFixed(2)] = val;
  }
  return ticks;
});

const yTicks = computed(()=>Object.keys(yValues.value));

async function fetchData(){
  loading.value = true;
  try{
    const res = await axios.get(`/business/insights/tax-trends?months=${months.value}`);
    labels.value = res.data.labels || [];
    datasets.value = res.data.datasets || [];
    // prepare chartData
    chartData.value = {
      labels: labels.value.map(l => {
        const [y,m] = l.split('-');
        return new Date(y, parseInt(m)-1).toLocaleString(undefined, { month: 'short', year: 'numeric' });
      }),
      datasets: datasets.value.map((ds, idx) => ({
        label: ds.label,
        data: ds.data,
        borderColor: colors[idx],
        backgroundColor: colors[idx] + '33',
        tension: 0.3,
        fill: false,
      }))
    };
  } finally { loading.value = false; }
}

async function fetchInsights(){
  try{
    const r = await axios.get(`/business/insights/summary?months=${months.value}`);
    insights.value = r.data.insights || [];
  }catch(e){ insights.value = []; }
}

async function fetchAnomalies(){
  try{
    const r = await axios.get(`/business/insights/anomalies?months=${months.value}`);
    anomalies.value = r.data || {};
  }catch(e){ anomalies.value = {}; }
}

onMounted(fetchData);
onMounted(()=>{ fetchData(); fetchInsights(); fetchAnomalies(); });
</script>

<style scoped>
/* Minimal styling */
</style>
