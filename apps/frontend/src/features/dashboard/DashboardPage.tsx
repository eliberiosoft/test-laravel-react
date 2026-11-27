import { ArrowPathIcon } from '@heroicons/react/20/solid'
import type { DashboardMetrics, UserStatus } from '@/core/types'
import { useDashboard } from '@/services/dashboard.service'
import { USER_STATUS } from '@/shared/domain'
import { Button } from '@/shared/ui/Button'
import { PanelSkeleton, QueryState } from '@/shared/ui/Feedback'
import { PageHeader, StatGroup } from '@/shared/ui/Layout'
import { cx, formatNumber } from '@/shared/utils'

const STATUS_BAR_COLORS: Record<UserStatus, string> = {
  active: 'bg-green-500',
  blocked: 'bg-red-500',
  inactive: 'bg-gray-400',
}

const STATUSES = Object.keys(STATUS_BAR_COLORS) as UserStatus[]

function AccountDistribution({ accounts }: { accounts: DashboardMetrics['accounts'] }) {
  if (accounts.total === 0) return null

  return (
    <div className="mt-4">
      <div className="flex h-2 overflow-hidden rounded-full bg-gray-100" aria-hidden="true">
        {STATUSES.map((status) => (
          <div key={status} className={STATUS_BAR_COLORS[status]} style={{ width: `${(accounts[status] / accounts.total) * 100}%` }} />
        ))}
      </div>
      <ul className="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-600">
        {STATUSES.map((status) => (
          <li key={status} className="flex items-center gap-2">
            <span aria-hidden="true" className={cx('size-2 rounded-full', STATUS_BAR_COLORS[status])} />
            {USER_STATUS[status].label}: <span className="font-medium text-gray-900 tabular-nums">{formatNumber(accounts[status])}</span>
          </li>
        ))}
      </ul>
    </div>
  )
}

export function DashboardPage() {
  const dashboard = useDashboard()

  return (
    <div className="space-y-10">
      <PageHeader
        title="Dashboard"
        description="Indicadores de cuentas, sesiones e intentos fallidos calculados en el servidor."
        actions={
          <Button variant="secondary" onClick={() => dashboard.refetch()} isLoading={dashboard.isFetching}>
            {!dashboard.isFetching && <ArrowPathIcon aria-hidden="true" className="-ml-0.5 size-5 text-gray-400" />}
            Actualizar
          </Button>
        }
      />
      <QueryState query={dashboard} fallback={<PanelSkeleton lines={6} />}>
        {({ accounts, sessions, failed_attempts: failures }) => (
          <>
            <StatGroup
              title="Cuentas"
              stats={[
                { label: 'Total', value: accounts.total },
                { label: 'Activas', value: accounts.active },
                { label: 'Bloqueadas', value: accounts.blocked, hint: 'Requieren desbloqueo de un administrador' },
                { label: 'Inactivas', value: accounts.inactive },
              ]}
            >
              <AccountDistribution accounts={accounts} />
            </StatGroup>
            <StatGroup
              title="Sesiones"
              stats={[
                { label: 'Activas ahora', value: sessions.active },
                { label: 'Iniciadas en 24 horas', value: sessions.started_last_24_hours },
              ]}
            />
            <StatGroup
              title="Intentos fallidos"
              stats={[
                { label: 'Últimas 24 horas', value: failures.last_24_hours },
                { label: 'Últimos 7 días', value: failures.last_7_days },
                { label: 'Con cuentas inexistentes (7 días)', value: failures.unknown_accounts_last_7_days },
              ]}
            />
          </>
        )}
      </QueryState>
    </div>
  )
}
