import { useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router'
import { useAuth, type RedirectState } from '@/core/auth'
import { AuthHeading } from '@/layout/AuthLayout'
import { useForm } from '@/shared/hooks'
import { Button } from '@/shared/ui/Button'
import { Alert } from '@/shared/ui/Feedback'
import { TextField } from '@/shared/ui/Form'
import { linkClasses } from '@/shared/ui/styles'
import { PASSWORD_HINT, createValidator, fieldRules, rules } from '@/shared/validation'

const RESET_SUCCESS: RedirectState = { message: 'Tu contraseña fue restablecida. Inicia sesión con la nueva contraseña.' }

const validateEmail = createValidator<{ email: string }>({ email: fieldRules.email })

const validateNewPassword = createValidator<{ password: string; password_confirmation: string }>({
  password: fieldRules.password,
  password_confirmation: [rules.required('Confirma la nueva contraseña.'), rules.sameAs('password', 'Las contraseñas no coinciden.')],
})

function BackToLogin() {
  return (
    <p className="mt-10 text-center text-sm text-gray-500">
      <Link to="/login" className={linkClasses}>
        Volver a iniciar sesión
      </Link>
    </p>
  )
}

function RequestNewLink({ children }: { children: string }) {
  return (
    <Link to="/forgot-password" className="font-semibold underline">
      {children}
    </Link>
  )
}

export function ForgotPasswordPage() {
  const { forgotPassword } = useAuth()
  const [confirmation, setConfirmation] = useState<string | null>(null)

  const form = useForm({
    initialValues: { email: '' },
    validate: validateEmail,
    onSubmit: async ({ email }) => setConfirmation(await forgotPassword(email)),
  })

  return (
    <>
      <AuthHeading title="Recupera tu contraseña" description="Te enviaremos un enlace para crear una nueva contraseña." />
      {confirmation ? (
        <Alert tone="success" title="Revisa tu correo">
          {confirmation} El enlace vence en 60 minutos.
        </Alert>
      ) : (
        <form onSubmit={form.handleSubmit} noValidate className="space-y-6">
          {form.formError && <Alert tone="error">{form.formError}</Alert>}
          <TextField label="Correo electrónico" type="email" autoComplete="email" autoFocus {...form.field('email')} />
          <Button type="submit" isLoading={form.isSubmitting} className="w-full">
            Enviar enlace
          </Button>
        </form>
      )}
      <BackToLogin />
    </>
  )
}

export function ResetPasswordPage() {
  const { resetPassword } = useAuth()
  const [params] = useSearchParams()
  const navigate = useNavigate()
  const token = params.get('token') ?? ''
  const email = params.get('email') ?? ''

  const form = useForm({
    initialValues: { password: '', password_confirmation: '' },
    validate: validateNewPassword,
    onSubmit: async (values) => {
      await resetPassword({ token, email, ...values })
      navigate('/login', { replace: true, state: RESET_SUCCESS })
    },
  })

  if (!token || !email) {
    return (
      <>
        <AuthHeading title="Enlace incompleto" />
        <Alert tone="error">
          El enlace de recuperación no es válido. <RequestNewLink>Solicita uno nuevo</RequestNewLink>.
        </Alert>
      </>
    )
  }

  return (
    <>
      <AuthHeading title="Crea una nueva contraseña" description={email} />
      <form onSubmit={form.handleSubmit} noValidate className="space-y-6">
        {form.formError && (
          <Alert tone="error">
            {form.formError} <RequestNewLink>Solicitar otro enlace</RequestNewLink>
          </Alert>
        )}
        <TextField label="Nueva contraseña" type="password" autoComplete="new-password" hint={PASSWORD_HINT} autoFocus {...form.field('password')} />
        <TextField label="Confirmar contraseña" type="password" autoComplete="new-password" {...form.field('password_confirmation')} />
        <Button type="submit" isLoading={form.isSubmitting} className="w-full">
          Guardar contraseña
        </Button>
      </form>
      <BackToLogin />
    </>
  )
}
