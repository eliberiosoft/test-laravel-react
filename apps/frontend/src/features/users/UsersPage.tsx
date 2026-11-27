import { useState } from 'react'
import { PlusIcon } from '@heroicons/react/20/solid'
import { UsersIcon } from '@heroicons/react/24/outline'
import { useAuth } from '@/core/auth'
import type { User } from '@/core/types'
import { useUserList } from '@/services/users.service'
import { PERMISSIONS } from '@/shared/domain'
import { Button } from '@/shared/ui/Button'
import { Alert, EmptyState, QueryError } from '@/shared/ui/Feedback'
import { PageHeader } from '@/shared/ui/Layout'
import { UserActionDialog, UserFormDialog, type ConfirmableAction } from './UserDialogs'
import { UserFilters } from './UserFilters'
import { UsersTable } from './UsersTable'
import { availableActions, toUserQuery, useUserListParams, type UserAction } from './users.logic'

type DialogState = { type: 'create' } | { type: 'edit'; user: User } | { type: ConfirmableAction; user: User } | null

export function UsersPage() {
  const { user: currentUser, can } = useAuth()
  const { params, update, hasFilters } = useUserListParams()
  const users = useUserList(toUserQuery(params))
  const [dialog, setDialog] = useState<DialogState>(null)
  const [notice, setNotice] = useState<string | null>(null)

  const closeDialog = () => setDialog(null)

  const finish = (message: string) => {
    setNotice(message)
    closeDialog()
  }

  const openAction = (action: UserAction, user: User) => {
    setNotice(null)
    setDialog(action === 'edit' ? { type: 'edit', user } : { type: action, user })
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title="Usuarios"
        description="Administra las cuentas de los usuarios comunes. Las cuentas de otros administradores no se pueden modificar."
        actions={
          can(PERMISSIONS.usersCreate) && (
            <Button onClick={() => setDialog({ type: 'create' })}>
              <PlusIcon aria-hidden="true" className="-ml-0.5 size-5" />
              Nuevo usuario
            </Button>
          )
        }
      />

      {notice && <Alert tone="success">{notice}</Alert>}

      <UserFilters params={params} hasFilters={hasFilters} onChange={update} />

      {users.isError ? (
        <QueryError error={users.error} onRetry={() => users.refetch()} />
      ) : (
        <UsersTable
          data={users.data}
          isLoading={users.isFetching}
          sort={params.sort}
          onSortChange={(sort) => update({ sort })}
          onPageChange={(page) => update({ page })}
          actionsFor={(user) => availableActions(user, currentUser, can)}
          onAction={openAction}
          empty={
            <EmptyState
              icon={UsersIcon}
              title={hasFilters ? 'Ningún usuario coincide con los filtros' : 'Todavía no hay usuarios'}
              description={hasFilters ? 'Prueba con otra búsqueda o limpia los filtros.' : 'Crea el primero o súbelos desde un archivo en Carga masiva.'}
            />
          }
        />
      )}

      {dialog?.type === 'create' && <UserFormDialog onClose={closeDialog} onSaved={finish} />}
      {dialog?.type === 'edit' && <UserFormDialog user={dialog.user} onClose={closeDialog} onSaved={finish} />}
      {dialog && dialog.type !== 'create' && dialog.type !== 'edit' && (
        <UserActionDialog action={dialog.type} user={dialog.user} onClose={closeDialog} onDone={finish} />
      )}
    </div>
  )
}
