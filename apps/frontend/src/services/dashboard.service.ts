import { useQuery } from '@tanstack/react-query'
import { http, unwrap } from '@/core/http'
import type { DashboardMetrics, Resource } from '@/core/types'

export const dashboardKeys = {
  metrics: ['dashboard'] as const,
}

export const dashboardService = {
  metrics: () => http.get<Resource<DashboardMetrics>>('/dashboard').then(unwrap),
}

export function useDashboard() {
  return useQuery({ queryKey: dashboardKeys.metrics, queryFn: dashboardService.metrics })
}
