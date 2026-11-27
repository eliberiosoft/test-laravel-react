import { useSearchParams } from 'react-router'
import type { Query } from '@/core/http'
import type { User } from '@/core/types'
import { PERMISSIONS, type Permission } from '@/shared/domain'
import { fieldRules, rules, type Rule } from '@/shared/validation'

export const USERS_PER_PAGE = 10
export const SEARCH_MIN_LENGTH = 3

const DEFAULT_SORT = '-created_at'

export interface UserListParams {
  search: string
  status: string
  role: string
  sort: string
  page: number
}

export function toUserQuery(params: UserListParams): Query {
  return {
    'filter[search]': params.search.length >= SEARCH_MIN_LENGTH ? params.search : undefined,
    'filter[status]': params.status,
    'filter[role]': params.role,
    sort: params.sort,
    page: params.page,
    per_page: USERS_PER_PAGE,
  }
}

export function useUserListParams() {
  const [searchParams, setSearchParams] = useSearchParams()

  const params: UserListParams = {
    search: searchParams.get('search') ?? '',
    status: searchParams.get('status') ?? '',
    role: searchParams.get('role') ?? '',
    sort: searchParams.get('sort') ?? DEFAULT_SORT,
    page: Number(searchParams.get('page') ?? 1),
  }

  const update = (changes: Partial<UserListParams>) => {
    setSearchParams(
      (current) => {
        const next = new URLSearchParams(current)

        Object.entries(changes).forEach(([key, value]) => (value ? next.set(key, String(value)) : next.delete(key)))

        if (!('page' in changes)) next.delete('page')

        return next
      },
      { replace: true },
    )
  }

  return { params, update, hasFilters: Boolean(params.search || params.status || params.role) }
}

export type UserAction = 'edit' | 'unlock' | 'revoke' | 'delete'

export function availableActions(target: User, actor: User | null, can: (permission: Permission) => boolean): UserAction[] {
  const isSelf = actor?.id === target.id
  const isManageable = !isSelf && !target.roles?.includes('admin')

  const allowed: Array<[UserAction, boolean]> = [
    ['edit', isManageable && can(PERMISSIONS.usersUpdate)],
    ['unlock', !isSelf && target.status === 'blocked' && can(PERMISSIONS.usersUnlock)],
    ['revoke', isManageable && can(PERMISSIONS.sessionsRevoke)],
    ['delete', isManageable && can(PERMISSIONS.usersDelete)],
  ]

  return allowed.filter(([, isAllowed]) => isAllowed).map(([action]) => action)
}

export interface PersonValues {
  first_name: string
  middle_name: string
  first_surname: string
  second_surname: string
  birth_date: string
  [key: string]: string
}

export const PERSON_NAME_FIELDS = [
  { name: 'first_name', label: 'Primer nombre', autoComplete: 'given-name', optional: false },
  { name: 'middle_name', label: 'Segundo nombre', autoComplete: 'additional-name', optional: true },
  { name: 'first_surname', label: 'Primer apellido', autoComplete: 'family-name', optional: false },
  { name: 'second_surname', label: 'Segundo apellido', autoComplete: 'off', optional: true },
] as const

export const personSchema: Record<string, Rule[]> = {
  first_name: [rules.required(), ...fieldRules.personName],
  middle_name: fieldRules.personName,
  first_surname: [rules.required(), ...fieldRules.personName],
  second_surname: fieldRules.personName,
}

export function personValues(user?: User | null): PersonValues {
  const person = user?.person

  return {
    first_name: person?.first_name ?? '',
    middle_name: person?.middle_name ?? '',
    first_surname: person?.first_surname ?? '',
    second_surname: person?.second_surname ?? '',
    birth_date: person?.birth_date ?? '',
  }
}

export const today = () => new Date().toISOString().slice(0, 10)
