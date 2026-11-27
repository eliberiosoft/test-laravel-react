export type FormValues = Record<string, string>

export type Rule = (value: string, values: FormValues) => string | null

export type FieldErrors<V extends FormValues> = Partial<Record<keyof V, string>>

const isBlank = (value: string | undefined) => (value ?? '').trim() === ''

export const rules = {
  required:
    (message = 'Este campo es obligatorio.'): Rule =>
    (value) =>
      isBlank(value) ? message : null,
  matches:
    (pattern: RegExp, message: string): Rule =>
    (value) =>
      !isBlank(value) && !pattern.test(value) ? message : null,
  rejects:
    (pattern: RegExp, message: string): Rule =>
    (value) =>
      !isBlank(value) && pattern.test(value) ? message : null,
  minLength:
    (min: number, message: string): Rule =>
    (value) =>
      !isBlank(value) && value.length < min ? message : null,
  maxLength:
    (max: number): Rule =>
    (value) =>
      !isBlank(value) && value.length > max ? `Usa como máximo ${max} caracteres.` : null,
  sameAs:
    (field: string, message: string): Rule =>
    (value, values) =>
      value !== values[field] ? message : null,
}

export const fieldRules = {
  email: [rules.required(), rules.matches(/^[^\s@]+@[^\s@]+\.[^\s@]+$/, 'Ingresa un correo electrónico válido.')],
  personName: [
    rules.matches(/^(?=.*\p{L})[\p{L}\s'-]+$/u, 'Usa solo letras, espacios, apóstrofos o guiones.'),
    rules.maxLength(60),
  ],
  identification: [
    rules.required(),
    rules.matches(/^\d{10}$/, 'La identificación debe tener exactamente 10 dígitos.'),
    rules.rejects(/(\d)\1{3}/, 'No puede tener cuatro dígitos iguales seguidos.'),
  ],
  username: [
    rules.required(),
    rules.matches(
      /^(?=.*[A-Z])(?=.*\d)[A-Za-z0-9]{8,20}$/,
      'Entre 8 y 20 caracteres alfanuméricos, con al menos una mayúscula y un número.',
    ),
  ],
  password: [
    rules.required(),
    rules.minLength(8, 'Usa al menos 8 caracteres.'),
    rules.matches(/\p{Lu}/u, 'Incluye al menos una letra mayúscula.'),
    rules.matches(/[^\p{L}\p{N}\s]/u, 'Incluye al menos un signo, por ejemplo ! o #.'),
    rules.rejects(/\s/, 'No uses espacios.'),
  ],
} satisfies Record<string, Rule[]>

export const PASSWORD_HINT = 'Al menos 8 caracteres, una mayúscula y un signo, sin espacios.'

export function createValidator<V extends FormValues>(schema: Partial<Record<keyof V, Rule[]>>) {
  return (values: V): FieldErrors<V> => {
    const errors: FieldErrors<V> = {}

    for (const field of Object.keys(schema) as Array<keyof V>) {
      const message = schema[field]?.map((check) => check(values[field] ?? '', values)).find(Boolean)

      if (message) errors[field] = message
    }

    return errors
  }
}
