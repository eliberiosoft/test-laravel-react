import type { BadgeTone } from '@/core/types'
import { cx } from '@/shared/utils'

export type ButtonVariant = 'primary' | 'secondary' | 'danger' | 'link'
export type ButtonSize = 'sm' | 'md'

const BUTTON_VARIANTS: Record<ButtonVariant, string> = {
  primary: 'bg-indigo-600 text-white shadow-xs hover:bg-indigo-500 disabled:hover:bg-indigo-600',
  secondary: 'bg-white text-gray-900 shadow-xs ring-1 ring-gray-300 ring-inset hover:bg-gray-50 disabled:hover:bg-white',
  danger: 'bg-red-600 text-white shadow-xs hover:bg-red-500 disabled:hover:bg-red-600',
  link: 'text-indigo-600 hover:text-indigo-500',
}

const BUTTON_SIZES: Record<ButtonSize | 'inline', string> = {
  sm: 'gap-x-1.5 rounded-md px-2.5 py-1.5 text-sm',
  md: 'gap-x-2 rounded-md px-3 py-2 text-sm',
  inline: 'gap-x-1 rounded-sm text-sm',
}

export function buttonClasses({
  variant = 'primary',
  size = 'md',
  className,
}: { variant?: ButtonVariant; size?: ButtonSize; className?: string } = {}): string {
  return cx(
    'inline-flex items-center justify-center font-semibold transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-60',
    BUTTON_VARIANTS[variant],
    BUTTON_SIZES[variant === 'link' ? 'inline' : size],
    className,
  )
}

export const linkClasses = 'rounded-sm font-semibold text-indigo-600 hover:text-indigo-500'

export const cardClasses = 'rounded-lg bg-white shadow-xs ring-1 ring-gray-900/5'

export function controlClasses(hasError: boolean): string {
  return cx(
    'block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500 sm:text-sm/6',
    hasError ? 'outline-red-300 focus:outline-red-600' : 'outline-gray-300 focus:outline-indigo-600',
  )
}

export const BADGE_TONES: Record<BadgeTone, string> = {
  green: 'bg-green-50 text-green-700 ring-green-600/20',
  red: 'bg-red-50 text-red-700 ring-red-600/10',
  amber: 'bg-amber-50 text-amber-800 ring-amber-600/20',
  sky: 'bg-sky-50 text-sky-700 ring-sky-700/10',
  indigo: 'bg-indigo-50 text-indigo-700 ring-indigo-700/10',
  gray: 'bg-gray-50 text-gray-600 ring-gray-500/10',
}
