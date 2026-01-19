<template>
  <div class="h-64">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from 'vue'
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend,
} from 'chart.js'

ChartJS.register(ArcElement, Tooltip, Legend)

const props = defineProps({
  data: {
    type: Array,
    default: () => [],
  },
})

const chartCanvas = ref(null)
let chartInstance = null

const colors = [
  'rgb(59, 130, 246)',
  'rgb(34, 197, 94)',
  'rgb(251, 146, 60)',
  'rgb(168, 85, 247)',
  'rgb(236, 72, 153)',
]

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
    },
    tooltip: {
      callbacks: {
        label: function (context) {
          const label = context.label || ''
          const value = '$' + context.parsed.toLocaleString()
          const total = context.dataset.data.reduce((a, b) => a + b, 0)
          const percentage = ((context.parsed / total) * 100).toFixed(1)
          return `${label}: ${value} (${percentage}%)`
        },
      },
    },
  },
}

const createChart = () => {
  if (chartInstance) {
    chartInstance.destroy()
  }

  if (!props.data || props.data.length === 0) {
    return
  }

  const chartData = {
    labels: props.data.map((d) => d.category),
    datasets: [
      {
        data: props.data.map((d) => d.amount),
        backgroundColor: colors.slice(0, props.data.length),
        borderWidth: 2,
        borderColor: '#fff',
      },
    ],
  }

  if (chartCanvas.value) {
    const ctx = chartCanvas.value.getContext('2d')
    chartInstance = new ChartJS(ctx, {
      type: 'doughnut',
      data: chartData,
      options: chartOptions,
    })
  }
}

onMounted(() => {
  createChart()
})

watch(
  () => props.data,
  () => {
    createChart()
  },
  { deep: true }
)

onBeforeUnmount(() => {
  if (chartInstance) {
    chartInstance.destroy()
  }
})
</script>
