import { ref } from 'vue'
import { getAnalyticsOverview } from '@/services/analytics'

export function useAnalytics() {
  const loading = ref(true)
  const error = ref(null)
  const data = ref(null)

  async function load() {
    loading.value = true
    error.value = null

    try {
      data.value = await getAnalyticsOverview()
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  load()

  return { loading, error, data, reload: load }
}
