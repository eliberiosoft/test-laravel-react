import { useId, useState, type ChangeEvent, type DragEvent, type FormEvent } from 'react'
import { ArrowDownTrayIcon, DocumentTextIcon, XMarkIcon } from '@heroicons/react/20/solid'
import { ArrowUpTrayIcon } from '@heroicons/react/24/outline'
import type { UserImport } from '@/core/types'
import { useUploadImport } from '@/services/imports.service'
import { Button } from '@/shared/ui/Button'
import { Alert } from '@/shared/ui/Feedback'
import { Panel } from '@/shared/ui/Layout'
import { cx } from '@/shared/utils'

const IMPORT_EXTENSIONS = ['csv', 'xlsx']
const IMPORT_MAX_BYTES = 10 * 1024 * 1024
const IMPORT_COLUMNS = ['first_name', 'middle_name', 'first_surname', 'second_surname', 'identification', 'birth_date', 'username']

const TEMPLATE_EXAMPLE = ['Juan', 'Alberto', 'Piguave', 'Loor', '1203574901', '1992-07-21', 'Jpiguave01']

function validateImportFile(file: File): string | null {
  const extension = file.name.split('.').pop()?.toLowerCase() ?? ''

  if (!IMPORT_EXTENSIONS.includes(extension)) return 'El archivo debe ser CSV o XLSX.'
  if (file.size > IMPORT_MAX_BYTES) return 'El archivo supera el tamaño máximo de 10 MB.'
  if (file.size === 0) return 'El archivo está vacío.'

  return null
}

function downloadTemplate(): void {
  const csv = `${IMPORT_COLUMNS.join(',')}\n${TEMPLATE_EXAMPLE.join(',')}\n`
  const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }))
  const link = Object.assign(document.createElement('a'), { href: url, download: 'plantilla-usuarios.csv' })

  link.click()
  URL.revokeObjectURL(url)
}

function formatFileSize(bytes: number): string {
  return bytes < 1024 * 1024 ? `${Math.max(1, Math.round(bytes / 1024))} KB` : `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

export function ImportUploader({ onUploaded }: { onUploaded: (userImport: UserImport) => void }) {
  const inputId = useId()
  const upload = useUploadImport()
  const [file, setFile] = useState<File | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [isDragging, setIsDragging] = useState(false)

  const select = (candidate: File | undefined) => {
    if (!candidate) return

    upload.reset()
    setError(validateImportFile(candidate))
    setFile(candidate)
  }

  const onDrop = (event: DragEvent<HTMLDivElement>) => {
    event.preventDefault()
    setIsDragging(false)
    select(event.dataTransfer.files[0])
  }

  const onChange = (event: ChangeEvent<HTMLInputElement>) => {
    select(event.target.files?.[0])
    event.target.value = ''
  }

  const clear = () => {
    setFile(null)
    setError(null)
    upload.reset()
  }

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    if (!file || error) return

    upload.mutate(file, { onSuccess: onUploaded })
  }

  const message = error ?? upload.error?.firstMessage()

  return (
    <Panel
      title="Subir archivo"
      description="Cada fila crea un usuario con rol Usuario. Las filas con datos inválidos o duplicados se reportan sin detener la carga."
      actions={
        <Button variant="secondary" size="sm" onClick={downloadTemplate}>
          <ArrowDownTrayIcon aria-hidden="true" className="-ml-0.5 size-4 text-gray-400" />
          Descargar plantilla
        </Button>
      }
    >
      <form onSubmit={submit} noValidate className="space-y-6">
        <div
          onDragOver={(event) => {
            event.preventDefault()
            setIsDragging(true)
          }}
          onDragLeave={() => setIsDragging(false)}
          onDrop={onDrop}
          className={cx(
            'flex justify-center rounded-lg border border-dashed px-6 py-10 transition-colors duration-150',
            isDragging ? 'border-indigo-600 bg-indigo-50/50' : 'border-gray-900/25',
          )}
        >
          <div className="text-center">
            <ArrowUpTrayIcon aria-hidden="true" className="mx-auto size-10 text-gray-400" />
            <div className="mt-4 flex justify-center text-sm/6 text-gray-600">
              <label
                htmlFor={inputId}
                className="relative cursor-pointer rounded-md bg-transparent font-semibold text-indigo-600 focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-indigo-600 hover:text-indigo-500"
              >
                <span>Elige un archivo</span>
                <input id={inputId} type="file" accept=".csv,.xlsx" className="sr-only" onChange={onChange} />
              </label>
              <p className="pl-1">o arrástralo aquí</p>
            </div>
            <p className="text-xs/5 text-gray-600">CSV o XLSX, hasta 10 MB</p>
          </div>
        </div>

        {file && (
          <div className="flex items-center gap-3 rounded-md bg-gray-50 px-4 py-3 ring-1 ring-gray-900/5">
            <DocumentTextIcon aria-hidden="true" className="size-5 shrink-0 text-gray-400" />
            <div className="min-w-0 flex-1 text-sm">
              <p className="truncate font-medium text-gray-900">{file.name}</p>
              <p className="text-gray-500 tabular-nums">{formatFileSize(file.size)}</p>
            </div>
            <button type="button" onClick={clear} className="rounded-md p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
              <span className="sr-only">Quitar archivo</span>
              <XMarkIcon aria-hidden="true" className="size-5" />
            </button>
          </div>
        )}

        {message && <Alert tone="error">{message}</Alert>}

        <div className="flex flex-col gap-4 border-t border-gray-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
          <p className="text-sm text-gray-500">
            Columnas: <span className="font-mono text-xs text-gray-700">{IMPORT_COLUMNS.join(', ')}</span>
          </p>
          <Button type="submit" disabled={!file || Boolean(error)} isLoading={upload.isPending} className="shrink-0">
            Iniciar carga
          </Button>
        </div>
      </form>
    </Panel>
  )
}
