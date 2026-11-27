import type { ComponentType, SVGProps } from 'react'

export type UserStatus = 'active' | 'blocked' | 'inactive'
export type RoleName = 'admin' | 'user'
export type SessionEndReason = 'logout' | 'expired' | 'revoked'
export type ImportStatus = 'pending' | 'processing' | 'completed' | 'failed'
export type ImportRowStatus = 'created' | 'skipped' | 'failed'

export interface Person {
  id: number
  first_name: string
  middle_name: string | null
  first_surname: string
  second_surname: string | null
  full_name: string
  identification: string
  birth_date: string | null
}

export interface User {
  id: number
  username: string
  email: string
  status: UserStatus
  person?: Person
  roles?: RoleName[]
  permissions?: string[]
}

export interface AuthSession {
  token: string
  token_type: string
  expires_at: string | null
  user: User
}

export interface Credentials {
  login: string
  password: string
}

export interface PasswordResetPayload {
  email: string
  token: string
  password: string
  password_confirmation: string
}

export interface MessageResponse {
  message: string
}

export type Payload = Record<string, string>

export interface Resource<T> {
  data: T
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  from: number | null
  to: number | null
  total: number
}

export interface Paginated<T> {
  data: T[]
  meta: PaginationMeta
}

export interface MenuItem {
  key: string
  label: string
  route: string | null
  icon: string | null
  children: MenuItem[]
}

export interface WelcomeSummary {
  user: User
  last_session: {
    started_at: string
    ended_at: string | null
    end_reason: SessionEndReason | null
    ip_address: string | null
  } | null
  failed_attempts: {
    since_last_session: number
    last_attempt_at: string | null
  }
}

export interface DashboardMetrics {
  accounts: Record<'total' | UserStatus, number>
  sessions: { active: number; started_last_24_hours: number }
  failed_attempts: { last_24_hours: number; last_7_days: number; unknown_accounts_last_7_days: number }
}

export interface UserImport {
  id: number
  status: ImportStatus
  file_name: string
  summary: Record<'total' | ImportRowStatus, number>
  error: string | null
  started_at: string | null
  finished_at: string | null
  created_at: string
}

export interface ImportRow {
  line: number
  status: ImportRowStatus
  identification: string | null
  email: string | null
  errors: Record<string, string[]>
}

export interface ApiErrorBody {
  message?: string
  code?: string
  errors?: Record<string, string[]>
}

export type IconComponent = ComponentType<SVGProps<SVGSVGElement>>

export type BadgeTone = 'green' | 'red' | 'amber' | 'sky' | 'indigo' | 'gray'

export interface StatusStyle {
  label: string
  tone: BadgeTone
}
