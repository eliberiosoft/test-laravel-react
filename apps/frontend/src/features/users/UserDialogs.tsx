import type { User } from '@/core/types'
import { useUserMutations } from '@/services/users.service'
import { USER_STATUS } from '@/shared/domain'
import { useForm, type FieldBinding } from '@/shared/hooks'
import { Button } from '@/shared/ui/Button'
import { ConfirmDialog, Dialog, type DialogTone } from '@/shared/ui/Dialog'
import { Alert } from '@/shared/ui/Feedback'
import { SelectField, TextField } from '@/shared/ui/Form'
import { PASSWORD_HINT, createValidator, fieldRules } from '@/shared/validation'
import { PERSON_NAME_FIELDS, personSchema, personValues, today, type PersonValues, type UserAction } from './users.logic'

interface UserFormValues extends PersonValues {
  identification: string
  username: string
  password: string
  status: string
}

const accountSchema = { ...personSchema, identification: fieldRules.identification, username: fieldRules.username }
const validateCreate = createValidator<UserFormValues>({ ...accountSchema, password: fieldRules.password })
const validateEdit = createValidator<UserFormValues>(accountSchema)

const STATUS_OPTIONS = (['active', 'inactive'] as const).map((value) => ({ value, label: USER_STATUS[value].label }))

export function PersonFields({ field }: { field: (name: string) => FieldBinding }) {
  return (
    <>
      {PERSON_NAME_FIELDS.map(({ name, label, autoComplete, optional }) => (
        <TextField key={name} label={label} autoComplete={autoComplete} optional={optional} {...field(name)} />
      ))}
      <TextField label="Fecha de nacimiento" type="date" max={today()} optional {...field('birth_date')} />
    </>
  )
}

interface UserFormDialogProps {
  user?: User
  onClose: () => void
  onSaved: (message: string) => void
}

export function UserFormDialog({ user, onClose, onSaved }: UserFormDialogProps) {
  const { create, update } = useUserMutations()
  const canChangeStatus = user !== undefined && user.status !== 'blocked'

  const form = useForm<UserFormValues>({
    initialValues: {
      ...personValues(user),
      identification: user?.person?.identification ?? '',
      username: user?.username ?? '',
      password: '',
      status: user?.status ?? 'active',
    },
    validate: user ? validateEdit : validateCreate,
    onSubmit: async ({ password, status, ...values }) => {
      if (!user) {
        const created = await create.mutateAsync({ ...values, password })
        onSaved(`Usuario creado. Su correo es ${created.email}.`)
        return
      }

      await update.mutateAsync({ id: user.id, payload: canChangeStatus ? { ...values, status } : values })
      onSaved(`Los datos de ${user.person?.full_name ?? user.username} se guardaron.`)
    },
  })

  return (
    <Dialog
      open
      onClose={onClose}
      size="lg"
      title={user ? `Editar a ${user.person?.full_name ?? user.username}` : 'Nuevo usuario'}
      description={
        user
          ? 'El correo generado no cambia aunque edites los nombres.'
          : 'El correo se genera con la inicial del primer nombre, el primer apellido y la inicial del segundo apellido.'
      }
      onSubmit={form.handleSubmit}
      actions={
        <>
          <Button variant="secondary" onClick={onClose} disabled={form.isSubmitting}>
            Cancelar
          </Button>
          <Button type="submit" isLoading={form.isSubmitting}>
            {user ? 'Guardar cambios' : 'Crear usuario'}
          </Button>
        </>
      }
    >
      {form.formError && (
        <Alert tone="error" className="mb-6">
          {form.formError}
        </Alert>
      )}
      <div className="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
        <PersonFields field={form.field} />
        <TextField label="Identificación" inputMode="numeric" maxLength={10} hint="10 dígitos, sin cuatro iguales seguidos." {...form.field('identification')} />
        <TextField label="Usuario" autoComplete="off" hint="8 a 20 caracteres, con una mayúscula y un número." {...form.field('username')} />
        {!user && <TextField label="Contraseña" type="password" autoComplete="new-password" hint={PASSWORD_HINT} {...form.field('password')} />}
        {user && canChangeStatus && <SelectField label="Estado" options={STATUS_OPTIONS} {...form.field('status')} />}
        {user && !canChangeStatus && (
          <Alert tone="warning" className="sm:col-span-2">
            La cuenta está bloqueada. Desbloquéala para poder cambiar su estado.
          </Alert>
        )}
      </div>
    </Dialog>
  )
}

export type ConfirmableAction = Exclude<UserAction, 'edit'>

interface Confirmation {
  title: (name: string) => string
  description: string
  confirmLabel: string
  tone: DialogTone
  success: (name: string) => string
}

const CONFIRMATIONS: Record<ConfirmableAction, Confirmation> = {
  unlock: {
    title: (name) => `Desbloquear a ${name}`,
    description: 'Podrá volver a iniciar sesión y su contador de intentos fallidos vuelve a cero.',
    confirmLabel: 'Desbloquear',
    tone: 'primary',
    success: (name) => `${name} fue desbloqueado.`,
  },
  revoke: {
    title: (name) => `Cerrar las sesiones de ${name}`,
    description: 'Su sesión activa se cierra de inmediato y tendrá que iniciar sesión otra vez.',
    confirmLabel: 'Cerrar sesiones',
    tone: 'primary',
    success: (name) => `Se cerraron las sesiones de ${name}.`,
  },
  delete: {
    title: (name) => `Eliminar a ${name}`,
    description: 'La cuenta deja de estar disponible y sus sesiones se cierran. Su identificación y su correo quedan reservados.',
    confirmLabel: 'Eliminar',
    tone: 'danger',
    success: (name) => `${name} fue eliminado.`,
  },
}

interface UserActionDialogProps {
  action: ConfirmableAction
  user: User
  onClose: () => void
  onDone: (message: string) => void
}

export function UserActionDialog({ action, user, onClose, onDone }: UserActionDialogProps) {
  const mutations = useUserMutations()
  const mutation = { unlock: mutations.unlock, revoke: mutations.revokeSessions, delete: mutations.remove }[action]
  const confirmation = CONFIRMATIONS[action]
  const name = user.person?.full_name ?? user.username

  const confirm = () => {
    mutation
      .mutateAsync(user)
      .then(() => onDone(confirmation.success(name)))
      .catch(() => undefined)
  }

  return (
    <ConfirmDialog
      open
      onClose={onClose}
      onConfirm={confirm}
      title={confirmation.title(name)}
      description={confirmation.description}
      confirmLabel={confirmation.confirmLabel}
      tone={confirmation.tone}
      isPending={mutation.isPending}
      error={mutation.error?.message}
    />
  )
}
