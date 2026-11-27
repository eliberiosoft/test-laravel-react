import { useState } from 'react'
import { ROLE_LABELS, USER_STATUS } from '@/shared/domain'
import { useDebouncedCallback } from '@/shared/hooks'
import { Button } from '@/shared/ui/Button'
import { SelectField, TextField, type SelectOption } from '@/shared/ui/Form'
import { SEARCH_MIN_LENGTH, type UserListParams } from './users.logic'

const ALL: SelectOption = { value: '', label: 'Todos' }

const STATUS_OPTIONS: SelectOption[] = [ALL, ...Object.entries(USER_STATUS).map(([value, { label }]) => ({ value, label }))]
const ROLE_OPTIONS: SelectOption[] = [ALL, ...Object.entries(ROLE_LABELS).map(([value, label]) => ({ value, label }))]

interface UserFiltersProps {
  params: UserListParams
  hasFilters: boolean
  onChange: (changes: Partial<UserListParams>) => void
}

export function UserFilters({ params, hasFilters, onChange }: UserFiltersProps) {
  const [term, setTerm] = useState(params.search)
  const search = useDebouncedCallback((value: string) => onChange({ search: value }))
  const trimmed = term.trim()
  const isTooShort = trimmed.length > 0 && trimmed.length < SEARCH_MIN_LENGTH

  const changeTerm = (value: string) => {
    setTerm(value)
    search.run(value.trim())
  }

  const clear = () => {
    search.cancel()
    setTerm('')
    onChange({ search: '', status: '', role: '' })
  }

  return (
    <div className="grid grid-cols-1 items-start gap-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_auto]">
      <TextField
        className="sm:col-span-2 lg:col-span-1"
        label="Buscar"
        name="search"
        type="search"
        placeholder="Nombre, usuario, correo o identificación"
        value={term}
        onChange={(event) => changeTerm(event.target.value)}
        hint={isTooShort ? `Escribe al menos ${SEARCH_MIN_LENGTH} caracteres para buscar.` : undefined}
      />
      <SelectField label="Estado" name="status" value={params.status} options={STATUS_OPTIONS} onChange={(event) => onChange({ status: event.target.value })} />
      <SelectField label="Rol" name="role" value={params.role} options={ROLE_OPTIONS} onChange={(event) => onChange({ role: event.target.value })} />
      {hasFilters && (
        <Button variant="link" onClick={clear} className="h-9 justify-self-start lg:mt-8">
          Limpiar filtros
        </Button>
      )}
    </div>
  )
}
