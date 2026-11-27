import type { ReactNode } from 'react'
import { ShieldCheckIcon } from '@heroicons/react/24/solid'
import { cx, formatNumber } from '@/shared/utils'
import { cardClasses } from './styles'

const STAT_COLUMNS: Record<number, string> = {
  1: '',
  2: 'sm:grid-cols-2',
  3: 'sm:grid-cols-3',
  4: 'sm:grid-cols-2 lg:grid-cols-4',
}

export function Brand({ centered = false }: { centered?: boolean }) {
  return (
    <span className={cx('flex items-center gap-2.5', centered && 'justify-center')}>
      <ShieldCheckIcon aria-hidden="true" className="size-8 text-indigo-600" />
      <span className="text-sm font-semibold tracking-tight text-gray-900">Gestión de usuarios</span>
    </span>
  )
}

export function PageHeader({ title, description, actions }: { title: string; description?: ReactNode; actions?: ReactNode }) {
  return (
    <div className="sm:flex sm:items-end sm:justify-between sm:gap-6">
      <div className="min-w-0">
        <h1 className="text-2xl/8 font-semibold tracking-tight text-balance text-gray-900">{title}</h1>
        {description && <p className="mt-2 max-w-2xl text-sm text-gray-600">{description}</p>}
      </div>
      {actions && <div className="mt-4 flex shrink-0 flex-wrap gap-3 sm:mt-0">{actions}</div>}
    </div>
  )
}

interface PanelProps {
  title?: string
  description?: ReactNode
  actions?: ReactNode
  className?: string
  children: ReactNode
}

export function Panel({ title, description, actions, className, children }: PanelProps) {
  return (
    <section className={cx(cardClasses, className)}>
      {title && (
        <header className="flex flex-wrap items-start justify-between gap-3 border-b border-gray-200 px-4 py-5 sm:px-6">
          <div>
            <h2 className="text-base/7 font-semibold text-gray-900">{title}</h2>
            {description && <p className="mt-1 text-sm text-gray-500">{description}</p>}
          </div>
          {actions}
        </header>
      )}
      <div className="px-4 py-5 sm:p-6">{children}</div>
    </section>
  )
}

export interface DescriptionItem {
  label: string
  value: ReactNode
}

export function DescriptionList({ items }: { items: DescriptionItem[] }) {
  return (
    <dl className="-my-3 divide-y divide-gray-100">
      {items.map(({ label, value }) => (
        <div key={label} className="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
          <dt className="text-sm/6 font-medium text-gray-900">{label}</dt>
          <dd className="mt-1 text-sm/6 break-words text-gray-700 sm:col-span-2 sm:mt-0">{value ?? '—'}</dd>
        </div>
      ))}
    </dl>
  )
}

export interface Stat {
  label: string
  value: number
  hint?: string
}

export function StatGroup({ title, stats, children }: { title: string; stats: Stat[]; children?: ReactNode }) {
  return (
    <section aria-label={title}>
      <h2 className="text-base/7 font-semibold text-gray-900">{title}</h2>
      <dl
        className={cx(
          'mt-3 grid grid-cols-1 gap-px overflow-hidden rounded-lg bg-gray-900/5 shadow-xs ring-1 ring-gray-900/5',
          STAT_COLUMNS[stats.length] ?? STAT_COLUMNS[4],
        )}
      >
        {stats.map(({ label, value, hint }) => (
          <div key={label} className="bg-white px-4 py-5 sm:px-6">
            <dt className="text-sm/6 font-medium text-gray-500">{label}</dt>
            <dd className="mt-1 text-3xl font-semibold tracking-tight text-gray-900 tabular-nums">{formatNumber(value)}</dd>
            {hint && <p className="mt-1 text-xs text-gray-500">{hint}</p>}
          </div>
        ))}
      </dl>
      {children}
    </section>
  )
}
