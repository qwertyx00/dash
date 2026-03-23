<script setup>
import { useAnalytics } from '@/composables/useAnalytics'
import { ref, watch, onMounted } from 'vue'

// API laden
const { loading, error, data, reload } = useAnalytics()

// Chart.js
import {
  Chart,
  LineController,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  BarController,
  BarElement,
  Tooltip,
  Legend
} from 'chart.js'

Chart.register(
  LineController,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  BarController,
  BarElement,
  Tooltip,
  Legend
)

let trafficChartInstance = null
let statusChartInstance = null

function renderCharts() {
  if (!data.value) return

  // -----------------------------
  // TRAFFIC (Monatliche Besucher)
  // -----------------------------
  const ctx1 = document.getElementById('trafficChart')

  if (ctx1) {
    if (trafficChartInstance) trafficChartInstance.destroy()

    trafficChartInstance = new Chart(ctx1, {
      type: 'line',
      data: {
        labels: data.value?.monthly?.map(m => m.month) ?? [],
        datasets: [
          {
            label: 'Besucher',
            data: data.value?.monthly?.map(m => m.unique) ?? [],
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78,115,223,0.1)',
            tension: 0.3
          },
          {
            label: 'Seitenaufrufe',
            data: data.value?.monthly?.map(m => m.pages) ?? [],
            borderColor: '#1cc88a',
            backgroundColor: 'rgba(28,200,138,0.1)',
            tension: 0.3
          }
        ]
      },
      options: { responsive: true }
    })
  }

  // -----------------------------
  // STATUSCODES (aus errors404)
  // -----------------------------
  const ctx2 = document.getElementById('statusChart')

  if (ctx2) {
    if (statusChartInstance) statusChartInstance.destroy()

    statusChartInstance = new Chart(ctx2, {
      type: 'bar',
      data: {
        labels: data.value?.errors404?.map(e => e.url) ?? [],
        datasets: [
          {
            label: 'Fehlerhits',
            data: data.value?.errors404?.map(e => e.hits) ?? [],
            backgroundColor: '#e74a3b'
          }
        ]
      },
      options: { responsive: true }
    })
  }
}

// Charts neu rendern wenn Daten geladen werden
watch(data, () => {
  if (data.value) {
    setTimeout(renderCharts, 50)
  }
})

onMounted(() => {
  if (data.value) renderCharts()
})
</script>

<template>
  <div class="dashboard">

    <!-- Header -->
    <div class="dashboard-header">
      <div>
        <h1>Dashboard</h1>
        <p class="meta">Letzte Aktualisierung: {{ data?.updatedAt ?? '–' }}</p>
      </div>

      <button class="reload-btn" @click="reload">
        🔄 Aktualisieren
      </button>
    </div>

    <!-- Loading / Error -->
    <div v-if="loading" class="state state--info">Lade Analytics…</div>
    <div v-else-if="error" class="state state--error">{{ error }}</div>

    <!-- Content -->
    <div v-else class="dashboard-content">

      <!-- KPI GRID -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <span class="kpi-label">Besucher (Monat)</span>
          <strong class="kpi-value">{{ data?.summary?.uniqueVisitors ?? 0 }}</strong>
          <span class="kpi-sub">Besuche: {{ data?.summary?.visits ?? 0 }}</span>
        </div>

        <div class="kpi-card">
          <span class="kpi-label">Seitenaufrufe</span>
          <strong class="kpi-value">{{ data?.summary?.pages ?? 0 }}</strong>
          <span class="kpi-sub">Hits: {{ data?.summary?.hits ?? 0 }}</span>
        </div>

        <div class="kpi-card">
          <span class="kpi-label">Traffic</span>
          <strong class="kpi-value">{{ data?.summary?.bandwidth ?? '0' }}</strong>
          <span class="kpi-sub">Gesehen</span>
        </div>

        <div class="kpi-card">
          <span class="kpi-label">Top OS</span>
          <strong class="kpi-value">{{ data?.os?.[0]?.name ?? '–' }}</strong>
          <span class="kpi-sub">{{ data?.os?.[0]?.hits ?? 0 }} Hits</span>
        </div>
      </div>

      <!-- CHARTS -->
      <div class="grid-2">
        <div class="panel">
          <div class="panel-header"><h2>Traffic Verlauf</h2></div>
          <canvas id="trafficChart"></canvas>
        </div>

        <div class="panel">
          <div class="panel-header"><h2>Fehler / 404</h2></div>
          <canvas id="statusChart"></canvas>
        </div>
      </div>

      <!-- REFERRER & BOTS -->
      <div class="grid-2">
        <div class="panel">
          <div class="panel-header"><h2>Top Referrer</h2></div>
          <ul class="list">
            <li v-for="r in (data?.referrers ?? [])" :key="r.host" class="list-item">
              <span class="truncate">{{ r.host }}</span>
              <strong>{{ r.hits }}</strong>
            </li>
          </ul>
        </div>

        <div class="panel">
          <div class="panel-header"><h2>Top Bots</h2></div>
          <ul class="list">
            <li v-for="b in (data?.robots ?? [])" :key="b.name" class="list-item">
              <span class="truncate">{{ b.name }}</span>
              <strong>{{ b.hits }}</strong>
            </li>
          </ul>
        </div>
      </div>

      <!-- LAST HOSTS -->
      <div class="panel">
        <div class="panel-header"><h2>Letzte Hosts</h2></div>
        <ul class="list">
          <li v-for="h in (data?.lastHosts ?? [])" :key="h.ip" class="list-item">
            <span class="truncate">{{ h.ip }}</span>
            <strong>{{ h.hits }}</strong>
          </li>
        </ul>
      </div>

    </div>
  </div>
</template>

<style scoped>
.dashboard {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  padding: 2rem 1.5rem;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 1rem;
}

.dashboard-header h1 {
  margin: 0;
  font-size: 2rem;
  font-weight: 600;
}

.dashboard-header p {
  margin: 0.25rem 0 0;
  color: #777;
}

.meta {
  font-size: 0.85rem;
  color: #888;
}

.state {
  padding: 1rem 1.2rem;
  border-radius: 8px;
  font-size: 0.95rem;
}

.state--info {
  background: #f3f6fb;
  color: #234;
}

.state--error {
  background: #fde8e8;
  color: #8b1c1c;
}

.dashboard-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* KPI Grid */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.5rem;
}

.kpi-card {
  background: #fff;
  border-radius: 12px;
  padding: 1.4rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.kpi-label {
  font-size: 0.85rem;
  color: #777;
}

.kpi-value {
  font-size: 1.6rem;
  font-weight: 600;
}

.kpi-sub {
  font-size: 0.85rem;
  color: #999;
}

/* Panels */
.grid-2 {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.5rem;
}

.panel {
  background: #fff;
  border-radius: 12px;
  padding: 1.2rem 1.4rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.panel-header h2 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
}

/* Lists */
.list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.list-item {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.35rem 0;
  font-size: 0.9rem;
  border-bottom: 1px solid #f1f1f1;
}

.list-item:last-child {
  border-bottom: none;
}

.truncate {
  max-width: 260px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
