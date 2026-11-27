import { useEffect } from 'react'
import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { http, unwrap, type Query } from '@/core/http'
import type { ImportRow, ImportStatus, Paginated, Resource, UserImport } from '@/core/types'
import { dashboardKeys } from './dashboard.service'
import { usersKeys } from './users.service'

const POLL_INTERVAL_MS = 1500

export const importsKeys = {
  detail: (id: number) => ['imports', id] as const,
  rows: (id: number, query: Query) => ['imports', id, 'rows', query] as const,
}

export const importsService = {
  upload: (file: File) => {
    const body = new FormData()
    body.append('file', file)

    return http.post<Resource<UserImport>>('/users/imports', body).then(unwrap)
  },
  find: (id: number) => http.get<Resource<UserImport>>(`/users/imports/${id}`).then(unwrap),
  rows: (id: number, query: Query) => http.get<Paginated<ImportRow>>(`/users/imports/${id}/rows`, query),
}

export const isImportRunning = (status: ImportStatus | undefined) => status === 'pending' || status === 'processing'

export function useImport(id: number | null) {
  const queryClient = useQueryClient()

  const query = useQuery({
    queryKey: importsKeys.detail(id ?? 0),
    queryFn: () => importsService.find(id ?? 0),
    enabled: id !== null,
    refetchInterval: (current) => (isImportRunning(current.state.data?.status) ? POLL_INTERVAL_MS : false),
  })

  const status = query.data?.status

  useEffect(() => {
    if (status !== 'completed') return

    void queryClient.invalidateQueries({ queryKey: usersKeys.all })
    void queryClient.invalidateQueries({ queryKey: dashboardKeys.metrics })
  }, [status, queryClient])

  return query
}

export function useImportRows(id: number, query: Query, enabled: boolean) {
  return useQuery({ queryKey: importsKeys.rows(id, query), queryFn: () => importsService.rows(id, query), enabled, placeholderData: keepPreviousData })
}

export function useUploadImport() {
  return useMutation({ mutationFn: importsService.upload })
}
