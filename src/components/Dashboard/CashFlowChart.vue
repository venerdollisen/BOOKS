<template>
  <div class="h-64">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from 'vue'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const props = defineProps({
  data: {
    type: Array,
    default: () => [],
  },
})

const chartCanvas = ref(null)
let chartInstance = null

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
    tooltip: {
      mode: 'index',
      intersect: false,
      callbacks: {
        label: function (context) {
          return '$' + context.parsed.y.toLocaleString()
        },
      },
    },
  },
  scales: {
    x: {
      grid: {
        display: false,
      },
    },
    y: {
      beginAtZero: true,
      ticks: {
        callback: function (value) {
          return '$' + value.toLocaleString()
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
    labels: props.data.map((d) => d.month),
    datasets: [
      {
        label: 'Cash Flow',
        data: props.data.map((d) => d.amount),
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        fill: true,
        tension: 0.4,
      },
    ],
  }

  if (chartCanvas.value) {
    const ctx = chartCanvas.value.getContext('2d')
    chartInstance = new ChartJS(ctx, {
      type: 'line',
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
