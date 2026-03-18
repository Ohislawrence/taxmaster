<template>
  <div class="relative">
    <div class="flex items-center justify-end gap-2 mb-2">
      <button @click="exportPng" class="px-3 py-1 rounded bg-gray-100 text-sm">Export PNG</button>
      <button @click="exportSvg" class="px-3 py-1 rounded bg-gray-100 text-sm">Export SVG</button>
    </div>

    <ApexChart ref="chartRef" type="line" :options="options" :series="series" height="360" />
  </div>
</template>

<script setup>
import { defineProps, computed, ref } from 'vue';
import ApexChart from 'vue3-apexcharts';
import ApexCharts from 'apexcharts';

const props = defineProps({
  chartData: { type: Object, required: true },
  chartOptions: { type: Object, default: () => ({}) },
  smooth: { type: Boolean, default: true }
});

const chartRef = ref(null);

const series = computed(() => {
  return (props.chartData?.datasets || []).map(ds => ({ name: ds.label, data: ds.data }));
});

const options = computed(() => {
  const labels = props.chartData?.labels || [];
  const base = props.chartOptions || {};
  const merged = Object.assign({
    chart: { id: 'tax-trends-chart', toolbar: { show: true } },
    xaxis: { categories: labels, labels: { rotate: -45 } },
    stroke: { curve: props.smooth ? 'smooth' : 'straight', width: 3 },
    tooltip: { shared: true, intersect: false },
    legend: { position: 'top' },
    markers: { size: 4 }
  }, base);

  return merged;
});

function exportPng() {
  try {
    const el = chartRef.value?.$el || null;
    const btn = el?.querySelector('.apexcharts-toolbar .apexcharts-download-icon');
    if (btn) { btn.click(); return; }
    // fallback: use exec to get dataURI and trigger download
    if (ApexCharts && chartRef.value) {
      const chartId = options.value.chart?.id || 'tax-trends-chart';
      ApexCharts.exec(chartId, 'dataURI').then((resp) => {
        if (resp?.imgURI) {
          const a = document.createElement('a');
          a.href = resp.imgURI;
          a.download = 'tax-trends.png';
          document.body.appendChild(a);
          a.click();
          a.remove();
        }
      }).catch(()=>{});
    }
  } catch (e) {}
}

function exportSvg(){
  exportPng();
}

// no local registration needed; using imported component in template
</script>

<style scoped>
</style>
