import { useState } from 'react'
import { useAuth } from '@/core/auth'
import type { User } from '@/core/types'
import { useUpdateProfile } from '@/services/account.service'
import { PERMISSIONS, USER_STATUS, roleNames } from '@/shared/domain'
import { useForm } from '@/shared/hooks'
import { Button } from '@/shared/ui/Button'
import { Alert, StatusBadge } from '@/shared/ui/Feedback'
import { TextField } from '@/shared/ui/Form'
import { DescriptionList, PageHeader, Panel } from '@/shared/ui/Layout'
import { formatDate } from '@/shared/utils'
import { PASSWORD_HINT, createValidator, fieldRules, rules } from '@/shared/validation'
import { PersonFields } from '../users/UserDialogs'
import { personSchema, personValues, type PersonValues } from '../users/users.logic'

const EMPTY_PASSWORD = { current_password: '', password: '', password_confirmation: '' }

const validateProfile = createValidator<PersonValues>(personSchema)

const validatePassword = createValidator<typeof EMPTY_PASSWORD>({
  current_password: [rules.required('Ingresa tu contraseña actual.')],
  password: fieldRules.password,
  password_confirmation: [rules.required('Confirma la nueva contraseña.'), rules.sameAs('password', 'Las contraseñas no coinciden.')],
})

function FormFooter({ isSubmitting, label }: { isSubmitting: boolean; label: string }) {
  return (
    <div className="flex justify-end border-t border-gray-200 pt-6">
      <Button type="submit" isLoading={isSubmitting}>
        {label}
      </Button>
    </div>
  )
}

function ProfileForm({ user }: { user: User }) {
  const updateProfile = useUpdateProfile()
  const [saved, setSaved] = useState(false)

  const form = useForm({
    initialValues: personValues(user),
    validate: validateProfile,
    onSubmit: async (values) => {
      setSaved(false)
      const updated = await updateProfile.mutateAsync(values)
      form.reset(personValues(updated))
      setSaved(true)
    },
  })

  return (
    <Panel title="Datos personales" description="Tu nombre completo se actualiza en todo el sistema. El correo y el usuario no cambian.">
      <form onSubmit={form.handleSubmit} noValidate className="space-y-6">
        {saved && <Alert tone="success">Tus datos se guardaron.</Alert>}
        {form.formError && <Alert tone="error">{form.formError}</Alert>}
        <div className="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
          <PersonFields field={form.field} />
        </div>
        <FormFooter isSubmitting={form.isSubmitting} label="Guardar datos" />
      </form>
    </Panel>
  )
}

function PasswordForm() {
  const updateProfile = useUpdateProfile()
  const [saved, setSaved] = useState(false)

  const form = useForm({
    initialValues: EMPTY_PASSWORD,
    validate: validatePassword,
    onSubmit: async (values) => {
      setSaved(false)
      await updateProfile.mutateAsync(values)
      form.reset()
      setSaved(true)
    },
  })

  return (
    <Panel title="Contraseña" description={`${PASSWORD_HINT} Necesitas tu contraseña actual.`}>
      <form onSubmit={form.handleSubmit} noValidate className="space-y-6">
        {saved && <Alert tone="success">Tu contraseña se actualizó.</Alert>}
        {form.formError && <Alert tone="error">{form.formError}</Alert>}
        <div className="grid grid-cols-1 gap-y-5">
          <TextField label="Contraseña actual" type="password" autoComplete="current-password" {...form.field('current_password')} />
          <TextField label="Nueva contraseña" type="password" autoComplete="new-password" {...form.field('password')} />
          <TextField label="Confirmar nueva contraseña" type="password" autoComplete="new-password" {...form.field('password_confirmation')} />
        </div>
        <FormFooter isSubmitting={form.isSubmitting} label="Cambiar contraseña" />
      </form>
    </Panel>
  )
}

export function ProfilePage() {
  const { user, can } = useAuth()

  if (!user) return null

  return (
    <div className="space-y-8">
      <PageHeader title="Mi perfil" description="Consulta los datos de tu cuenta y mantén tu información al día." />
      <div className="grid items-start gap-6 lg:grid-cols-5">
        <Panel title="Cuenta" className="lg:col-span-2">
          <DescriptionList
            items={[
              { label: 'Usuario', value: user.username },
              { label: 'Correo', value: user.email },
              { label: 'Identificación', value: <span className="tabular-nums">{user.person?.identification}</span> },
              { label: 'Nacimiento', value: formatDate(user.person?.birth_date) },
              { label: 'Rol', value: roleNames(user.roles) },
              { label: 'Estado', value: <StatusBadge statuses={USER_STATUS} value={user.status} /> },
            ]}
          />
        </Panel>
        {can(PERMISSIONS.profileUpdate) && (
          <div className="space-y-6 lg:col-span-3">
            <ProfileForm key={user.id} user={user} />
            <PasswordForm />
          </div>
        )}
      </div>
    </div>
  )
}
