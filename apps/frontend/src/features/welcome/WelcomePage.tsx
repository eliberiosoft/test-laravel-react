import { Link } from 'react-router'
import type { WelcomeSummary } from '@/core/types'
import { useWelcome } from '@/services/account.service'
import { SESSION_END_REASONS, USER_STATUS, roleNames } from '@/shared/domain'
import { Alert, PanelSkeleton, QueryState, StatusBadge } from '@/shared/ui/Feedback'
import { DescriptionList, PageHeader, Panel } from '@/shared/ui/Layout'
import { formatDateTime } from '@/shared/utils'

function AccountPanel({ summary }: { summary: WelcomeSummary }) {
  const { user } = summary

  return (
    <Panel title="Tus datos">
      <DescriptionList
        items={[
          { label: 'Nombre', value: user.person?.full_name },
          { label: 'Usuario', value: user.username },
          { label: 'Correo', value: user.email },
          { label: 'Identificación', value: <span className="tabular-nums">{user.person?.identification}</span> },
          { label: 'Rol', value: roleNames(user.roles) },
          { label: 'Estado', value: <StatusBadge statuses={USER_STATUS} value={user.status} /> },
        ]}
      />
    </Panel>
  )
}

function sessionEnd(session: WelcomeSummary['last_session']): string {
  if (!session) return '—'
  if (!session.ended_at) return 'Sin cierre registrado'

  return [formatDateTime(session.ended_at), session.end_reason && SESSION_END_REASONS[session.end_reason]].filter(Boolean).join(' · ')
}

function ActivityPanel({ summary }: { summary: WelcomeSummary }) {
  const session = summary.last_session
  const failures = summary.failed_attempts

  return (
    <Panel title="Actividad de acceso">
      <DescriptionList
        items={[
          { label: 'Última sesión', value: session ? formatDateTime(session.started_at) : 'Esta es tu primera sesión.' },
          { label: 'Cierre', value: sessionEnd(session) },
          { label: 'Dirección IP', value: <span className="tabular-nums">{session?.ip_address ?? '—'}</span> },
          {
            label: 'Intentos fallidos',
            value: (
              <span className="tabular-nums">
                {failures.since_last_session} desde tu última sesión
                {failures.last_attempt_at && <span className="block text-gray-500">Último: {formatDateTime(failures.last_attempt_at)}</span>}
              </span>
            ),
          },
        ]}
      />
      {failures.since_last_session > 0 && (
        <Alert tone="warning" className="mt-6">
          Hubo intentos fallidos de acceso a tu cuenta. Si no fuiste tú,{' '}
          <Link to="/profile" className="font-semibold underline">
            cambia tu contraseña
          </Link>
          .
        </Alert>
      )}
    </Panel>
  )
}

export function WelcomePage() {
  const welcome = useWelcome()
  const firstName = welcome.data?.user.person?.first_name

  return (
    <div className="space-y-8">
      <PageHeader title={firstName ? `Hola, ${firstName}` : 'Bienvenida'} description="Este es el estado de tu cuenta y de tus accesos recientes." />
      <QueryState
        query={welcome}
        fallback={
          <div className="grid gap-6 lg:grid-cols-2">
            <PanelSkeleton lines={6} />
            <PanelSkeleton lines={4} />
          </div>
        }
      >
        {(summary) => (
          <div className="grid items-start gap-6 lg:grid-cols-2">
            <AccountPanel summary={summary} />
            <ActivityPanel summary={summary} />
          </div>
        )}
      </QueryState>
    </div>
  )
}
