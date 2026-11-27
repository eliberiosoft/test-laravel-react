import { useId, type FormEvent, type ReactNode } from 'react'
import { ExclamationTriangleIcon, QuestionMarkCircleIcon } from '@heroicons/react/24/outline'
import type { IconComponent } from '@/core/types'
import { useModalDialog } from '@/shared/hooks'
import { cx } from '@/shared/utils'
import { Button } from './Button'
import { Alert } from './Feedback'

export type DialogTone = 'primary' | 'danger'

const ICON_TONES: Record<DialogTone, string> = {
  primary: 'bg-indigo-100 text-indigo-600',
  danger: 'bg-red-100 text-red-600',
}

const SIZES = {
  md: 'sm:max-w-lg',
  lg: 'sm:max-w-2xl',
}

interface DialogProps {
  open: boolean
  onClose: () => void
  title: string
  description?: ReactNode
  icon?: IconComponent
  tone?: DialogTone
  size?: keyof typeof SIZES
  onSubmit?: (event: FormEvent<HTMLFormElement>) => void
  actions?: ReactNode
  children?: ReactNode
}

export function Dialog({ open, onClose, title, description, icon: Icon, tone = 'primary', size = 'md', onSubmit, actions, children }: DialogProps) {
  const dialogProps = useModalDialog(open, onClose)
  const titleId = useId()
  const descriptionId = useId()

  const body = (
    <>
      <div className="px-4 pt-5 pb-4 sm:p-6">
        <div className="sm:flex sm:items-start sm:gap-4">
          {Icon && (
            <div className={cx('mx-auto flex size-12 shrink-0 items-center justify-center rounded-full sm:mx-0 sm:size-10', ICON_TONES[tone])}>
              <Icon aria-hidden="true" className="size-6" />
            </div>
          )}
          <div className={cx('mt-3 w-full sm:mt-0', Icon ? 'text-center sm:text-left' : 'text-left')}>
            <h2 id={titleId} className="text-base font-semibold text-gray-900">
              {title}
            </h2>
            {description && (
              <p id={descriptionId} className="mt-2 text-sm text-gray-500">
                {description}
              </p>
            )}
            {children && <div className="mt-4 text-left">{children}</div>}
          </div>
        </div>
      </div>
      {actions && <div className="flex flex-col-reverse gap-3 bg-gray-50 px-4 py-3 sm:flex-row sm:justify-end sm:px-6">{actions}</div>}
    </>
  )

  return (
    <dialog
      {...dialogProps}
      role={tone === 'danger' ? 'alertdialog' : undefined}
      aria-labelledby={titleId}
      aria-describedby={description ? descriptionId : undefined}
      className={cx('m-auto w-[calc(100%-2rem)] overflow-hidden rounded-lg bg-white p-0 text-left shadow-xl backdrop:bg-gray-500/75', SIZES[size])}
    >
      {open &&
        (onSubmit ? (
          <form onSubmit={onSubmit} noValidate>
            {body}
          </form>
        ) : (
          body
        ))}
    </dialog>
  )
}

interface ConfirmDialogProps {
  open: boolean
  onClose: () => void
  onConfirm: () => void
  title: string
  description: ReactNode
  confirmLabel: string
  tone?: DialogTone
  isPending?: boolean
  error?: string | null
}

export function ConfirmDialog({ open, onClose, onConfirm, title, description, confirmLabel, tone = 'primary', isPending, error }: ConfirmDialogProps) {
  return (
    <Dialog
      open={open}
      onClose={onClose}
      title={title}
      description={description}
      tone={tone}
      icon={tone === 'danger' ? ExclamationTriangleIcon : QuestionMarkCircleIcon}
      actions={
        <>
          <Button variant="secondary" onClick={onClose} disabled={isPending}>
            Cancelar
          </Button>
          <Button variant={tone === 'danger' ? 'danger' : 'primary'} onClick={onConfirm} isLoading={isPending}>
            {confirmLabel}
          </Button>
        </>
      }
    >
      {error && <Alert tone="error">{error}</Alert>}
    </Dialog>
  )
}
