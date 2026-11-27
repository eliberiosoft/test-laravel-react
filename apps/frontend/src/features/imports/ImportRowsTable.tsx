import { useState } from 'react'
import { CheckCircleIcon } from '@heroicons/react/24/outline'
import type { ImportRow, ImportRowStatus, UserImport } from '@/core/types'
import { useImportRows } from '@/services/imports.service'
import { IMPORT_ROW_STATUS } from '@/shared/domain'
import { DataTable, Pagination, type Column } from '@/shared/ui/DataTable'
import { EmptyState, QueryError, StatusBadge } from '@/shared/ui/Feedback'
import { cx } from '@/shared/utils'

const ROWS_PER_PAGE = 25
const WIDE_ONLY = 'hidden xl:table-cell'
const TABLET_UP = 'hidden sm:table-cell'

const FILTERS: Array<{ value: ImportRowStatus | ''; label: string }> = [
  { value: '', label: 'Todas' },
  ...(Object.entries(IMPORT_ROW_STATUS) as Array<[ImportRowStatus, { label: string }]>).map(([value, { label }]) => ({ value, label })),
]

const errorMessages = (row: ImportRow) => Object.values(row.errors).flat()

function ErrorList({ row, className }: { row: ImportRow; className?: string }) {
  const messages = errorMessages(row)

  if (messages.length === 0) return null

  return (
    <ul className={cx('list-disc space-y-0.5 pl-4 text-gray-700', className)}>
      {messages.map((message) => (
        <li key={message}>{message}</li>
      ))}
    </ul>
  )
}

const columns: Column<ImportRow>[] = [
  { key: 'line', header: 'Fila', cell: (row) => <span className="tabular-nums">{row.line}</span>, className: 'w-16' },
  {
    key: 'status',
    header: 'Resultado',
    cell: (row) => (
      <>
        <StatusBadge statuses={IMPORT_ROW_STATUS} value={row.status} />
        <dl className="mt-2 space-y-0.5 xl:hidden">
          <dt className="sr-only">Identificación</dt>
          <dd className="tabular-nums">{row.identification ?? '—'}</dd>
          {row.email && (
            <>
              <dt className="sr-only">Correo asignado</dt>
              <dd className="break-words">{row.email}</dd>
            </>
          )}
        </dl>
        <ErrorList row={row} className="mt-2 sm:hidden" />
      </>
    ),
  },
  {
    key: 'identification',
    header: 'Identificación',
    cell: (row) => <span className="tabular-nums">{row.identification ?? '—'}</span>,
    className: WIDE_ONLY,
    headerClassName: WIDE_ONLY,
  },
  { key: 'email', header: 'Correo asignado', cell: (row) => row.email ?? '—', className: WIDE_ONLY, headerClassName: WIDE_ONLY },
  {
    key: 'errors',
    header: 'Detalle',
    cell: (row) => (errorMessages(row).length > 0 ? <ErrorList row={row} /> : '—'),
    className: `${TABLET_UP} min-w-64`,
    headerClassName: TABLET_UP,
  },
]

export function ImportRowsTable({ userImport }: { userImport: UserImport }) {
  const [status, setStatus] = useState<ImportRowStatus | ''>('')
  const [page, setPage] = useState(1)
  const isFinished = userImport.status === 'completed' || userImport.status === 'failed'
  const rows = useImportRows(userImport.id, { 'filter[status]': status, page, per_page: ROWS_PER_PAGE }, isFinished)

  if (!isFinished) return null

  const changeFilter = (value: ImportRowStatus | '') => {
    setStatus(value)
    setPage(1)
  }

  return (
    <section aria-labelledby="import-rows-title" className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <h2 id="import-rows-title" className="text-base/7 font-semibold text-gray-900">
          Resultado por fila
        </h2>
        <div role="group" aria-label="Filtrar por resultado" className="isolate inline-flex rounded-md shadow-xs">
          {FILTERS.map((filter, index) => (
            <button
              key={filter.value}
              type="button"
              aria-pressed={status === filter.value}
              onClick={() => changeFilter(filter.value)}
              className={cx(
                'relative -ml-px px-3 py-2 text-sm font-semibold ring-1 ring-inset first:ml-0 focus:z-20',
                index === 0 && 'rounded-l-md',
                index === FILTERS.length - 1 && 'rounded-r-md',
                status === filter.value ? 'z-10 bg-indigo-600 text-white ring-indigo-600' : 'bg-white text-gray-900 ring-gray-300 hover:bg-gray-50',
              )}
            >
              {filter.label}
            </button>
          ))}
        </div>
      </div>
      {rows.isError ? (
        <QueryError error={rows.error} onRetry={() => rows.refetch()} />
      ) : (
        <DataTable
          caption="Resultado de cada fila del archivo"
          columns={columns}
          rows={rows.data?.data}
          rowKey={(row) => row.line}
          isLoading={rows.isFetching}
          empty={<EmptyState icon={CheckCircleIcon} title="No hay filas con este resultado" />}
          footer={<Pagination meta={rows.data?.meta} onPageChange={setPage} />}
        />
      )}
    </section>
  )
}
