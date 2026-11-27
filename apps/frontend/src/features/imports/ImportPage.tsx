import { useSearchParams } from 'react-router'
import { ArrowUpTrayIcon } from '@heroicons/react/20/solid'
import type { UserImport } from '@/core/types'
import { isImportRunning, useImport } from '@/services/imports.service'
import { IMPORT_STATUS } from '@/shared/domain'
import { Button, Spinner } from '@/shared/ui/Button'
import { Alert, QueryState, StatusBadge } from '@/shared/ui/Feedback'
import { PageHeader } from '@/shared/ui/Layout'
import { cardClasses } from '@/shared/ui/styles'
import { cx, formatDateTime, formatNumber } from '@/shared/utils'
import { ImportRowsTable } from './ImportRowsTable'
import { ImportUploader } from './ImportUploader'

const IMPORT_PARAM = 'import'

const SUMMARY_ITEMS = [
  { key: 'total', label: 'Filas leídas', color: 'text-gray-900' },
  { key: 'created', label: 'Creados', color: 'text-green-700' },
  { key: 'skipped', label: 'Omitidos', color: 'text-amber-700' },
  { key: 'failed', label: 'Con errores', color: 'text-red-700' },
] as const

function ImportSummary({ userImport }: { userImport: UserImport }) {
  const running = isImportRunning(userImport.status)

  return (
    <section aria-labelledby="import-summary-title" className={cardClasses}>
      <header className="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-4 py-5 sm:px-6">
        <div className="min-w-0">
          <h2 id="import-summary-title" className="truncate text-base/7 font-semibold text-gray-900">
            {userImport.file_name}
          </h2>
          <p className="mt-1 text-sm text-gray-500">
            Subido el {formatDateTime(userImport.created_at)}
            {userImport.finished_at && ` · terminó el ${formatDateTime(userImport.finished_at)}`}
          </p>
        </div>
        <div className="flex items-center gap-2" aria-live="polite">
          {running && <Spinner className="text-sky-600" />}
          <StatusBadge statuses={IMPORT_STATUS} value={userImport.status} />
        </div>
      </header>
      <dl className="grid grid-cols-2 gap-px bg-gray-900/5 lg:grid-cols-4">
        {SUMMARY_ITEMS.map(({ key, label, color }) => (
          <div key={key} className="bg-white px-4 py-5 sm:px-6">
            <dt className="text-sm/6 font-medium text-gray-500">{label}</dt>
            <dd className={cx('mt-1 text-2xl font-semibold tracking-tight tabular-nums', color)}>{formatNumber(userImport.summary[key])}</dd>
          </div>
        ))}
      </dl>
      {running && (
        <p className="border-t border-gray-200 px-4 py-3 text-sm text-gray-500 sm:px-6">
          La carga se procesa en segundo plano. Puedes salir de esta página y volver con el mismo enlace.
        </p>
      )}
      {userImport.error && (
        <div className="border-t border-gray-200 p-4 sm:px-6">
          <Alert tone="error">{userImport.error}</Alert>
        </div>
      )}
    </section>
  )
}

export function ImportPage() {
  const [searchParams, setSearchParams] = useSearchParams()
  const importId = Number(searchParams.get(IMPORT_PARAM)) || null
  const userImport = useImport(importId)

  const show = (id: number | null) => setSearchParams(id ? { [IMPORT_PARAM]: String(id) } : {})

  return (
    <div className="space-y-6">
      <PageHeader
        title="Carga masiva"
        description="Crea varios usuarios a la vez desde un archivo CSV o XLSX. El correo de cada usuario se genera automáticamente."
        actions={
          importId !== null && (
            <Button variant="secondary" onClick={() => show(null)}>
              <ArrowUpTrayIcon aria-hidden="true" className="-ml-0.5 size-5 text-gray-400" />
              Subir otro archivo
            </Button>
          )
        }
      />
      {importId ? (
        <QueryState query={userImport}>
          {(data) => (
            <>
              <ImportSummary userImport={data} />
              <ImportRowsTable userImport={data} />
            </>
          )}
        </QueryState>
      ) : (
        <ImportUploader onUploaded={(created) => show(created.id)} />
      )}
    </div>
  )
}
