import type { ReactNode } from 'react'
import type { Paginated, User } from '@/core/types'
import { USER_STATUS, roleNames } from '@/shared/domain'
import { DataTable, Pagination, type Column } from '@/shared/ui/DataTable'
import { StatusBadge } from '@/shared/ui/Feedback'
import { buttonClasses } from '@/shared/ui/styles'
import type { UserAction } from './users.logic'

const ACTION_LABELS: Record<UserAction, string> = {
  edit: 'Editar',
  unlock: 'Desbloquear',
  revoke: 'Cerrar sesiones',
  delete: 'Eliminar',
}

const WIDE_ONLY = 'hidden xl:table-cell'
const TABLET_UP = 'hidden sm:table-cell'

interface UsersTableProps {
  data: Paginated<User> | undefined
  isLoading: boolean
  sort: string
  onSortChange: (sort: string) => void
  onPageChange: (page: number) => void
  actionsFor: (user: User) => UserAction[]
  onAction: (action: UserAction, user: User) => void
  empty: ReactNode
}

export function UsersTable({ data, isLoading, sort, onSortChange, onPageChange, actionsFor, onAction, empty }: UsersTableProps) {
  const columns: Column<User>[] = [
    {
      key: 'name',
      header: 'Nombre',
      cell: (user) => (
        <>
          <div className="font-medium text-gray-900">{user.person?.full_name}</div>
          <div className="mt-1 tabular-nums">{user.person?.identification}</div>
          <dl className="mt-1 space-y-0.5 xl:hidden">
            <dt className="sr-only">Usuario y rol</dt>
            <dd>
              {user.username} · {roleNames(user.roles)}
            </dd>
            <dt className="sr-only">Correo</dt>
            <dd className="break-words">{user.email}</dd>
            <dt className="sr-only sm:hidden">Estado</dt>
            <dd className="pt-1 sm:hidden">
              <StatusBadge statuses={USER_STATUS} value={user.status} />
            </dd>
          </dl>
        </>
      ),
    },
    { key: 'username', header: 'Usuario', sortKey: 'username', cell: (user) => user.username, className: `${WIDE_ONLY} whitespace-nowrap`, headerClassName: WIDE_ONLY },
    { key: 'email', header: 'Correo', sortKey: 'email', cell: (user) => user.email, className: WIDE_ONLY, headerClassName: WIDE_ONLY },
    { key: 'role', header: 'Rol', cell: (user) => roleNames(user.roles), className: `${WIDE_ONLY} whitespace-nowrap`, headerClassName: WIDE_ONLY },
    {
      key: 'status',
      header: 'Estado',
      sortKey: 'status',
      cell: (user) => <StatusBadge statuses={USER_STATUS} value={user.status} />,
      className: TABLET_UP,
      headerClassName: TABLET_UP,
    },
    {
      key: 'actions',
      header: <span className="sr-only">Acciones</span>,
      className: 'text-right',
      cell: (user) => {
        const actions = actionsFor(user)

        if (actions.length === 0) return <span className="sr-only">Sin acciones disponibles</span>

        return (
          <div className="flex flex-wrap justify-end gap-x-4 gap-y-1">
            {actions.map((action) => (
              <button
                key={action}
                type="button"
                onClick={() => onAction(action, user)}
                className={buttonClasses({ variant: 'link', className: action === 'delete' ? 'text-red-600 hover:text-red-500' : undefined })}
              >
                {ACTION_LABELS[action]}
                <span className="sr-only">, {user.person?.full_name ?? user.username}</span>
              </button>
            ))}
          </div>
        )
      },
    },
  ]

  return (
    <DataTable
      caption="Usuarios registrados"
      columns={columns}
      rows={data?.data}
      rowKey={(user) => user.id}
      isLoading={isLoading}
      empty={empty}
      sort={sort}
      onSortChange={onSortChange}
      footer={<Pagination meta={data?.meta} onPageChange={onPageChange} />}
    />
  )
}
