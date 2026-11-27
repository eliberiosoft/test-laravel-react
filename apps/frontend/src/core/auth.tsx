import { createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from 'react'
import { Navigate, Outlet, useLocation, type Location } from 'react-router'
import { useQuery, useQueryClient } from '@tanstack/react-query'
import type { Permission } from '@/shared/domain'
import { PageLoader } from '@/shared/ui/Button'
import { UNAUTHENTICATED_EVENT, http, tokenStorage, unwrap, type ApiError } from './http'
import type { AuthSession, Credentials, MessageResponse, PasswordResetPayload, Resource, User } from './types'

const EXPIRED_MESSAGE = 'Tu sesión expiró. Inicia sesión nuevamente.'
const INVALID_MESSAGE = 'Tu sesión ya no es válida. Inicia sesión nuevamente.'
const MAX_TIMEOUT_MS = 2 ** 31 - 1

export const authKeys = {
  me: ['me'] as const,
}

const authApi = {
  login: (credentials: Credentials) => http.post<Resource<AuthSession>>('/auth/login', credentials).then(unwrap),
  logout: () => http.post('/auth/logout'),
  me: () => http.get<Resource<User>>('/auth/me').then(unwrap),
  forgotPassword: (email: string) => http.post<MessageResponse>('/auth/forgot-password', { email }),
  resetPassword: (payload: PasswordResetPayload) => http.post<MessageResponse>('/auth/reset-password', payload),
}

export interface RedirectState {
  from?: Location
  message?: string
}

interface AuthState {
  user: User | null
  notice: string | null
  isAuthenticated: boolean
  isLoading: boolean
  can: (permission: Permission) => boolean
  login: (credentials: Credentials) => Promise<void>
  logout: () => Promise<void>
  forgotPassword: (email: string) => Promise<string>
  resetPassword: (payload: PasswordResetPayload) => Promise<void>
}

const AuthContext = createContext<AuthState | null>(null)

export function useAuth(): AuthState {
  const context = useContext(AuthContext)

  if (!context) throw new Error('useAuth must be used inside <AuthProvider>.')

  return context
}

export function AuthProvider({ children }: { children: ReactNode }) {
  const queryClient = useQueryClient()
  const [token, setToken] = useState(() => tokenStorage.token())
  const [notice, setNotice] = useState<string | null>(null)
  const me = useQuery({ queryKey: authKeys.me, queryFn: authApi.me, enabled: Boolean(token) })

  const endSession = useCallback(
    (message: string | null = null) => {
      tokenStorage.clear()
      setToken(null)
      setNotice(message)
      queryClient.clear()
    },
    [queryClient],
  )

  useEffect(() => {
    const onUnauthenticated = (event: Event) => {
      const { code } = (event as CustomEvent<ApiError>).detail
      endSession(code === 'session_expired' ? EXPIRED_MESSAGE : INVALID_MESSAGE)
    }

    window.addEventListener(UNAUTHENTICATED_EVENT, onUnauthenticated)

    return () => window.removeEventListener(UNAUTHENTICATED_EVENT, onUnauthenticated)
  }, [endSession])

  useEffect(() => {
    const expiresAt = tokenStorage.read()?.expiresAt

    if (!token || !expiresAt) return undefined

    const remaining = new Date(expiresAt).getTime() - Date.now()

    if (remaining > MAX_TIMEOUT_MS) return undefined

    const timeout = setTimeout(() => endSession(EXPIRED_MESSAGE), Math.max(remaining, 0))

    return () => clearTimeout(timeout)
  }, [token, endSession])

  const login = useCallback(
    async (credentials: Credentials) => {
      const session = await authApi.login(credentials)

      tokenStorage.save(session.token, session.expires_at)
      queryClient.setQueryData(authKeys.me, session.user)
      setNotice(null)
      setToken(session.token)
    },
    [queryClient],
  )

  const logout = useCallback(async () => {
    await authApi.logout().catch(() => undefined)
    endSession()
  }, [endSession])

  const value = useMemo<AuthState>(() => {
    const user = token ? (me.data ?? null) : null

    return {
      user,
      notice,
      isAuthenticated: user !== null,
      isLoading: Boolean(token) && me.isPending,
      can: (permission) => user?.permissions?.includes(permission) ?? false,
      login,
      logout,
      forgotPassword: (email) => authApi.forgotPassword(email).then(({ message }) => message),
      resetPassword: (payload) => authApi.resetPassword(payload).then(() => undefined),
    }
  }, [token, me.data, me.isPending, notice, login, logout])

  return <AuthContext value={value}>{children}</AuthContext>
}

export function RequireAuth() {
  const { isAuthenticated, isLoading } = useAuth()
  const location = useLocation()

  if (isLoading) return <PageLoader />
  if (!isAuthenticated) return <Navigate to="/login" replace state={{ from: location } satisfies RedirectState} />

  return <Outlet />
}

export function GuestOnly() {
  const { isAuthenticated, isLoading } = useAuth()
  const state = useLocation().state as RedirectState | null

  if (isLoading) return <PageLoader />
  if (isAuthenticated) return <Navigate to={state?.from?.pathname ?? '/welcome'} replace />

  return <Outlet />
}

export function RequirePermission({ permission, fallback, children }: { permission: Permission; fallback: ReactNode; children: ReactNode }) {
  const { can } = useAuth()

  return can(permission) ? children : fallback
}
