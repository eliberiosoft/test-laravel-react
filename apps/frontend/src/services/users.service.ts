import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { http, unwrap, type Query } from '@/core/http'
import type { Paginated, Payload, Resource, User } from '@/core/types'
import { useApiMutation } from '@/shared/hooks'
import { dashboardKeys } from './dashboard.service'

export const usersKeys = {
  all: ['users'] as const,
  list: (query: Query) => ['users', 'list', query] as const,
}

export const usersService = {
  list: (query: Query) => http.get<Paginated<User>>('/users', query),
  create: (payload: Payload) => http.post<Resource<User>>('/users', payload).then(unwrap),
  update: ({ id, payload }: { id: number; payload: Payload }) => http.patch<Resource<User>>(`/users/${id}`, payload).then(unwrap),
  remove: (user: User) => http.delete(`/users/${user.id}`),
  unlock: (user: User) => http.post<Resource<User>>(`/users/${user.id}/unlock`).then(unwrap),
  revokeSessions: (user: User) => http.post(`/users/${user.id}/sessions/revoke`),
}

export function useUserList(query: Query) {
  return useQuery({ queryKey: usersKeys.list(query), queryFn: () => usersService.list(query), placeholderData: keepPreviousData })
}

export function useUserMutations() {
  const options = { invalidate: [usersKeys.all, dashboardKeys.metrics] }

  return {
    create: useApiMutation(usersService.create, options),
    update: useApiMutation(usersService.update, options),
    remove: useApiMutation(usersService.remove, options),
    unlock: useApiMutation(usersService.unlock, options),
    revokeSessions: useApiMutation(usersService.revokeSessions, options),
  }
}
