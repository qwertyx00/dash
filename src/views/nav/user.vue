<script setup>
</script>

<template>
  <div class="user">

    <h1>Benutzer</h1>

  </div>
</template>

<style scoped>
.user {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  padding: 2rem 1.5rem;
}

.user-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 1rem;
}

.user-header h1 {
  margin: 0;
  font-size: 2rem;
  font-weight: 600;
}

.user-header p {
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

.user-content {
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
