const LOCALE = 'es-EC'

const dateTimeFormat = new Intl.DateTimeFormat(LOCALE, { dateStyle: 'medium', timeStyle: 'short' })
const dateFormat = new Intl.DateTimeFormat(LOCALE, { dateStyle: 'long', timeZone: 'UTC' })
const numberFormat = new Intl.NumberFormat(LOCALE)

export const PAGE_GAP = 'gap'

export type PageItem = number | typeof PAGE_GAP

export function cx(...classes: Array<string | false | null | undefined>): string {
  return classes.filter(Boolean).join(' ')
}

export function formatDateTime(value: string | null | undefined): string {
  return value ? dateTimeFormat.format(new Date(value)) : '—'
}

export function formatDate(value: string | null | undefined): string {
  return value ? dateFormat.format(new Date(`${value}T00:00:00Z`)) : '—'
}

export function formatNumber(value: number | null | undefined): string {
  return numberFormat.format(value ?? 0)
}

export function initials(name = ''): string {
  return name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part.charAt(0).toUpperCase())
    .join('')
}

export function pageWindow(current: number, last: number, radius = 1): PageItem[] {
  const pages = new Set<number>([1, last])

  for (let page = current - radius; page <= current + radius; page += 1) {
    if (page > 1 && page < last) pages.add(page)
  }

  const sorted = [...pages].sort((a, b) => a - b)

  return sorted.flatMap((page, index): PageItem[] => {
    const previous = sorted[index - 1]

    return previous !== undefined && page - previous > 1 ? [PAGE_GAP, page] : [page]
  })
}
