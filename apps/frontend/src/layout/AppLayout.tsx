import { useEffect, useState } from 'react'
import { NavLink, Outlet } from 'react-router'
import { ArrowRightStartOnRectangleIcon } from '@heroicons/react/20/solid'
import {
  ArrowUpTrayIcon,
  Bars3Icon,
  ChartBarIcon,
  HomeIcon,
  ListBulletIcon,
  Squares2X2Icon,
  UserCircleIcon,
  UsersIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline'
import { useAuth } from '@/core/auth'
import type { IconComponent, MenuItem } from '@/core/types'
import { useMenu } from '@/services/account.service'
import { ROLE_LABELS } from '@/shared/domain'
import { useModalDialog } from '@/shared/hooks'
import { Button, Spinner } from '@/shared/ui/Button'
import { Skeleton } from '@/shared/ui/Feedback'
import { Brand } from '@/shared/ui/Layout'
import { cx, initials } from '@/shared/utils'

const DESKTOP_QUERY = '(min-width: 64rem)'

const MENU_ICONS: Record<string, IconComponent> = {
  home: HomeIcon,
  chart: ChartBarIcon,
  users: UsersIcon,
  list: ListBulletIcon,
  upload: ArrowUpTrayIcon,
  user: UserCircleIcon,
}

function MenuLink({ item, onNavigate }: { item: MenuItem; onNavigate: () => void }) {
  const Icon = MENU_ICONS[item.icon ?? ''] ?? Squares2X2Icon

  return (
    <li>
      <NavLink
        to={item.route ?? '/'}
        end
        onClick={onNavigate}
        className={({ isActive }) =>
          cx(
            'group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold transition-colors duration-150',
            isActive ? 'bg-gray-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600',
          )
        }
      >
        {({ isActive }) => (
          <>
            <Icon aria-hidden="true" className={cx('size-6 shrink-0', isActive ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600')} />
            {item.label}
          </>
        )}
      </NavLink>
    </li>
  )
}

function MenuNav({ onNavigate }: { onNavigate: () => void }) {
  const menu = useMenu()

  if (menu.isPending) {
    return (
      <div className="space-y-3" role="status" aria-label="Cargando menú">
        {Array.from({ length: 4 }, (_, index) => (
          <Skeleton key={index} className="h-8 w-full" />
        ))}
      </div>
    )
  }

  if (menu.isError) {
    return (
      <div className="text-sm text-gray-500">
        No se pudo cargar el menú.{' '}
        <Button variant="link" onClick={() => menu.refetch()}>
          Reintentar
        </Button>
      </div>
    )
  }

  return (
    <ul role="list" className="-mx-2 space-y-1">
      {menu.data.map((item) =>
        item.children.length > 0 ? (
          <li key={item.key}>
            <div className="px-2 pt-4 pb-1 text-xs/6 font-semibold text-gray-500">{item.label}</div>
            <ul role="list" className="space-y-1">
              {item.children.map((child) => (
                <MenuLink key={child.key} item={child} onNavigate={onNavigate} />
              ))}
            </ul>
          </li>
        ) : (
          <MenuLink key={item.key} item={item} onNavigate={onNavigate} />
        ),
      )}
    </ul>
  )
}

function UserSummary() {
  const { user, logout } = useAuth()
  const [isLoggingOut, setIsLoggingOut] = useState(false)

  if (!user) return null

  const name = user.person?.full_name ?? user.username
  const role = user.roles?.[0]

  const handleLogout = async () => {
    setIsLoggingOut(true)
    await logout()
  }

  return (
    <div className="-mx-6 mt-auto flex items-center gap-x-3 border-t border-gray-200 px-6 py-4">
      <span aria-hidden="true" className="flex size-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-sm font-semibold text-indigo-700">
        {initials(name)}
      </span>
      <div className="min-w-0 flex-1">
        <p className="truncate text-sm font-semibold text-gray-900">{name}</p>
        <p className="truncate text-xs text-gray-500">{role ? ROLE_LABELS[role] : user.username}</p>
      </div>
      <button
        type="button"
        onClick={handleLogout}
        disabled={isLoggingOut}
        className="rounded-md p-2 text-gray-500 transition-colors duration-150 hover:bg-gray-50 hover:text-gray-700 disabled:opacity-60"
      >
        <span className="sr-only">Cerrar sesión</span>
        {isLoggingOut ? <Spinner className="size-5" /> : <ArrowRightStartOnRectangleIcon aria-hidden="true" className="size-5" />}
      </button>
    </div>
  )
}

function SidebarContent({ onNavigate }: { onNavigate: () => void }) {
  return (
    <div className="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 bg-white px-6">
      <div className="flex h-16 shrink-0 items-center">
        <Brand />
      </div>
      <nav aria-label="Menú principal" className="flex flex-1 flex-col">
        <MenuNav onNavigate={onNavigate} />
        <UserSummary />
      </nav>
    </div>
  )
}

function MobileSidebar({ open, onClose }: { open: boolean; onClose: () => void }) {
  const dialogProps = useModalDialog(open, onClose)

  return (
    <dialog
      {...dialogProps}
      aria-label="Menú principal"
      className="m-0 h-full max-h-none w-full max-w-xs overflow-hidden bg-transparent p-0 backdrop:bg-gray-900/80 lg:hidden"
    >
      {open && (
        <div className="relative flex h-full">
          <SidebarContent onNavigate={onClose} />
          <button type="button" onClick={onClose} className="absolute top-3.5 right-3 rounded-md p-1.5 text-gray-500 hover:bg-gray-50 hover:text-gray-700">
            <span className="sr-only">Cerrar menú</span>
            <XMarkIcon aria-hidden="true" className="size-6" />
          </button>
        </div>
      )}
    </dialog>
  )
}

export function AppLayout() {
  const [isMenuOpen, setIsMenuOpen] = useState(false)
  const closeMenu = () => setIsMenuOpen(false)

  useEffect(() => {
    const desktop = window.matchMedia(DESKTOP_QUERY)
    const closeOnDesktop = (event: MediaQueryListEvent) => {
      if (event.matches) setIsMenuOpen(false)
    }

    desktop.addEventListener('change', closeOnDesktop)

    return () => desktop.removeEventListener('change', closeOnDesktop)
  }, [])

  return (
    <div className="min-h-full">
      <a
        href="#contenido"
        className="sr-only rounded-md bg-white px-3 py-2 text-sm font-semibold text-indigo-600 shadow-xs ring-1 ring-gray-900/5 focus:not-sr-only focus:absolute focus:top-3 focus:left-3 focus:z-50"
      >
        Saltar al contenido
      </a>

      <MobileSidebar open={isMenuOpen} onClose={closeMenu} />

      <aside className="hidden lg:fixed lg:inset-y-0 lg:z-40 lg:flex lg:w-72 lg:flex-col">
        <SidebarContent onNavigate={closeMenu} />
      </aside>

      <header className="sticky top-0 z-30 flex items-center gap-x-4 bg-white px-4 py-3 shadow-xs sm:px-6 lg:hidden">
        <button type="button" onClick={() => setIsMenuOpen(true)} className="-m-2.5 rounded-md p-2.5 text-gray-700">
          <span className="sr-only">Abrir menú</span>
          <Bars3Icon aria-hidden="true" className="size-6" />
        </button>
        <Brand />
      </header>

      <main id="contenido" tabIndex={-1} className="py-8 outline-none sm:py-10 lg:pl-72">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Outlet />
        </div>
      </main>
    </div>
  )
}
