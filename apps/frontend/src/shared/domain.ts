import type { ImportRowStatus, ImportStatus, RoleName, SessionEndReason, StatusStyle, UserStatus } from '@/core/types'

export const PERMISSIONS = {
  welcomeView: 'welcome.view',
  dashboardView: 'dashboard.view',
  profileView: 'profile.view',
  profileUpdate: 'profile.update',
  usersView: 'users.view',
  usersCreate: 'users.create',
  usersUpdate: 'users.update',
  usersDelete: 'users.delete',
  usersUnlock: 'users.unlock',
  usersImport: 'users.import',
  sessionsRevoke: 'sessions.revoke',
} as const

export type Permission = (typeof PERMISSIONS)[keyof typeof PERMISSIONS]

export const USER_STATUS: Record<UserStatus, StatusStyle> = {
  active: { label: 'Activo', tone: 'green' },
  blocked: { label: 'Bloqueado', tone: 'red' },
  inactive: { label: 'Inactivo', tone: 'gray' },
}

export const ROLE_LABELS: Record<RoleName, string> = {
  admin: 'Administrador',
  user: 'Usuario',
}

export const SESSION_END_REASONS: Record<SessionEndReason, string> = {
  logout: 'Cerró sesión',
  expired: 'Expiró',
  revoked: 'Revocada',
}

export const IMPORT_STATUS: Record<ImportStatus, StatusStyle> = {
  pending: { label: 'En cola', tone: 'amber' },
  processing: { label: 'Procesando', tone: 'sky' },
  completed: { label: 'Completada', tone: 'green' },
  failed: { label: 'Fallida', tone: 'red' },
}

export const IMPORT_ROW_STATUS: Record<ImportRowStatus, StatusStyle> = {
  created: { label: 'Creado', tone: 'green' },
  skipped: { label: 'Omitido', tone: 'amber' },
  failed: { label: 'Con errores', tone: 'red' },
}

export const roleNames = (roles: RoleName[] = []) => roles.map((role) => ROLE_LABELS[role]).join(', ')
