import type { ReactNode } from 'react'
import type { UseQueryResult } from '@tanstack/react-query'
import { CheckCircleIcon, ExclamationTriangleIcon, InformationCircleIcon, XCircleIcon } from '@heroicons/react/20/solid'
import type { BadgeTone, IconComponent, StatusStyle } from '@/core/types'
import { cx } from '@/shared/utils'
import { Button } from './Button'
import { BADGE_TONES, cardClasses } from './styles'

type AlertTone = 'error' | 'warning' | 'success' | 'info'

const ALERT_TONES: Record<AlertTone, { icon: IconComponent; box: string; iconColor: string; title: string; body: string }> = {
  error: { icon: XCircleIcon, box: 'bg-red-50', iconColor: 'text-red-400', title: 'text-red-800', body: 'text-red-700' },
  warning: { icon: ExclamationTriangleIcon, box: 'bg-amber-50', iconColor: 'text-amber-400', title: 'text-amber-800', body: 'text-amber-700' },
  success: { icon: CheckCircleIcon, box: 'bg-green-50', iconColor: 'text-green-400', title: 'text-green-800', body: 'text-green-700' },
  info: { icon: InformationCircleIcon, box: 'bg-sky-50', iconColor: 'text-sky-400', title: 'text-sky-800', body: 'text-sky-700' },
}

interface AlertProps {
  tone?: AlertTone
  title?: string
  action?: ReactNode
  className?: string
  children?: ReactNode
}

export function Alert({ tone = 'info', title, action, className, children }: AlertProps) {
  const style = ALERT_TONES[tone]
  const Icon = style.icon

  return (
    <div role={tone === 'error' ? 'alert' : 'status'} className={cx('rounded-md p-4', style.box, className)}>
      <div className="flex gap-3">
        <Icon aria-hidden="true" className={cx('size-5 shrink-0', style.iconColor)} />
        <div className="min-w-0 flex-1 text-sm">
          {title && <h3 className={cx('font-medium', style.title)}>{title}</h3>}
          {children && <div className={cx(style.body, title && 'mt-1')}>{children}</div>}
          {action && <div className="mt-3">{action}</div>}
        </div>
      </div>
    </div>
  )
}

export function Badge({ tone = 'gray', children }: { tone?: BadgeTone; children: ReactNode }) {
  return (
    <span className={cx('inline-flex items-center rounded-md px-2 py-1 text-xs font-medium whitespace-nowrap ring-1 ring-inset', BADGE_TONES[tone])}>
      {children}
    </span>
  )
}

export function StatusBadge<S extends string>({ statuses, value }: { statuses: Record<S, StatusStyle>; value: S }) {
  const { label, tone } = statuses[value]

  return <Badge tone={tone}>{label}</Badge>
}

interface EmptyStateProps {
  icon?: IconComponent
  title: string
  description?: ReactNode
  action?: ReactNode
}

export function EmptyState({ icon: Icon, title, description, action }: EmptyStateProps) {
  return (
    <div className="px-6 py-12 text-center">
      {Icon && <Icon aria-hidden="true" className="mx-auto size-10 text-gray-400" />}
      <h3 className="mt-2 text-sm font-semibold text-gray-900">{title}</h3>
      {description && <p className="mx-auto mt-1 max-w-md text-sm text-gray-500">{description}</p>}
      {action && <div className="mt-6">{action}</div>}
    </div>
  )
}

export function Skeleton({ className }: { className?: string }) {
  return <div aria-hidden="true" className={cx('animate-pulse rounded-md bg-gray-100', className)} />
}

export function PanelSkeleton({ lines = 4 }: { lines?: number }) {
  return (
    <div role="status" aria-label="Cargando" className={cx('space-y-4 p-6', cardClasses)}>
      <Skeleton className="h-5 w-1/3" />
      {Array.from({ length: lines }, (_, index) => (
        <Skeleton key={index} className="h-4 w-full" />
      ))}
    </div>
  )
}

export function QueryError({ error, onRetry }: { error: Error; onRetry: () => void }) {
  return (
    <Alert
      tone="error"
      title="No se pudo cargar la información"
      action={
        <Button variant="secondary" size="sm" onClick={onRetry}>
          Reintentar
        </Button>
      }
    >
      {error.message}
    </Alert>
  )
}

interface QueryStateProps<T> {
  query: UseQueryResult<T>
  fallback?: ReactNode
  children: (data: T) => ReactNode
}

export function QueryState<T>({ query, fallback = <PanelSkeleton />, children }: QueryStateProps<T>) {
  if (query.isPending) return fallback
  if (query.isError) return <QueryError error={query.error} onRetry={() => query.refetch()} />

  return children(query.data)
}
