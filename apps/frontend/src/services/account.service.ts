import { useQuery } from '@tanstack/react-query'
import { authKeys } from '@/core/auth'
import { http, unwrap } from '@/core/http'
import type { MenuItem, Payload, Resource, User, WelcomeSummary } from '@/core/types'
import { useApiMutation } from '@/shared/hooks'

export const accountKeys = {
  menu: ['menu'] as const,
  welcome: ['welcome'] as const,
}

export const accountService = {
  menu: () => http.get<Resource<MenuItem[]>>('/menu').then(unwrap),
  welcome: () => http.get<Resource<WelcomeSummary>>('/welcome').then(unwrap),
  updateProfile: (payload: Payload) => http.patch<Resource<User>>('/profile', payload).then(unwrap),
}

export function useMenu() {
  return useQuery({ queryKey: accountKeys.menu, queryFn: accountService.menu, staleTime: Infinity })
}

export function useWelcome() {
  return useQuery({ queryKey: accountKeys.welcome, queryFn: accountService.welcome })
}

export function useUpdateProfile() {
  return useApiMutation(accountService.updateProfile, { invalidate: [authKeys.me, accountKeys.welcome] })
}
