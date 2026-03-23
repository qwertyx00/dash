export async function getAnalyticsOverview() {
  const res = await fetch('/api/analytics/overview.php')

  if (!res.ok) {
    throw new Error('Analytics API error')
  }

  return await res.json()
}
