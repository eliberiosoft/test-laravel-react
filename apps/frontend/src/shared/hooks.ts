import { useEffect, useRef, useState, type ChangeEvent, type FormEvent, type MouseEvent, type PointerEvent } from 'react'
import { useMutation, useQueryClient, type QueryKey } from '@tanstack/react-query'
import { ApiError } from '@/core/http'
import type { FieldErrors, FormValues } from './validation'

const FALLBACK_FOCUS_ID = 'contenido'

export interface FieldBinding {
  name: string
  value: string
  error: string | undefined
  onChange: (event: ChangeEvent<HTMLInputElement | HTMLSelectElement>) => void
}

interface UseFormOptions<V extends FormValues> {
  initialValues: V
  validate?: (values: V) => FieldErrors<V>
  onSubmit: (values: V) => Promise<void> | void
}

export function useForm<V extends FormValues>({ initialValues, validate, onSubmit }: UseFormOptions<V>) {
  const [values, setValues] = useState<V>(initialValues)
  const [errors, setErrors] = useState<FieldErrors<V>>({})
  const [formError, setFormError] = useState<string | null>(null)
  const [isSubmitting, setIsSubmitting] = useState(false)

  const setValue = (name: keyof V, value: string) => {
    setValues((current) => ({ ...current, [name]: value }))
    setErrors((current) => ({ ...current, [name]: undefined }))
  }

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    const clientErrors = validate?.(values) ?? {}
    setErrors(clientErrors)
    setFormError(null)

    if (Object.keys(clientErrors).length > 0) return

    setIsSubmitting(true)

    try {
      await onSubmit(values)
    } catch (error) {
      const serverErrors = error instanceof ApiError ? error.fieldErrors() : {}

      if (Object.keys(serverErrors).length > 0) {
        setErrors(serverErrors as FieldErrors<V>)
      } else {
        setFormError(error instanceof Error ? error.message : 'Ocurrió un error inesperado.')
      }
    } finally {
      setIsSubmitting(false)
    }
  }

  const field = (name: keyof V & string): FieldBinding => ({
    name,
    value: values[name] ?? '',
    error: errors[name],
    onChange: (event) => setValue(name, event.target.value),
  })

  const reset = (nextValues: V = initialValues) => {
    setValues(nextValues)
    setErrors({})
    setFormError(null)
  }

  return { values, formError, isSubmitting, field, setValue, handleSubmit, reset }
}

export function useApiMutation<TVariables = void, TData = unknown>(
  mutationFn: (variables: TVariables) => Promise<TData>,
  { invalidate = [] }: { invalidate?: QueryKey[] } = {},
) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn,
    onSuccess: () => Promise.all(invalidate.map((queryKey) => queryClient.invalidateQueries({ queryKey }))),
  })
}

export function useDebouncedCallback<A extends unknown[]>(callback: (...args: A) => void, delay = 300) {
  const timeout = useRef<ReturnType<typeof setTimeout>>(undefined)
  const latest = useRef(callback)

  useEffect(() => {
    latest.current = callback
  })

  useEffect(() => () => clearTimeout(timeout.current), [])

  const cancel = () => clearTimeout(timeout.current)

  const run = (...args: A) => {
    cancel()
    timeout.current = setTimeout(() => latest.current(...args), delay)
  }

  return { run, cancel }
}

function restoreFocus(opener: HTMLElement | null) {
  const target = opener?.isConnected ? opener : document.getElementById(FALLBACK_FOCUS_ID)

  target?.focus({ preventScroll: true })
}

export function useModalDialog(open: boolean, onClose: () => void) {
  const ref = useRef<HTMLDialogElement>(null)
  const opener = useRef<HTMLElement | null>(null)
  const pressedOnBackdrop = useRef(false)

  useEffect(() => {
    const dialog = ref.current

    if (!dialog) return

    if (open && !dialog.open) {
      opener.current = document.activeElement instanceof HTMLElement ? document.activeElement : null
      dialog.showModal()
    }

    if (!open && dialog.open) {
      dialog.close()
      restoreFocus(opener.current)
    }
  }, [open])

  useEffect(() => {
    const dialog = ref.current

    return () => {
      if (dialog?.open) restoreFocus(opener.current)
    }
  }, [])

  return {
    ref,
    onClose,
    onPointerDown: (event: PointerEvent<HTMLDialogElement>) => {
      pressedOnBackdrop.current = event.target === ref.current
    },
    onClick: (event: MouseEvent<HTMLDialogElement>) => {
      if (pressedOnBackdrop.current && event.target === ref.current) onClose()
      pressedOnBackdrop.current = false
    },
  }
}
