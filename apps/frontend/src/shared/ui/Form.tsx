import { useId, type ComponentProps, type ReactNode } from 'react'
import { ChevronDownIcon } from '@heroicons/react/16/solid'
import { cx } from '@/shared/utils'
import { controlClasses } from './styles'

interface FieldProps {
  label: string
  name: string
  error?: string
  hint?: ReactNode
  optional?: boolean
  labelAction?: ReactNode
  className?: string
}

interface ControlProps {
  id: string
  name: string
  'aria-invalid'?: boolean
  'aria-describedby'?: string
}

function Field({ label, name, error, hint, optional, labelAction, className, children }: FieldProps & { children: (props: ControlProps) => ReactNode }) {
  const id = `${name}-${useId()}`
  const hintId = hint && !error ? `${id}-hint` : undefined
  const errorId = error ? `${id}-error` : undefined

  return (
    <div className={className}>
      <div className="flex items-center justify-between gap-3">
        <label htmlFor={id} className="block text-sm/6 font-medium text-gray-900">
          {label}
        </label>
        {optional && !labelAction && <span className="text-sm/6 text-gray-500">Opcional</span>}
        {labelAction && <div className="text-sm">{labelAction}</div>}
      </div>
      <div className="mt-2">{children({ id, name, 'aria-invalid': error ? true : undefined, 'aria-describedby': errorId ?? hintId })}</div>
      {hintId && (
        <p id={hintId} className="mt-2 text-sm text-gray-500">
          {hint}
        </p>
      )}
      {errorId && (
        <p id={errorId} className="mt-2 text-sm text-red-600">
          {error}
        </p>
      )}
    </div>
  )
}

type TextFieldProps = FieldProps & Omit<ComponentProps<'input'>, 'name' | 'className'>

export function TextField({ label, name, error, hint, optional, labelAction, className, ...inputProps }: TextFieldProps) {
  return (
    <Field {...{ label, name, error, hint, optional, labelAction, className }}>
      {(controlProps) => <input {...controlProps} {...inputProps} className={controlClasses(Boolean(error))} />}
    </Field>
  )
}

export interface SelectOption {
  value: string
  label: string
}

type SelectFieldProps = Omit<FieldProps, 'labelAction'> & Omit<ComponentProps<'select'>, 'name' | 'className'> & { options: SelectOption[] }

export function SelectField({ label, name, error, hint, optional, options, className, ...selectProps }: SelectFieldProps) {
  return (
    <Field {...{ label, name, error, hint, optional, className }}>
      {(controlProps) => (
        <div className="grid grid-cols-1">
          <select {...controlProps} {...selectProps} className={cx(controlClasses(Boolean(error)), 'col-start-1 row-start-1 appearance-none pr-8')}>
            {options.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>
          <ChevronDownIcon
            aria-hidden="true"
            className="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end text-gray-500 sm:size-4"
          />
        </div>
      )}
    </Field>
  )
}
