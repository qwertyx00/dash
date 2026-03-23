<template>
  <div class="dashboard">

    <!-- Header -->
    <header class="dashboard-header">
      <div>
        <h1>Dashboard</h1>
        <p>Überblick über Traffic, Performance und Systemzustand</p>
      </div>
      <div class="meta">
        <span v-if="data">Letztes Update: {{ formattedUpdatedAt }}</span>
      </div>
    </header>

    <!-- Loading / Error -->
    <div v-if="loading" class="state state--info">
      Daten werden geladen …
    </div>
    <div v-else-if="error" class="state state--error">
      {{ error }}
    </div>

    <!-- Content -->
    <div v-else class="dashboard-content">

      <!-- KPI Row -->
      <section class="kpi-grid">
        <div class="kpi-card">
          <span class="kpi-label">Besucher</span>
          <span class="kpi-value">{{ data.visitors.total }}</span>
          <span class="kpi-sub">heute: {{ data.visitors.today }}</span>
        </div>

        <div class="kpi-card">
          <span class="kpi-label">Seitenaufrufe</span>
          <span class="kpi-value">{{ data.pageViews.total }}</span>
          <span class="kpi-sub">Ø / Besucher: {{ data.pageViews.perVisitor.toFixed(2) }}</span>
        </div>

        <div class="kpi-card">
          <span class="kpi-label">Fehlerrate</span>
          <span class="kpi-value">{{ data.errors.rate.toFixed(2) }}%</span>
          <span class="kpi-sub">4xx/5xx: {{ data.errors.count }}</span>
        </div>

        <div class="kpi-card">
          <span class="kpi-label">Antwortzeit</span>
          <span class="kpi-value">{{ data.performance.avgResponseTime }} ms</span>
          <span class="kpi-sub">P95: {{ data.performance.p95 }} ms</span>
        </div>
      </section>

      <!-- Two Columns -->
      <section class="grid-2">
        <!-- Länder -->
        <div class="panel">
          <div class="panel-header">
            <h2>Top Länder</h2>
          </div>
          <ul class="list">
            <li v-for="country in data.countries" :key="country.code" class="list-item">
              <span>{{ country.name }}</span>
              <span>{{ country.hits }}</span>
            </li>
          </ul>
        </div>

        <!-- Statuscodes -->
        <div class="panel">
          <div class="panel-header">
            <h2>Statuscodes</h2>
          </div>
          <ul class="list">
            <li v-for="status in data.statusCodes" :key="status.code" class="list-item">
              <span>{{ status.code }}</span>
              <span>{{ status.count }}</span>
            </li>
          </ul>
        </div>
      </section>

      <!-- Bots / Referrer -->
      <section class="grid-2">
        <div class="panel">
          <div class="panel-header">
            <h2>Bots</h2>
          </div>
          <ul class="list">
            <li v-for="bot in data.bots" :key="bot.name" class="list-item">
              <span>{{ bot.name }}</span>
              <span>{{ bot.hits }}</span>
            </li>
          </ul>
        </div>

        <div class="panel">
          <div class="panel-header">
            <h2>Top Referrer</h2>
          </div>
          <ul class="list">
            <li v-for="ref in data.referrers" :key="ref.host" class="list-item">
              <span class="truncate">{{ ref.host }}</span>
              <span>{{ ref.hits }}</span>
            </li>
          </ul>
        </div>
      </section>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

const loading = ref(true)
const error = ref(null)
const data = ref(null)

const formattedUpdatedAt = computed(() => {
  if (!data.value?.updatedAt) return ''
  return new Date(data.value.updatedAt).toLocaleString('de-DE')
})

onMounted(async () => {
  try {
const res = await fetch('http://localhost/admin/api/analytics/overview.php')
    if (!res.ok) throw new Error('Analytics konnten nicht geladen werden.')
    const json = await res.json()
    data.value = json
  } catch (e) {
    error.value = e.message || 'Unbekannter Fehler beim Laden der Analytics.'
  } finally {
    loading.value = false
  }
})
</script>

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
