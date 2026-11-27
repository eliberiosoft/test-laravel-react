import type { ReactNode } from 'react'
import { Navigate, createBrowserRouter } from 'react-router'
import { GuestOnly, RequireAuth, RequirePermission } from '@/core/auth'
import { LoginPage } from '@/features/auth/LoginPage'
import { ForgotPasswordPage, ResetPasswordPage } from '@/features/auth/PasswordRecovery'
import { DashboardPage } from '@/features/dashboard/DashboardPage'
import { ForbiddenPage, NotFoundPage } from '@/features/errors/ErrorPages'
import { ImportPage } from '@/features/imports/ImportPage'
import { ProfilePage } from '@/features/profile/ProfilePage'
import { UsersPage } from '@/features/users/UsersPage'
import { WelcomePage } from '@/features/welcome/WelcomePage'
import { AppLayout } from '@/layout/AppLayout'
import { AuthLayout } from '@/layout/AuthLayout'
import { PERMISSIONS, type Permission } from '@/shared/domain'

const protectedPages: Array<{ path: string; permission: Permission; element: ReactNode }> = [
  { path: '/welcome', permission: PERMISSIONS.welcomeView, element: <WelcomePage /> },
  { path: '/dashboard', permission: PERMISSIONS.dashboardView, element: <DashboardPage /> },
  { path: '/users', permission: PERMISSIONS.usersView, element: <UsersPage /> },
  { path: '/users/import', permission: PERMISSIONS.usersImport, element: <ImportPage /> },
  { path: '/profile', permission: PERMISSIONS.profileView, element: <ProfilePage /> },
]

export const router = createBrowserRouter([
  { index: true, element: <Navigate to="/welcome" replace /> },
  {
    element: <GuestOnly />,
    children: [
      {
        element: <AuthLayout />,
        children: [
          { path: '/login', element: <LoginPage /> },
          { path: '/forgot-password', element: <ForgotPasswordPage /> },
          { path: '/reset-password', element: <ResetPasswordPage /> },
        ],
      },
    ],
  },
  {
    element: <RequireAuth />,
    children: [
      {
        element: <AppLayout />,
        children: protectedPages.map(({ path, permission, element }) => ({
          path,
          element: (
            <RequirePermission permission={permission} fallback={<ForbiddenPage />}>
              {element}
            </RequirePermission>
          ),
        })),
      },
    ],
  },
  { path: '*', element: <NotFoundPage /> },
])
