import type { ReactNode } from 'react'
import { ChevronDownIcon, ChevronLeftIcon, ChevronRightIcon, ChevronUpDownIcon, ChevronUpIcon } from '@heroicons/react/20/solid'
import type { PaginationMeta } from '@/core/types'
import { PAGE_GAP, cx, formatNumber, pageWindow } from '@/shared/utils'
import { Skeleton } from './Feedback'
import { cardClasses } from './styles'

const SKELETON_ROWS = 5
const PAGE_BUTTON = 'relative inline-flex items-center px-3 py-2 text-sm font-semibold tabular-nums ring-1 ring-gray-300 ring-inset focus:z-10'

export interface Column<T> {
  key: string
  header: ReactNode
  cell: (row: T) => ReactNode
  sortKey?: string
  className?: string
  headerClassName?: string
}

interface DataTableProps<T> {
  caption: string
  columns: Column<T>[]
  rows: T[] | undefined
  rowKey: (row: T) => string | number
  isLoading?: boolean
  empty: ReactNode
  sort?: string
  onSortChange?: (sort: string) => void
  footer?: ReactNode
}

function parseSort(sort: string | undefined) {
  return { key: sort?.replace(/^-/, '') ?? null, descending: sort?.startsWith('-') ?? false }
}

function ariaSort(sortKey: string | undefined, sort: string | undefined) {
  if (!sortKey) return undefined

  const current = parseSort(sort)

  if (current.key !== sortKey) return 'none'

  return current.descending ? 'descending' : 'ascending'
}

function SortButton({ label, sortKey, sort, onSortChange }: { label: ReactNode; sortKey: string; sort?: string; onSortChange: (sort: string) => void }) {
  const current = parseSort(sort)
  const active = current.key === sortKey
  const Icon = !active ? ChevronUpDownIcon : current.descending ? ChevronDownIcon : ChevronUpIcon

  return (
    <button
      type="button"
      onClick={() => onSortChange(active && !current.descending ? `-${sortKey}` : sortKey)}
      className="group inline-flex items-center gap-1 rounded-sm"
    >
      {label}
      <Icon aria-hidden="true" className={cx('size-4', active ? 'text-gray-900' : 'text-gray-400 group-hover:text-gray-600')} />
    </button>
  )
}

export function DataTable<T>({ caption, columns, rows, rowKey, isLoading = false, empty, sort, onSortChange, footer }: DataTableProps<T>) {
  const showSkeleton = isLoading && !rows
  const isEmpty = !isLoading && rows?.length === 0

  return (
    <div className={cx('overflow-hidden', cardClasses)}>
      <div className="relative overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200" aria-busy={isLoading || undefined}>
          <caption className="sr-only">{caption}</caption>
          <thead className="bg-gray-50">
            <tr>
              {columns.map((column) => (
                <th
                  key={column.key}
                  scope="col"
                  aria-sort={ariaSort(column.sortKey, sort)}
                  className={cx('px-4 py-3.5 text-left text-sm font-semibold whitespace-nowrap text-gray-900 sm:px-6', column.headerClassName)}
                >
                  {column.sortKey && onSortChange ? (
                    <SortButton label={column.header} sortKey={column.sortKey} sort={sort} onSortChange={onSortChange} />
                  ) : (
                    column.header
                  )}
                </th>
              ))}
            </tr>
          </thead>
          <tbody className={cx('divide-y divide-gray-200 bg-white', isLoading && rows && 'opacity-60 transition-opacity')}>
            {showSkeleton &&
              Array.from({ length: SKELETON_ROWS }, (_, index) => (
                <tr key={index}>
                  {columns.map((column) => (
                    <td key={column.key} className={cx('px-4 py-4 sm:px-6', column.className)}>
                      <Skeleton className="h-4 w-full max-w-40" />
                    </td>
                  ))}
                </tr>
              ))}
            {isEmpty && (
              <tr>
                <td colSpan={columns.length}>{empty}</td>
              </tr>
            )}
            {rows?.map((row) => (
              <tr key={rowKey(row)} className="hover:bg-gray-50/60">
                {columns.map((column) => (
                  <td key={column.key} className={cx('px-4 py-4 align-top text-sm text-gray-500 sm:px-6', column.className)}>
                    {column.cell(row)}
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      {footer}
    </div>
  )
}

export function Pagination({ meta, onPageChange }: { meta: PaginationMeta | undefined; onPageChange: (page: number) => void }) {
  if (!meta || meta.total === 0) return null

  const { current_page: current, last_page: last, from, to, total } = meta

  return (
    <nav aria-label="Paginación" className="flex items-center justify-between gap-4 border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
      <p className="text-sm text-gray-700">
        Mostrando <span className="font-medium tabular-nums">{formatNumber(from)}</span> a <span className="font-medium tabular-nums">{formatNumber(to)}</span> de{' '}
        <span className="font-medium tabular-nums">{formatNumber(total)}</span> resultados
      </p>
      {last > 1 && (
        <div className="isolate inline-flex -space-x-px rounded-md shadow-xs">
          <button
            type="button"
            onClick={() => onPageChange(current - 1)}
            disabled={current === 1}
            className={cx(PAGE_BUTTON, 'rounded-l-md px-2 text-gray-500 hover:bg-gray-50 disabled:opacity-50')}
          >
            <span className="sr-only">Anterior</span>
            <ChevronLeftIcon aria-hidden="true" className="size-5" />
          </button>
          {pageWindow(current, last).map((page, index) =>
            page === PAGE_GAP ? (
              <span key={`gap-${index}`} className={cx(PAGE_BUTTON, 'hidden text-gray-700 sm:inline-flex')}>
                …
              </span>
            ) : (
              <button
                key={page}
                type="button"
                onClick={() => onPageChange(page)}
                aria-current={page === current ? 'page' : undefined}
                className={cx(PAGE_BUTTON, page === current ? 'z-10 bg-indigo-600 text-white ring-indigo-600' : 'hidden text-gray-900 hover:bg-gray-50 sm:inline-flex')}
              >
                {page}
              </button>
            ),
          )}
          <button
            type="button"
            onClick={() => onPageChange(current + 1)}
            disabled={current === last}
            className={cx(PAGE_BUTTON, 'rounded-r-md px-2 text-gray-500 hover:bg-gray-50 disabled:opacity-50')}
          >
            <span className="sr-only">Siguiente</span>
            <ChevronRightIcon aria-hidden="true" className="size-5" />
          </button>
        </div>
      )}
    </nav>
  )
}
