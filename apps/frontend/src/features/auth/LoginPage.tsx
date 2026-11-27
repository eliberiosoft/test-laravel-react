import { Link, useLocation } from 'react-router'
import { useAuth, type RedirectState } from '@/core/auth'
import { AuthHeading } from '@/layout/AuthLayout'
import { useForm } from '@/shared/hooks'
import { Button } from '@/shared/ui/Button'
import { Alert } from '@/shared/ui/Feedback'
import { TextField } from '@/shared/ui/Form'
import { linkClasses } from '@/shared/ui/styles'
import { createValidator, rules } from '@/shared/validation'

const validate = createValidator<{ login: string; password: string }>({
  login: [rules.required('Ingresa tu usuario o correo.')],
  password: [rules.required('Ingresa tu contraseña.')],
})

export function LoginPage() {
  const { login, notice } = useAuth()
  const state = useLocation().state as RedirectState | null
  const form = useForm({ initialValues: { login: '', password: '' }, validate, onSubmit: login })
  const message = state?.message ?? notice

  return (
    <>
      <AuthHeading title="Inicia sesión en tu cuenta" />
      <form onSubmit={form.handleSubmit} noValidate className="space-y-6">
        {message && <Alert tone="info">{message}</Alert>}
        {form.formError && <Alert tone="error">{form.formError}</Alert>}
        <TextField label="Usuario o correo" autoComplete="username" autoFocus {...form.field('login')} />
        <TextField
          label="Contraseña"
          type="password"
          autoComplete="current-password"
          labelAction={
            <Link to="/forgot-password" className={linkClasses}>
              ¿Olvidaste tu contraseña?
            </Link>
          }
          {...form.field('password')}
        />
        <Button type="submit" isLoading={form.isSubmitting} className="w-full">
          Iniciar sesión
        </Button>
      </form>
    </>
  )
}
